<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Seeds the CURRENT (legacy A-F) UACE ruleset from the verified UNEB reference
// data. CONFIRMED rules only — known gaps (e.g. 3-paper two-P7 + one-C6) are
// deliberately NOT seeded; they must land in the manual-review queue, never be
// guessed. Matcher DSL is documented in App\Services\UaceGradingEngine.
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('exam_cycles')->where('name', 'UACE Legacy (A-F) — 2025')->exists()) {
            return;
        }

        $cycleId = DB::table('exam_cycles')->insertGetId([
            'name' => 'UACE Legacy (A-F) — 2025',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // A. Paper band boundaries — CONFIRMED
        $bands = [
            ['D1', 85, 100],
            ['D2', 80, 84],
            ['C3', 75, 79],
            ['C4', 70, 74],
            ['C5', 65, 69],
            ['C6', 60, 64],
            ['P7', 50, 59],
            ['P8', 40, 49],
            ['F9', 0, 39],
        ];
        foreach ($bands as [$code, $min, $max]) {
            DB::table('paper_band_boundaries')->insert([
                'exam_cycle_id' => $cycleId,
                'band_code' => $code,
                'min_pct' => $min,
                'max_pct' => $max,
            ]);
        }

        // F. Grade points — CONFIRMED (A-Level ONLY; never share with O-Level)
        foreach ([['A', 6], ['B', 5], ['C', 4], ['D', 3], ['E', 2], ['O', 1], ['F', 0]] as [$grade, $points]) {
            DB::table('uace_grade_points')->insert([
                'exam_cycle_id' => $cycleId,
                'grade_code' => $grade,
                'points' => $points,
            ]);
        }

        // E. Subsidiary: anything except F9 passes (P8 floor = 40%)
        DB::table('subsidiary_configs')->insert([
            'exam_cycle_id' => $cycleId,
            'pass_threshold_pct' => 40,
            'pass_points' => 1,
            'fail_points' => 0,
        ]);

        $rule = function (int $papers, int $order, string $grade, array $matcher, string $desc, ?string $categoryOverride = null) use ($cycleId) {
            DB::table('combination_rules')->insert([
                'exam_cycle_id' => $cycleId,
                'paper_count' => $papers,
                'rule_order' => $order,
                'matcher' => json_encode($matcher),
                'subject_category_override' => $categoryOverride,
                'resulting_grade' => $grade,
                'description' => $desc,
            ]);
        };

        // B. TWO-PAPER rules — CONFIRMED. Rules 6-7 are SUM-based (D1=1..F9=9).
        $rule(2, 1, 'A', ['worst_at_best' => 'D2'], 'Both papers Distinctions (D1/D2)');
        $rule(2, 2, 'B', ['worst_at_best' => 'C3'], 'Worst paper C3-or-better');
        $rule(2, 3, 'C', ['worst_at_best' => 'C4'], 'Worst paper C4, other C4-or-better');
        $rule(2, 4, 'D', ['worst_at_best' => 'C5'], 'Worst paper C5, other C5-or-better');
        $rule(2, 5, 'E', ['worst_at_best' => 'C6'], 'Worst paper C6, other C6-or-better');
        $rule(2, 6, 'E', ['requires_pass_band' => true, 'forbid_f9' => true, 'sum_max' => 12], 'One paper P7/P8, no F9, band sum <= 12');
        $rule(2, 7, 'O', ['requires_pass_band' => true, 'forbid_f9' => true, 'sum_min' => 13, 'sum_max' => 16], 'One paper P7/P8, no F9, band sum 13-16');
        $rule(2, 8, 'O', ['f9_count' => 1, 'others_at_best' => 'C6'], 'Credit-or-better + F9');
        $rule(2, 9, 'F', ['f9_count' => 1, 'others_exact' => 'P8'], 'F9 + P8');
        $rule(2, 10, 'F', ['f9_count' => 2], 'F9 in both papers');
        // GAP (unseeded, manual review): F9 + P7 — not in the published examples.

        // C. THREE-PAPER rules — CONFIRMED
        $rule(3, 1, 'A', ['worst_at_best' => 'C3', 'others_at_best' => 'D2'], 'Worst C3-or-better, other two Distinctions');
        $rule(3, 2, 'B', ['worst_at_best' => 'C4', 'others_at_best' => 'C3'], 'Worst C4-or-better, other two C3-or-better');
        $rule(3, 3, 'C', ['worst_at_best' => 'C5', 'others_at_best' => 'C4'], 'Worst C5-or-better, other two C4-or-better');
        $rule(3, 4, 'D', ['worst_at_best' => 'C6', 'others_at_best' => 'C5'], 'Worst C6-or-better, other two C5-or-better');
        $rule(3, 5, 'E', ['worst_exact' => 'P7', 'others_at_best' => 'C6'], 'Worst P7, other two Credit-or-better');
        $rule(3, 6, 'E', ['worst_exact' => 'P8', 'others_at_best' => 'C6', 'max_band_count' => ['band' => 'C6', 'max' => 1, 'scope' => 'others']], 'Worst P8, at most one C6 among the other two, rest C5-or-better');
        $rule(3, 7, 'O', ['all_exact' => 'P7'], 'All three papers P7');
        $rule(3, 8, 'O', ['all_exact' => 'P8'], 'All three papers P8');
        $rule(3, 9, 'O', ['f9_count' => 1, 'others_at_best' => 'P8'], 'One F9, other two Pass-or-better');
        // *** Confirmed science exception: two F9 + one P7 = FAIL for science subjects ***
        $rule(3, 10, 'F', ['f9_count' => 2, 'others_at_best' => 'P7'], 'SCIENCE: two F9 + third P7-or-better = Fail', 'science');
        $rule(3, 11, 'O', ['f9_count' => 2, 'others_at_best' => 'P7'], 'Two F9, third P7-or-better');
        $rule(3, 12, 'F', ['f9_count' => 2, 'others_exact' => 'P8'], 'Two F9, third P8');
        $rule(3, 13, 'F', ['f9_count' => 3], 'All three papers F9');
        // GAP (unseeded, manual review): two P7 + one C6 — not covered by published examples.

        // D. FOUR-PAPER rules — CONFIRMED (same shape, "others" group is 3 papers)
        $rule(4, 1, 'A', ['worst_at_best' => 'C3', 'others_at_best' => 'D2'], 'Worst C3-or-better, other three Distinctions');
        $rule(4, 2, 'B', ['worst_at_best' => 'C4', 'others_at_best' => 'C3'], 'Worst C4-or-better, other three C3-or-better');
        $rule(4, 3, 'C', ['worst_at_best' => 'C5', 'others_at_best' => 'C4'], 'Worst C5-or-better, other three C4-or-better');
        $rule(4, 4, 'D', ['worst_at_best' => 'C6', 'others_at_best' => 'C5'], 'Worst C6-or-better, other three C5-or-better');
        $rule(4, 5, 'E', ['worst_exact' => 'P7', 'others_at_best' => 'C6'], 'Worst P7, other three Credit-or-better');
        $rule(4, 6, 'E', ['worst_exact' => 'P8', 'others_at_best' => 'C6', 'max_band_count' => ['band' => 'C6', 'max' => 2, 'scope' => 'others']], 'Worst P8, at most two C6 among the other three');
        $rule(4, 7, 'O', ['all_exact' => 'P7'], 'All four papers P7');
        $rule(4, 8, 'O', ['all_exact' => 'P8'], 'All four papers P8');
        $rule(4, 9, 'O', ['f9_count' => 1, 'others_at_best' => 'P8'], 'One F9, other three Pass-or-better');
        $rule(4, 10, 'O', ['f9_count' => 2, 'others_at_best' => 'P7'], 'Two F9, other two P7-or-better');
        $rule(4, 11, 'F', ['f9_count' => 2, 'others_exact' => 'P8'], 'Two F9, other two P8');
        $rule(4, 12, 'F', ['f9_count' => 4], 'All four papers F9');

        // Mark seeded subsidiary subjects.
        DB::table('subjects')->whereIn('code', ['GP', 'SMTC', 'SICT'])
            ->update(['is_subsidiary' => true, 'paper_count' => 1]);
    }

    public function down(): void
    {
        // Versioned reference data — intentionally not reversed.
    }
};
