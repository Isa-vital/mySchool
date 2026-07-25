<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CHANGED (bugfix): grades.achievement_level was varchar(5) — sized for a bare
     * "A"-"E" letter — but grade saves store the full band descriptor (e.g.
     * "Exceptional", "Satisfactory", "Subsidiary Pass"), which failed with
     * "Data too long for column" under MySQL strict mode. Descriptors are also
     * admin-configurable JSON, so allow a comfortable length.
     */
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->string('achievement_level', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Clear any long descriptors before shrinking the column back.
        DB::table('grades')
            ->whereRaw('CHAR_LENGTH(achievement_level) > 5')
            ->update(['achievement_level' => null]);

        Schema::table('grades', function (Blueprint $table) {
            $table->string('achievement_level', 5)->nullable()->change();
        });
    }
};
