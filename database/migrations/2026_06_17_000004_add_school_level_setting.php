<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'school_level'],
            [
                'value' => 'both',
                'type' => 'select',
                'group' => 'academic',
                'label' => 'School Level',
                'description' => 'Choose which class levels are active in this school.',
                'options' => json_encode([
                    'primary' => 'Primary (P.1-P.7)',
                    'secondary' => 'Secondary (S.1-S.6)',
                    'both' => 'Both (P.1-P.7 and S.1-S.6)',
                ]),
                'sort_order' => 39,
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'school_level')->delete();
    }
};