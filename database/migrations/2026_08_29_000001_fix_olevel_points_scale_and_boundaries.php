<?php

use App\Models\Setting;
use App\Services\AssessmentGradingService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corrects the O-Level (UCE) competency scale to the confirmed model
     * (verified against a real report card):
     *   - points were INVERTED (A=1..E=5, an E scored more points than an A) -> A=4..E=0
     *   - B/C/D/E boundaries were shifted -> A 80-100, B 60-79.99, C 50-59.99,
     *     D 40-49.99, E 0-39.99
     *
     * Fixes saved settings rows still holding the old default values (a code-only
     * fix changes nothing for schools whose settings row was already seeded), then
     * recalculates stored grade letters for O-Level classes and logs how many
     * historical rows were re-banded — Total Points sums generated under the
     * inverted scale ranked students backwards.
     */

    // Previous shipped boundaries+points (descriptors varied pre/post the A4 rename).
    private const OLD_DEFAULT_BANDS = [
        'A' => ['min' => 80, 'max' => 100, 'points' => 1],
        'B' => ['min' => 65, 'max' => 79.99, 'points' => 2],
        'C' => ['min' => 50, 'max' => 64.99, 'points' => 3],
        'D' => ['min' => 35, 'max' => 49.99, 'points' => 4],
        'E' => ['min' => 0, 'max' => 34.99, 'points' => 5],
    ];

    private const NEW_BANDS = [
        ['grade' => 'A', 'min' => 80, 'max' => 100, 'points' => 4, 'description' => 'Exceptional'],
        ['grade' => 'B', 'min' => 60, 'max' => 79.99, 'points' => 3, 'description' => 'Outstanding'],
        ['grade' => 'C', 'min' => 50, 'max' => 59.99, 'points' => 2, 'description' => 'Satisfactory'],
        ['grade' => 'D', 'min' => 40, 'max' => 49.99, 'points' => 1, 'description' => 'Basic'],
        ['grade' => 'E', 'min' => 0, 'max' => 39.99, 'points' => 0, 'description' => 'Elementary'],
    ];

    private const OLD_POINTS_MAP = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5];
    private const NEW_POINTS_MAP = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

    public function up(): void
    {
        $this->fixScaleSetting();
        $this->fixPointsSetting();
        $this->rebandStoredOLevelGrades();
    }

    public function down(): void
    {
        // Data correction — the old scale was wrong; intentionally not restorable.
    }

    private function fixScaleSetting(): void
    {
        $setting = Setting::where('key', 'olevel_competency_scale')->first();
        if (! $setting || ! is_string($setting->value)) {
            return;
        }

        $bands = json_decode($setting->value, true);
        if (! is_array($bands)) {
            return;
        }

        if ($this->matchesOldDefault($bands)) {
            // Stale copy of the old shipped default — replace wholesale.
            $setting->update(['value' => json_encode(self::NEW_BANDS)]);
            Setting::flushCache();

            return;
        }

        // Custom boundaries but still the inverted A=1..E=5 points: fix points only.
        if ($this->hasInvertedPoints($bands)) {
            foreach ($bands as &$band) {
                $grade = strtoupper((string) ($band['grade'] ?? ''));
                if (isset(self::NEW_POINTS_MAP[$grade])) {
                    $band['points'] = self::NEW_POINTS_MAP[$grade];
                }
            }
            $setting->update(['value' => json_encode(array_values($bands))]);
            Setting::flushCache();
        }
    }

    private function fixPointsSetting(): void
    {
        $setting = Setting::where('key', 'olevel_competency_points')->first();
        if (! $setting || ! is_string($setting->value)) {
            return;
        }

        $map = json_decode($setting->value, true);
        if (! is_array($map)) {
            return;
        }

        $normalized = [];
        foreach ($map as $grade => $points) {
            $normalized[strtoupper((string) $grade)] = (int) $points;
        }
        ksort($normalized);
        $old = self::OLD_POINTS_MAP;
        ksort($old);

        if ($normalized === $old) {
            $setting->update(['value' => json_encode(self::NEW_POINTS_MAP)]);
            Setting::flushCache();
        }
    }

    private function matchesOldDefault(array $bands): bool
    {
        if (count($bands) !== count(self::OLD_DEFAULT_BANDS)) {
            return false;
        }

        foreach ($bands as $band) {
            $grade = strtoupper((string) ($band['grade'] ?? ''));
            $expected = self::OLD_DEFAULT_BANDS[$grade] ?? null;
            if (
                ! $expected
                || abs((float) ($band['min'] ?? -1) - $expected['min']) > 0.001
                || abs((float) ($band['max'] ?? -1) - $expected['max']) > 0.001
                || (int) ($band['points'] ?? -1) !== $expected['points']
            ) {
                return false;
            }
        }

        return true;
    }

    private function hasInvertedPoints(array $bands): bool
    {
        $seen = [];
        foreach ($bands as $band) {
            $grade = strtoupper((string) ($band['grade'] ?? ''));
            if (isset(self::OLD_POINTS_MAP[$grade])) {
                $seen[$grade] = (int) ($band['points'] ?? -1);
            }
        }
        ksort($seen);
        $old = self::OLD_POINTS_MAP;
        ksort($old);

        return $seen === $old;
    }

    /**
     * Historical O-Level grade rows carry letters/descriptors banded under the old
     * boundaries — re-band them from marks_obtained with the corrected scale so
     * they are not left standing as "graded under an old rule."
     */
    private function rebandStoredOLevelGrades(): void
    {
        if (! Schema::hasTable('grades') || ! Schema::hasTable('school_classes')) {
            return;
        }

        $oLevelClassIds = \App\Models\SchoolClass::all()
            ->filter(fn($class) => $class->category() === 'o_level')
            ->pluck('id');

        if ($oLevelClassIds->isEmpty()) {
            return;
        }

        $reband = 0;
        $examIds = \App\Models\Grade::whereIn('school_class_id', $oLevelClassIds)
            ->whereNotNull('marks_obtained')
            ->distinct()
            ->pluck('exam_id');

        foreach ($examIds as $examId) {
            $exam = \App\Models\Exam::find($examId);
            if (! $exam) {
                continue;
            }

            $classGroups = \App\Models\Grade::where('exam_id', $examId)
                ->whereIn('school_class_id', $oLevelClassIds)
                ->whereNotNull('marks_obtained')
                ->get()
                ->groupBy('school_class_id');

            foreach ($classGroups as $classId => $rows) {
                $class = \App\Models\SchoolClass::find($classId);

                // Only rows actually graded under the O-Level scale are re-banded —
                // an exam explicitly marked primary/a-level uses a different scale
                // (whose grade labels also don't fit the o-level letter column).
                $format = AssessmentGradingService::resolveFormat($exam->assessment_format, $class);
                if ($format !== 'o-level') {
                    continue;
                }

                $ranges = AssessmentGradingService::rangesForExam($exam, $class);

                foreach ($rows as $row) {
                    $resolved = AssessmentGradingService::resolve((float) $row->marks_obtained, $ranges);
                    if ($row->grade_letter !== $resolved['grade'] || $row->achievement_level !== $resolved['description']) {
                        // Quiet update: skip LogsActivity/model events for the bulk re-band.
                        \App\Models\Grade::withoutEvents(fn() => $row->update([
                            'grade_letter' => $resolved['grade'],
                            'achievement_level' => $resolved['description'],
                        ]));
                        $reband++;
                    }
                }
            }
        }

        if ($reband > 0) {
            Log::warning("O-Level scale fix: re-banded {$reband} historical grade rows to the corrected UCE scale. Any Total Points sums printed under the inverted points scale (A=1..E=5) ranked students backwards — regenerate affected report cards.");
        }
    }
};
