<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CHANGED (A1): auto-detect assessment format is represented as NULL, resolved at
     * read time from the student's class (AssessmentGradingService::resolveFormat).
     * The old enum('primary','o-level','a-level') rejected the 'auto' sentinel the
     * application had started saving, which broke exam creation under strict mode.
     */
    public function up(): void
    {
        Schema::table('exams', function ($table) {
            $table->string('assessment_format', 20)->nullable()->default(null)->change();
        });

        // Clean any sentinel values that slipped in while MySQL ran non-strict.
        DB::table('exams')->whereIn('assessment_format', ['auto', ''])->update(['assessment_format' => null]);
    }

    public function down(): void
    {
        DB::table('exams')->whereNull('assessment_format')->update(['assessment_format' => 'primary']);

        Schema::table('exams', function ($table) {
            $table->enum('assessment_format', ['primary', 'o-level', 'a-level'])->default('primary')->change();
        });
    }
};
