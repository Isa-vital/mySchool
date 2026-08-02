<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// UACE paper-level grading (A-Level rebuild): subjects are composed of 2-4
// independently marked papers; letter grades come from a versioned rule table,
// never from a blended percentage. All UNEB-specific values live in data
// versioned by exam_cycle_id so ruleset changes never mutate history.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedTinyInteger('paper_count')->nullable()->after('type');
            $table->string('subject_category', 20)->default('non_science')->after('paper_count');
            $table->boolean('is_subsidiary')->default(false)->after('subject_category');
        });

        // Ruleset version — one active at a time; clone-then-edit, never overwrite active.
        Schema::create('exam_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('paper_band_boundaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_cycle_id')->constrained()->cascadeOnDelete();
            $table->string('band_code', 5); // D1..F9
            $table->decimal('min_pct', 5, 2);
            $table->decimal('max_pct', 5, 2);
            $table->unique(['exam_cycle_id', 'band_code']);
        });

        Schema::create('combination_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_cycle_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('paper_count');
            $table->unsignedInteger('rule_order'); // first match wins
            $table->json('matcher');               // rule DSL, see UaceGradingEngine
            $table->string('subject_category_override', 20)->nullable(); // e.g. 'science'
            $table->string('resulting_grade', 2);  // A/B/C/D/E/O/F
            $table->string('description')->nullable();
            $table->index(['exam_cycle_id', 'paper_count', 'rule_order']);
        });

        // A-Level points ONLY — deliberately separate from any O-Level scale.
        Schema::create('uace_grade_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_cycle_id')->constrained()->cascadeOnDelete();
            $table->string('grade_code', 2);
            $table->unsignedTinyInteger('points');
            $table->unique(['exam_cycle_id', 'grade_code']);
        });

        Schema::create('subsidiary_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_cycle_id')->constrained()->cascadeOnDelete();
            $table->decimal('pass_threshold_pct', 5, 2)->default(40);
            $table->unsignedTinyInteger('pass_points')->default(1);
            $table->unsignedTinyInteger('fail_points')->default(0);
        });

        // Paper definitions (what prints on the card), not results.
        Schema::create('subject_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('paper_number');
            $table->string('paper_code')->nullable();   // e.g. P210/1
            $table->string('display_name')->nullable(); // e.g. Paper 1: Mechanics
            $table->timestamps();
            $table->unique(['subject_id', 'paper_number']);
        });

        // Per-paper results. status is explicit — a raw 0 NEVER means absent.
        Schema::create('paper_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete(); // the sitting
            $table->unsignedTinyInteger('paper_number');
            $table->decimal('raw_percentage', 5, 2)->nullable();
            $table->string('status', 10)->default('scored'); // scored|absent|withheld
            $table->string('paper_grade', 5)->nullable();    // D1..F9 when scored
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['enrollment_id', 'subject_id', 'exam_id', 'paper_number'], 'paper_results_unique');
        });

        // Pin each exam to the ruleset version active when it was sat.
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('exam_cycle_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exams', fn(Blueprint $table) => $table->dropConstrainedForeignId('exam_cycle_id'));
        Schema::dropIfExists('paper_results');
        Schema::dropIfExists('subject_papers');
        Schema::dropIfExists('subsidiary_configs');
        Schema::dropIfExists('uace_grade_points');
        Schema::dropIfExists('combination_rules');
        Schema::dropIfExists('paper_band_boundaries');
        Schema::dropIfExists('exam_cycles');
        Schema::table('subjects', fn(Blueprint $table) => $table->dropColumn(['paper_count', 'subject_category', 'is_subsidiary']));
    }
};
