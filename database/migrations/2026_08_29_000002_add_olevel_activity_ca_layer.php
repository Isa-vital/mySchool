<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * O-Level (UCE) activity/CA layer:
     *   - activity_scores: per-activity raw scores (Activities of Integration) feeding
     *     the CA average. Unfilled slots are simply absent rows — never zeros.
     *   - grades: EOT (end-of-term) score/status, teacher-entered identifier (1-3),
     *     and project work fields (own grade, never merged into the final mark).
     *   - settings: activity_scale_config (activity max score, default 3.0) and
     *     ca_weight_config (CA/EOT split, default 20/80) — admin-editable JSON,
     *     same mechanism as olevel_competency_scale.
     */
    public function up(): void
    {
        if (! Schema::hasTable('activity_scores')) {
            Schema::create('activity_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                // Grading-period key: this codebase keys per-period marks by exam
                // (ExamCycle is UACE-only), so activities attach to the exam too.
                $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('activity_number');
                $table->decimal('raw_score', 5, 2)->nullable();
                $table->string('status', 30)->default('scored'); // scored | not_yet_administered
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['enrollment_id', 'subject_id', 'exam_id', 'activity_number'], 'activity_scores_slot_unique');
            });
        }

        Schema::table('grades', function (Blueprint $table) {
            if (! Schema::hasColumn('grades', 'eot_raw_score')) {
                $table->decimal('eot_raw_score', 6, 2)->nullable();
            }
            if (! Schema::hasColumn('grades', 'eot_max_score')) {
                $table->decimal('eot_max_score', 6, 2)->default(80);
            }
            if (! Schema::hasColumn('grades', 'eot_status')) {
                $table->string('eot_status', 30)->nullable(); // scored | absent | withheld (null = legacy single-mark row)
            }
            if (! Schema::hasColumn('grades', 'identifier')) {
                $table->unsignedTinyInteger('identifier')->nullable(); // teacher-entered 1/2/3, never derived
            }
            if (! Schema::hasColumn('grades', 'project_score_raw')) {
                $table->decimal('project_score_raw', 6, 2)->nullable();
            }
            if (! Schema::hasColumn('grades', 'project_score_max')) {
                $table->decimal('project_score_max', 6, 2)->nullable();
            }
            if (! Schema::hasColumn('grades', 'project_status')) {
                $table->string('project_status', 30)->nullable();
            }
        });

        // DB-level identifier check where supported (SQLite can't add checks to an
        // existing table; the Grade model enforces 1/2/3 for every driver).
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE grades ADD CONSTRAINT grades_identifier_check CHECK (identifier IN (1, 2, 3))');
            } catch (\Throwable $e) {
                // Constraint already exists or MySQL < 8.0.16 (parses but ignores checks).
            }
        }

        Setting::firstOrCreate(
            ['key' => 'activity_scale_config'],
            [
                'value' => json_encode(['activity_max_score' => 3, 'project_max_score' => 10]),
                'type' => 'textarea',
                'group' => 'academic',
                'label' => 'O-Level Activity Scale',
                'description' => 'JSON config for Activities of Integration. activity_max_score = maximum raw score per activity (default 3); project_max_score = maximum project-work score (default 10).',
                'sort_order' => 46,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'ca_weight_config'],
            [
                'value' => json_encode(['ca' => 20, 'eot' => 80]),
                'type' => 'textarea',
                'group' => 'academic',
                'label' => 'O-Level CA/EOT Weights',
                'description' => 'JSON config for the continuous assessment vs end-of-term split. ca + eot must sum to 100 (default 20/80).',
                'sort_order' => 47,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', ['activity_scale_config', 'ca_weight_config'])->delete();

        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE grades DROP CONSTRAINT grades_identifier_check');
            } catch (\Throwable $e) {
                // Constraint was never created.
            }
        }

        Schema::table('grades', function (Blueprint $table) {
            foreach (['eot_raw_score', 'eot_max_score', 'eot_status', 'identifier', 'project_score_raw', 'project_score_max', 'project_status'] as $column) {
                if (Schema::hasColumn('grades', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('activity_scores');
    }
};
