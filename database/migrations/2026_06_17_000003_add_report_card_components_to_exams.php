<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('is_report_card')->default(false)->after('max_points');
            $table->foreignId('grading_scale_id')->nullable()->after('is_report_card')->constrained('grading_scales')->nullOnDelete();
        });

        Schema::create('exam_report_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('component_exam_id')->constrained('exams')->cascadeOnDelete();
            $table->decimal('weight', 6, 2)->default(0);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['report_exam_id', 'component_exam_id']);
        });

        Setting::firstOrCreate(
            ['key' => 'primary_achievement_levels'],
            [
                'value' => json_encode([
                    ['grade' => 'Excellent', 'min' => 90, 'max' => 100, 'points' => 1, 'description' => 'Excellent'],
                    ['grade' => 'Very Good', 'min' => 80, 'max' => 89.99, 'points' => 2, 'description' => 'Very Good'],
                    ['grade' => 'Good', 'min' => 70, 'max' => 79.99, 'points' => 3, 'description' => 'Good'],
                    ['grade' => 'Satisfactory', 'min' => 60, 'max' => 69.99, 'points' => 4, 'description' => 'Satisfactory'],
                    ['grade' => 'Fair', 'min' => 50, 'max' => 59.99, 'points' => 5, 'description' => 'Fair'],
                    ['grade' => 'Poor', 'min' => 0, 'max' => 49.99, 'points' => 6, 'description' => 'Poor'],
                ]),
                'type' => 'textarea',
                'group' => 'academic',
                'label' => 'Primary Achievement Levels',
                'description' => 'JSON array of primary grading bands. Each item needs grade, min, max, points, description.',
                'sort_order' => 43,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'olevel_competency_scale'],
            [
                'value' => json_encode([
                    ['grade' => 'A', 'min' => 80, 'max' => 100, 'points' => 1, 'description' => 'Excellent Competency'],
                    ['grade' => 'B', 'min' => 65, 'max' => 79.99, 'points' => 2, 'description' => 'Very Good Competency'],
                    ['grade' => 'C', 'min' => 50, 'max' => 64.99, 'points' => 3, 'description' => 'Satisfactory Competency'],
                    ['grade' => 'D', 'min' => 35, 'max' => 49.99, 'points' => 4, 'description' => 'Basic Competency'],
                    ['grade' => 'E', 'min' => 0, 'max' => 34.99, 'points' => 5, 'description' => 'Developing Competency'],
                ]),
                'type' => 'textarea',
                'group' => 'academic',
                'label' => 'O-Level Competency Scale',
                'description' => 'JSON array of O-Level competency bands. Each item needs grade, min, max, points, description.',
                'sort_order' => 44,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'alevel_grade_scale'],
            [
                'value' => json_encode([
                    ['grade' => 'A', 'min' => 80, 'max' => 100, 'points' => 6, 'description' => 'Excellent'],
                    ['grade' => 'B', 'min' => 70, 'max' => 79.99, 'points' => 5, 'description' => 'Very Good'],
                    ['grade' => 'C', 'min' => 60, 'max' => 69.99, 'points' => 4, 'description' => 'Good'],
                    ['grade' => 'D', 'min' => 55, 'max' => 59.99, 'points' => 3, 'description' => 'Fairly Good'],
                    ['grade' => 'E', 'min' => 50, 'max' => 54.99, 'points' => 2, 'description' => 'Pass'],
                    ['grade' => 'O', 'min' => 40, 'max' => 49.99, 'points' => 1, 'description' => 'Subsidiary Pass'],
                    ['grade' => 'F', 'min' => 0, 'max' => 39.99, 'points' => 0, 'description' => 'Fail'],
                ]),
                'type' => 'textarea',
                'group' => 'academic',
                'label' => 'A-Level Grade Scale',
                'description' => 'JSON array of A-Level grading bands. Each item needs grade, min, max, points, description.',
                'sort_order' => 45,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'primary_achievement_levels',
            'olevel_competency_scale',
            'alevel_grade_scale',
        ])->delete();

        Schema::dropIfExists('exam_report_components');

        Schema::table('exams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grading_scale_id');
            $table->dropColumn('is_report_card');
        });
    }
};
