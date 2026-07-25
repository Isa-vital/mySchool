<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CHANGED (A6): subjects can be assessed in multiple weighted components within ONE
     * exam (e.g. Physics: Paper 1 theory 60 + Paper 2 practical 40). Previously grades
     * were hard-limited to a single score per (exam, student, subject).
     */
    public function up(): void
    {
        // CHANGED: guards added — an interrupted first run left the table/column in place
        // without the index swap, so every step must be individually re-runnable.
        if (! Schema::hasTable('subject_components')) {
            Schema::create('subject_components', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                $table->string('name'); // e.g. "Paper 1 (Theory)"
                $table->decimal('weight', 6, 2)->default(1);
                $table->decimal('max_score', 6, 2)->default(100);
                $table->timestamps();

                $table->unique(['subject_id', 'name']);
            });
        }

        if (! Schema::hasColumn('grades', 'subject_component_id')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->foreignId('subject_component_id')->nullable()->after('subject_id')
                    ->constrained('subject_components')->nullOnDelete();
            });
        }

        // CHANGED: Schema::getIndexes() instead of MySQL-only "SHOW INDEX" — the test
        // suite runs on SQLite and every RefreshDatabase test died on that statement.
        $indexes = collect(Schema::getIndexes('grades'))->pluck('name');

        // One row per component; NULL component = whole-subject score (legacy shape).
        // CHANGED: the NEW index must be added BEFORE the old one is dropped — MySQL uses
        // the old composite unique to satisfy the exam_id foreign key (error 1553), and
        // both indexes start with exam_id so the FK can fall back to the new one.
        Schema::table('grades', function (Blueprint $table) use ($indexes) {
            if (! $indexes->contains('grades_exam_student_subject_component_unique')) {
                $table->unique(['exam_id', 'student_id', 'subject_id', 'subject_component_id'], 'grades_exam_student_subject_component_unique');
            }
        });

        Schema::table('grades', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('grades_exam_id_student_id_subject_id_unique')) {
                $table->dropUnique(['exam_id', 'student_id', 'subject_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // Reverse order of up(): restore the legacy index first, then drop the new one.
            $table->unique(['exam_id', 'student_id', 'subject_id']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropUnique('grades_exam_student_subject_component_unique');
            $table->dropConstrainedForeignId('subject_component_id');
        });

        Schema::dropIfExists('subject_components');
    }
};
