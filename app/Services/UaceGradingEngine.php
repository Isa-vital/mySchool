<?php

namespace App\Services;

use App\Models\CombinationRule;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamCycle;
use App\Models\PaperResult;
use App\Models\Subject;
use Illuminate\Support\Collection;

/**
 * UACE paper-level grading engine.
 *
 * Every principal subject is 2-4 independently marked papers. Each paper's %
 * is banded D1..F9 via the cycle's paper_band_boundaries; the subject letter
 * comes from walking combination_rules in rule_order (first match wins) —
 * NEVER from averaging. Unknown band patterns return UNMATCHED for manual
 * review instead of a guessed grade. All boundaries/rules/points are data,
 * versioned per exam_cycle.
 *
 * Matcher DSL (all present keys must hold — conjunctive):
 *   worst_at_best: 'C3'   worst paper is C3 or better
 *   worst_exact:   'P7'   worst paper is exactly P7
 *   others_at_best:'D2'   every other paper is D2 or better
 *   others_exact:  'P8'   every other paper is exactly P8
 *   max_band_count: {band:'C6', max:1, scope:'others'}  at most N at that band
 *   all_exact:     'P7'   every paper is exactly P7
 *   f9_count:      2      exactly N F9 papers ("others" = the non-F9 papers)
 *   requires_pass_band: true   at least one paper is P7 or P8
 *   forbid_f9:     true   no paper is F9
 *   sum_min/sum_max: int  sum of numeric band values (D1=1..F9=9)
 *   exact_set: ['P7','P7','C6']  literal multiset (used by manual-review resolutions)
 */
class UaceGradingEngine
{
    public const BAND_RANKS = ['D1' => 1, 'D2' => 2, 'C3' => 3, 'C4' => 4, 'C5' => 5, 'C6' => 6, 'P7' => 7, 'P8' => 8, 'F9' => 9];

    public const STATUS_GRADED = 'graded';
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_UNMATCHED = 'unmatched';

    /** Resolve the ruleset for an exam: pinned cycle, else the active one. */
    public static function cycleForExam(?Exam $exam): ?ExamCycle
    {
        if ($exam?->exam_cycle_id) {
            return ExamCycle::find($exam->exam_cycle_id) ?? ExamCycle::active();
        }

        return ExamCycle::active();
    }

    /**
     * Papers belong to one sitting. For a composite report exam, use the latest
     * component sitting (highest display_order) that has paper results.
     */
    public static function resolveSitting(Exam $exam, int $enrollmentId): ?Exam
    {
        $candidates = collect([$exam]);
        if ($exam->is_report_card ?? false) {
            // reorder(): the relation already orders ascending; we want latest first.
            $candidates = $candidates->concat(
                $exam->reportComponents()->reorder('exam_report_components.display_order', 'desc')->get()
            );
        }

        foreach ($candidates as $candidate) {
            if (PaperResult::where('exam_id', $candidate->id)->where('enrollment_id', $enrollmentId)->exists()) {
                return $candidate;
            }
        }

        return null;
    }

    /** Band a raw percentage using the cycle's boundaries. */
    public static function bandFor(float $pct, ExamCycle $cycle): ?string
    {
        foreach ($cycle->bandBoundaries as $band) {
            if ($pct >= (float) $band->min_pct && $pct <= (float) $band->max_pct) {
                return $band->band_code;
            }
        }

        return null;
    }

