<?php

namespace App\Http\Controllers;

// CHANGED (UACE paper rebuild): manual review queue. UNMATCHED band patterns are
// never guessed by the engine — a registrar confirms the grade here, and the
// confirmation is stored as a new combination_rules row (exact_set matcher) so
// the same pattern auto-resolves from then on.

use App\Models\CombinationRule;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\PaperResult;
use App\Services\UaceGradingEngine;
use Illuminate\Http\Request;

class UaceReviewController extends Controller
{
    /** List every INCOMPLETE / UNMATCHED subject result for a chosen sitting. */
    public function index(Request $request)
    {
        // Sittings that actually have paper results.
        $examIds = PaperResult::query()->distinct()->pluck('exam_id');
        $exams = Exam::with(['academicYear', 'term'])
            ->whereIn('id', $examIds)
            ->orderByDesc('created_at')
            ->get();

        $selectedExam = $request->filled('exam_id') ? $exams->firstWhere('id', (int) $request->exam_id) : null;

        $pending = collect();
        $cycle = null;
        if ($selectedExam) {
            $cycle = UaceGradingEngine::cycleForExam($selectedExam);
            $enrollmentIds = PaperResult::where('exam_id', $selectedExam->id)->distinct()->pluck('enrollment_id');
            $enrollments = Enrollment::with(['student', 'subjectCombination.subjects.papers'])
                ->whereIn('id', $enrollmentIds)
                ->get();

            foreach ($enrollments as $enrollment) {
                $result = UaceGradingEngine::studentResult($enrollment, $selectedExam, $cycle);
                if (! $result['available']) {
                    continue;
                }

                foreach (array_merge($result['principals'], $result['subsidiaries']) as $subjectResult) {
                    if ($subjectResult['status'] === UaceGradingEngine::STATUS_GRADED) {
                        continue;
                    }

                    $pending->push([
                        'enrollment' => $enrollment,
                        'student' => $enrollment->student,
                        'subject' => $subjectResult['subject'],
                        'subject_id' => $subjectResult['subject_id'],
                        'status' => $subjectResult['status'],
                        'bands' => $subjectResult['bands'],
                        'papers' => $subjectResult['papers'],
                    ]);
                }
            }
        }

        $gradeCodes = $cycle?->gradePoints->sortBy('id')->pluck('grade_code')->values() ?? collect();

        return view('uace.review', compact('exams', 'selectedExam', 'pending', 'cycle', 'gradeCodes'));
    }

    /**
     * Confirm a grade for an UNMATCHED band pattern. The resolution is recorded
     * as a versioned rule (exact_set matcher) in the exam's pinned cycle.
     */
    public function resolve(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'bands' => 'required|string', // e.g. "P7,P7,C6" (worst-first, from the queue row)
            'grade' => 'required|string|max:2',
            'confirm' => 'accepted', // explicit admin confirmation checkbox
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);
        $cycle = UaceGradingEngine::cycleForExam($exam);
        abort_unless($cycle, 422, 'No exam cycle configured.');

        $bands = collect(explode(',', $validated['bands']))
            ->map(fn($b) => strtoupper(trim($b)))
            ->filter(fn($b) => isset(UaceGradingEngine::BAND_RANKS[$b]))
            ->values();

        if ($bands->isEmpty() || $bands->count() < 1 || $bands->count() > 4) {
            return back()->with('error', 'Invalid band pattern — only fully-banded (scored) subjects can be resolved here. Incomplete subjects need their papers entered first.');
        }

        // An INCOMPLETE subject (absent/withheld paper) must never sneak in as a
        // shorter pattern — the bands must cover the subject's full paper count.
        $subject = \App\Models\Subject::findOrFail($validated['subject_id']);
        $requiredCount = $subject->is_subsidiary ? 1 : (int) $subject->paper_count;
        if ($bands->count() !== $requiredCount) {
            return back()->with('error', "{$subject->name} needs {$requiredCount} banded paper(s) before its grade can be confirmed — enter or resolve the missing papers first.");
        }

        $grade = strtoupper($validated['grade']);
        if (! $cycle->gradePoints->contains('grade_code', $grade)) {
            return back()->with('error', "\"{$grade}\" is not a grade in the {$cycle->name} ruleset.");
        }

        $paperCount = $bands->count();
        $nextOrder = (int) CombinationRule::where('exam_cycle_id', $cycle->id)
            ->where('paper_count', $paperCount)
            ->max('rule_order') + 1;

        CombinationRule::create([
            'exam_cycle_id' => $cycle->id,
            'paper_count' => $paperCount,
            'rule_order' => $nextOrder,
            'matcher' => ['exact_set' => $bands->all()],
            'subject_category_override' => null,
            'resulting_grade' => $grade,
            'description' => 'Manual resolution [' . $bands->implode(',') . '] confirmed by '
                . (auth()->user()->name ?? 'admin') . ' on ' . now()->format('Y-m-d'),
        ]);

        return redirect()->route('uace-review.index', ['exam_id' => $exam->id])
            ->with('success', "Pattern [{$bands->implode(', ')}] confirmed as {$grade}. It will now auto-resolve for every student with the same bands.");
    }
}
