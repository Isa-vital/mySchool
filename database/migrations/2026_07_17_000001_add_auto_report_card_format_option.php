<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * CHANGED: add 'auto' report card format — the format is resolved from each
     * student's class (P.1-P.7 => primary, S.1-S.4 => o-level, S.5-S.6 => a-level).
     */
    public function up(): void
    {
        $setting = Setting::where('key', 'report_card_format')->first();

        if ($setting) {
            $setting->update([
                'options' => json_encode([
                    'auto' => 'Auto (based on student class)',
                    'primary' => 'Primary',
                    'o-level' => 'O-Level',
                    'a-level' => 'A-Level',
                ]),
                'value' => 'auto',
                'description' => 'Report card format. Auto picks per student class: P.1-P.7 Primary, S.1-S.4 O-Level, S.5-S.6 A-Level.',
            ]);
        }

        Setting::flushCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $setting = Setting::where('key', 'report_card_format')->first();

        if ($setting) {
            $setting->update([
                'options' => json_encode(['primary' => 'Primary', 'o-level' => 'O-Level', 'a-level' => 'A-Level']),
                'value' => $setting->value === 'auto' ? 'primary' : $setting->value,
                'description' => 'Default assessment format for report cards (primary, o-level, a-level)',
            ]);
        }

        Setting::flushCache();
    }
};