    /**
     * Grade one principal subject from its paper results.
     * Returns an auditable array: status, grade, points, matched rule, papers.
     */
    public static function gradeSubject(Subject $subject, Collection $paperResults, ExamCycle $cycle): array
    {
        $paperCount = (int) ($subject->paper_count ?: $paperResults->count());
        $papers = [];
        $bands = [];
        $incomplete = false;

        for ($n = 1; $n <= $paperCount; $n++) {
            $result = $paperResults->firstWhere('paper_number', $n);
            $definition = $subject->papers->firstWhere('paper_number', $n);
            $label = $definition?->label() ?? ('Paper ' . $n);

            if (! $result || $result->status !== 'scored' || $result->raw_percentage === null) {
                // A required paper that is absent/withheld/missing blocks the grade.
                $papers[] = [
                    'paper_number' => $n,
                    'label' => $label,
                    'percentage' => null,
                    'band' => strtoupper($result->status ?? 'missing'),
                ];
                $incomplete = true;
                continue;
            }

            $band = self::bandFor((float) $result->raw_percentage, $cycle);
            $papers[] = [
                'paper_number' => $n,
                'label' => $label,
                'percentage' => (float) $result->raw_percentage,
                'band' => $band,
            ];
            $bands[] = $band;
        }

        if ($incomplete) {
            return ['status' => self::STATUS_INCOMPLETE, 'grade' => null, 'points' => null, 'rule_order' => null, 'papers' => $papers, 'bands' => $bands];
        }

        // Subsidiaries never enter the combination matrix — simple threshold path.
        if ($subject->is_subsidiary) {
            $config = $cycle->subsidiaryConfig;
            $pct = $papers[0]['percentage'] ?? 0;
            $pass = $pct >= (float) ($config->pass_threshold_pct ?? 40);

            return [
                'status' => self::STATUS_GRADED,
                'grade' => $pass ? 'Pass' : 'Fail',
                'points' => $pass ? (int) ($config->pass_points ?? 1) : (int) ($config->fail_points ?? 0),
                'rule_order' => null,
                'papers' => $papers,
                'bands' => $bands,
            ];
        }

        // Sort worst-to-best, then first matching rule wins.
        usort($bands, fn($a, $b) => self::BAND_RANKS[$b] <=> self::BAND_RANKS[$a]);

        $rules = CombinationRule::where('exam_cycle_id', $cycle->id)
            ->where('paper_count', $paperCount)
            ->orderBy('rule_order')
            ->get()
            // Category-specific rules are tested before general rules of the same order.
            ->sortBy(fn($r) => [$r->rule_order, $r->subject_category_override === null ? 1 : 0])
            ->values();

        foreach ($rules as $rule) {
            if ($rule->subject_category_override !== null && $rule->subject_category_override !== $subject->subject_category) {
                continue;
            }
            if (self::matches($rule->matcher, $bands)) {
                $points = $cycle->gradePoints->firstWhere('grade_code', $rule->resulting_grade)?->points;

                return [
                    'status' => self::STATUS_GRADED,
                    'grade' => $rule->resulting_grade,
                    'points' => (int) $points,
                    'rule_order' => $rule->rule_order,
                    'rule_description' => $rule->description,
                    'papers' => $papers,
                    'bands' => $bands,
                ];
            }
        }

        // No rule matched: flag for manual review — a wrong guess is worse than a gap.
        return ['status' => self::STATUS_UNMATCHED, 'grade' => null, 'points' => null, 'rule_order' => null, 'papers' => $papers, 'bands' => $bands];
    }

    /** Test one matcher (conjunctive DSL) against a worst-to-best sorted band list. */
    public static function matches(array $matcher, array $bandsWorstFirst): bool
    {
        if ($bandsWorstFirst === []) {
            return false;
        }

        $ranks = array_map(fn($b) => self::BAND_RANKS[$b], $bandsWorstFirst);

        if (isset($matcher['exact_set'])) {
            $expected = $matcher['exact_set'];
            sort($expected);
            $actual = $bandsWorstFirst;
            sort($actual);
            if ($expected !== $actual) {
                return false;
            }
        }

        if (isset($matcher['all_exact']) && count(array_unique($bandsWorstFirst)) !== 1) {
            return false;
        }
        if (isset($matcher['all_exact']) && $bandsWorstFirst[0] !== $matcher['all_exact']) {
            return false;
        }

        // f9_count redefines "others" as the non-F9 papers; otherwise others = all but worst.
        if (isset($matcher['f9_count'])) {
            $f9s = count(array_filter($bandsWorstFirst, fn($b) => $b === 'F9'));
            if ($f9s !== (int) $matcher['f9_count']) {
                return false;
            }
            $others = array_values(array_filter($bandsWorstFirst, fn($b) => $b !== 'F9'));
        } else {
            $others = array_slice($bandsWorstFirst, 1);
        }

        if (isset($matcher['worst_at_best']) && $ranks[0] > self::BAND_RANKS[$matcher['worst_at_best']]) {
            return false;
        }
        if (isset($matcher['worst_exact']) && $bandsWorstFirst[0] !== $matcher['worst_exact']) {
            return false;
        }
        if (isset($matcher['others_at_best'])) {
            foreach ($others as $band) {
                if (self::BAND_RANKS[$band] > self::BAND_RANKS[$matcher['others_at_best']]) {
                    return false;
                }
            }
        }
        if (isset($matcher['others_exact'])) {
            foreach ($others as $band) {
                if ($band !== $matcher['others_exact']) {
                    return false;
                }
            }
        }
        if (isset($matcher['max_band_count'])) {
            $config = $matcher['max_band_count'];
            $pool = ($config['scope'] ?? 'others') === 'all' ? $bandsWorstFirst : $others;
            if (count(array_filter($pool, fn($b) => $b === $config['band'])) > (int) $config['max']) {
                return false;
            }
        }
        if (! empty($matcher['requires_pass_band']) && ! array_intersect(['P7', 'P8'], $bandsWorstFirst)) {
            return false;
        }
        if (! empty($matcher['forbid_f9']) && in_array('F9', $bandsWorstFirst, true)) {
            return false;
        }
        $sum = array_sum($ranks);
        if (isset($matcher['sum_max']) && $sum > (int) $matcher['sum_max']) {
            return false;
        }
        if (isset($matcher['sum_min']) && $sum < (int) $matcher['sum_min']) {
            return false;
        }

        return true;
    }

