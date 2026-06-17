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
        Schema::table('exams', function (Blueprint $table) {
            $table->enum('assessment_format', ['primary', 'o-level', 'a-level'])->default('primary')->after('is_published');
            $table->unsignedSmallInteger('max_points')->default(100)->after('assessment_format')->comment('Max marks/points for this exam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['assessment_format', 'max_points']);
        });
    }
};
