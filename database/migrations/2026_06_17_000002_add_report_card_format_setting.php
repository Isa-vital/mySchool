<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add report card format setting
        Setting::firstOrCreate(
            ['key' => 'report_card_format'],
            [
                'value' => 'primary',
                'type' => 'select',
                'group' => 'academic',
                'label' => 'Report Card Format',
                'description' => 'Default assessment format for report cards (primary, o-level, a-level)',
                'options' => json_encode(['primary' => 'Primary', 'o-level' => 'O-Level', 'a-level' => 'A-Level']),
                'sort_order' => 40,
            ]
        );

        // Add O-Level grade boundaries setting
        Setting::firstOrCreate(
            ['key' => 'olevel_grade_boundaries'],
            [
                'value' => json_encode([
                    'A' => 80,
                    'B' => 70,
                    'C' => 60,
                    'D' => 50,
                    'E' => 40
                ]),
                'type' => 'textarea',
                'group' => 'academic',
                'label' => 'O-Level Grade Boundaries',
                'description' => 'O-Level grade boundaries (marks to grade mapping)',
                'sort_order' => 41,
            ]
        );

        // Add A-Level points calculation method
        Setting::firstOrCreate(
            ['key' => 'alevel_points_max'],
            [
                'value' => '20',
                'type' => 'number',
                'group' => 'academic',
                'label' => 'A-Level Maximum Points',
                'description' => 'Maximum points for A-Level exams',
                'sort_order' => 42,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', ['report_card_format', 'olevel_grade_boundaries', 'alevel_points_max'])->delete();
    }
};
