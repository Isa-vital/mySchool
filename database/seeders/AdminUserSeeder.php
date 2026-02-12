<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@myschool.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
                'phone' => '',
                'is_active' => true,
            ]
        );

        $admin->assignRole('Super Admin');
    }
}
