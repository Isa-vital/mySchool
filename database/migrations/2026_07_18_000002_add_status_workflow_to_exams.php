<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * CHANGED (A2): marks lock/moderation workflow.
     * Exams now move through: draft → marks_entry_open → locked → published.
     *  - Grade writes are rejected once locked/published.
     *  - Unlocking (locked → marks_entry_open) and unpublishing require the new
     *    'exams.moderate' permission (DOS/moderator role).
     *  - Publishing is only possible from 'locked'.
     */
    public function up(): void
    {
        Schema::table('exams', function ($table) {
            $table->string('status', 20)->default('draft')->after('is_published');
        });

        // Backfill: published exams stay published; everything else opens for marks
        // entry so existing schools' current workflows are not interrupted.
        DB::table('exams')->where('is_published', true)->update(['status' => 'published']);
        DB::table('exams')->where('is_published', false)->update(['status' => 'marks_entry_open']);

        // Moderator permission — granted to Super Admin and Admin by default.
        $permission = Permission::firstOrCreate(['name' => 'exams.moderate', 'guard_name' => 'web']);
        foreach (['Super Admin', 'Admin'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            $role?->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('exams', function ($table) {
            $table->dropColumn('status');
        });

        Permission::where('name', 'exams.moderate')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