    /**
     * Full student result for one sitting: every combination subject graded,
     * aggregate from BEST 3 principals + subsidiaries, computed ceiling
     * (never a hardcoded 20), provisional when anything is unresolved.
     */
    public static function studentResult(Enrollment $enrollment, Exam $exam, ?ExamCycle $cycle = null): array
    {
        $cycle ??= self::cycleForExam($exam);
        if (! $cycle) {
            return ['available' => false, 'reason' => 'No active exam cycle (ruleset) configured.'];
        }

        $combination = $enrollment->subjectCombination?->load('subjects.papers');
        if (! $combination) {
            return ['available' => false, 'reason' => 'No subject combination assigned.'];
        }

        $results = PaperResult::where('enrollment_id', $enrollment->id)
            ->where('exam_id', $exam->id)
            ->get()
            ->groupBy('subject_id');

        if ($results->isEmpty()) {
            return ['available' => false, 'reason' => 'No paper results recorded for this sitting.'];
        }

        $principals = [];
        $subsidiaries = [];
        foreach ($combination->subjects as $subject) {
            $graded = self::gradeSubject($subject, $results->get($subject->id, collect()), $cycle);
            $graded['subject'] = $subject->name;
            $graded['subject_id'] = $subject->id;

            if ($subject->pivot->is_principal && ! $subject->is_subsidiary) {
                $principals[] = $graded;
            } else {
                $subsidiaries[] = $graded;
            }
        }

        $maxPoints = (int) $cycle->gradePoints->max('points');
        $passPoints = (int) ($cycle->subsidiaryConfig->pass_points ?? 1);

        $resolvedPrincipals = array_filter($principals, fn($p) => $p['status'] === self::STATUS_GRADED);
        $bestThree = collect($resolvedPrincipals)->sortByDesc('points')->take(3);
        $principalPoints = (int) $bestThree->sum('points');
        $subsidiaryPoints = (int) collect($subsidiaries)->where('status', self::STATUS_GRADED)->sum('points');

        $provisional = collect($principals)->concat($subsidiaries)
            ->contains(fn($s) => $s['status'] !== self::STATUS_GRADED);

        // Marks total across scored papers (ranking tie-break only, never grading).
        $marksTotal = collect($principals)->concat($subsidiaries)
            ->flatMap(fn($s) => $s['papers'])
            ->sum(fn($p) => (float) ($p['percentage'] ?? 0));

        return [
            'available' => true,
            'cycle' => $cycle->name,
            'sitting' => $exam->name,
            'combination_code' => $combination->code,
            'combination_name' => $combination->name,
            'principals' => $principals,
            'subsidiaries' => $subsidiaries,
            'principal_points' => $principalPoints,
            'subsidiary_points' => $subsidiaryPoints,
            'total_points' => $principalPoints + $subsidiaryPoints,
            'max_points' => (min(3, count($principals)) * $maxPoints) + (count($subsidiaries) * $passPoints),
            'marks_total' => round($marksTotal, 2),
            'provisional' => $provisional,
        ];
    }
}
