<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ProductionFullDataSeeder extends Seeder
{
    /**
     * Seed production-safe bootstrap + full sample data.
     *
     * Notes:
     * - Does NOT call SettingsSeeder, so existing school settings are preserved.
     * - Is safe to run repeatedly for roles/admin user.
     * - SampleDataSeeder runs only on an empty academic/student dataset.
     */
    public function run(): void
    {
        $this->command?->info('Running ProductionFullDataSeeder...');

        // Keep permissions/roles/user aligned across environments.
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Avoid duplicate heavy sample data in production.
        if (AcademicYear::exists() || Student::exists()) {
            $this->command?->warn('Academic years or students already exist. Skipping SampleDataSeeder to avoid duplicates.');
            $this->command?->info('If you intentionally want sample data, seed manually on a clean database.');
            return;
        }

        $this->call([
            SampleDataSeeder::class,
        ]);

        $this->command?->info('ProductionFullDataSeeder completed.');
    }
}
