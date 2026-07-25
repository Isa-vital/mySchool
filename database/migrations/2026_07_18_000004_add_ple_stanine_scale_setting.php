<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * CHANGED (A5): PLE stanine (D1–F9) boundaries become configurable — they were the
     * last grading bands hardcoded in code (UgandaGrading::subjectGrade). Values mirror
     * the previous hardcoded map; UNEB revisions are now a settings edit, not a deploy.
     */
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'ple_stanine_scale'],
            [
                'value' => json_encode([
                    ['grade' => 'D1', 'min' => 90, 'max' => 100, 'points' => 1, 'description' => 'Distinction'],
                    ['grade' => 'D2', 'min' => 80, 'max' => 89.99, 'points' => 2, 'description' => 'Distinction'],
                    ['grade' => 'C3', 'min' => 70, 'max' => 79.99, 'points' => 3, 'description' => 'Credit'],
                    ['grade' => 'C4', 'min' => 60, 'max' => 69.99, 'points' => 4, 'description' => 'Credit'],
                    ['grade' => 'C5', 'min' => 55, 'max' => 59.99, 'points' => 5, 'description' => 'Credit'],
                    ['grade' => 'C6', 'min' => 50, 'max' => 54.99, 'points' => 6, 'description' => 'Credit'],
                    ['grade' => 'P7', 'min' => 45, 'max' => 49.99, 'points' => 7, 'description' => 'Pass'],
                    ['grade' => 'P8', 'min' => 40, 'max' => 44.99, 'points' => 8, 'description' => 'Pass'],
                    ['grade' => 'F9', 'min' => 0, 'max' => 39.99, 'points' => 9, 'description' => 'Failure'],
                ]),
                'type' => 'textarea',
                'group' => 'academic',
                'label' => 'PLE Stanine Scale (D1-F9)',
                'description' => 'JSON array of PLE stanine bands. Each item needs grade, min, max, points, description. Points 1-9 feed the PLE aggregate.',
                'sort_order' => 46,
            ]
        );

        Setting::flushCache();
    }

    public function down(): void
    {
        Setting::where('key', 'ple_stanine_scale')->delete();
        Setting::flushCache();
    }
};
