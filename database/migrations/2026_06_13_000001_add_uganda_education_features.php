<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Uganda education-system fit:
 *  - Boarding vs day scholars
 *  - Class category (nursery / primary / O-level / A-level) for national-exam logic
 *  - Continuous assessment + achievement levels (new lower-secondary curriculum)
 *  - A-level subject combinations (PCM, PCB, HEG ...)
 *  - School requirements tracking (reams, brooms, etc.)
 *  - Persistent report cards (position, conduct, comments, national result)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Boarding vs day scholar
        Schema::table('students', function (Blueprint $table) {
            $table->string('boarding_status', 10)->default('day')->after('status'); // day, boarding
        });

        // Class category drives PLE / UCE / UACE behaviour
        Schema::table('school_classes', function (Blueprint $table) {
            $table->string('category')->nullable()->after('level'); // nursery, lower_primary, upper_primary, o_level, a_level
        });

        // New lower-secondary curriculum: continuous assessment + achievement level
        Schema::table('grades', function (Blueprint $table) {
            $table->decimal('ca_marks', 5, 2)->nullable()->after('marks_obtained'); // continuous assessment component
            $table->string('achievement_level', 5)->nullable()->after('grade_letter'); // A-E (competency based)
        });

        // A-level subject combinations
        Schema::create('subject_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // e.g. PCM, PCB, HEG, BAM
            $table->string('name');
            $table->string('level')->default('a_level');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('combination_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_combination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_principal')->default(true); // principal vs subsidiary
            $table->unique(['subject_combination_id', 'subject_id']);
        });

        // Link a student's enrolment to an A-level combination
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('subject_combination_id')->nullable()->after('section_id')->constrained()->nullOnDelete();
        });

        // School requirements (scholastic materials parents must bring)
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Ream of paper, Broom, Toilet paper
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->string('unit')->nullable(); // pieces, reams, kg
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('requirement_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_brought')->default(0);
            $table->date('submitted_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['requirement_id', 'student_id']);
        });

        // Persistent report card metadata (position computed, comments/conduct stored)
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->decimal('average', 5, 2)->nullable();
            $table->integer('aggregate')->nullable();       // PLE/UCE aggregate or UACE points
            $table->string('result')->nullable();           // Division I-IV / U, or A-level points label
            $table->integer('position')->nullable();        // position in class
            $table->integer('class_size')->nullable();
            $table->string('conduct')->nullable();          // Excellent, Good, Fair...
            $table->text('class_teacher_comment')->nullable();
            $table->text('head_teacher_comment')->nullable();
            $table->date('next_term_begins')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
        Schema::dropIfExists('requirement_submissions');
        Schema::dropIfExists('requirements');

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_combination_id');
        });

        Schema::dropIfExists('combination_subject');
        Schema::dropIfExists('subject_combinations');

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['ca_marks', 'achievement_level']);
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('boarding_status');
        });
    }
};
