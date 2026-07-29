<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// CHANGED (verification): unguessable per-report serial so printed report cards can be
// verified against the live database via a public /verify/{code} page (anti-forgery).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->string('verification_code', 32)->nullable()->unique()->after('next_term_begins');
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropUnique(['verification_code']);
            $table->dropColumn('verification_code');
        });
    }
};
