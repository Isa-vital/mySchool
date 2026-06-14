<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Uganda EMIS (Education Management Information System) identifiers:
 *  - lin                : Learner Identification Number (unique, permanent national learner ID).
 *  - uneb_index_number  : UNEB candidate index number (CENTRE/CANDIDATE) for exam classes.
 * The school-level EMIS number and UNEB centre number are stored in settings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('lin', 30)->nullable()->unique()->after('admission_number'); // EMIS Learner Identification Number
            $table->string('uneb_index_number', 30)->nullable()->after('lin');           // UNEB index number for candidates
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['lin', 'uneb_index_number']);
        });
    }
};
