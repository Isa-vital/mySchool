<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Previous school details (school name already exists as `previous_school`)
            $table->string('previous_school_grade')->nullable()->after('previous_school'); // last grade/class attended
            $table->string('previous_school_attachment')->nullable()->after('previous_school_grade'); // transfer letter / report card file
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['previous_school_grade', 'previous_school_attachment']);
        });
    }
};
