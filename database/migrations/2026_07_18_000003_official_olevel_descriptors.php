<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * CHANGED (A4): align O-Level competency descriptors with the official UNEB/NCDC
     * wording — A = Exceptional, B = Outstanding, C = Satisfactory, D = Basic,
     * E = Elementary (previously "Excellent / Very Good / Satisfactory / Basic /
     * Developing"). Boundaries and points are preserved; only descriptions change.
     */
    private const OFFICIAL = [
        'A' => 'Exceptional',
        'B' => 'Outstanding',
        'C' => 'Satisfactory',
        'D' => 'Basic',
        'E' => 'Elementary',
    ];

    private const LEGACY = [
        'A' => 'Excellent Competency',
        'B' => 'Very Good Competency',
        'C' => 'Satisfactory Competency',
        'D' => 'Basic Competency',
        'E' => 'Developing Competency',
    ];

    public function up(): void
    {
        $this->rewriteDescriptors(self::OFFICIAL);
    }

    public function down(): void
    {
        $this->rewriteDescriptors(self::LEGACY);
    }

    private function rewriteDescriptors(array $map): void
    {
        $setting = Setting::where('key', 'olevel_competency_scale')->first();
        if (! $setting || ! is_string($setting->value)) {
            return;
        }

        $bands = json_decode($setting->value, true);
        if (! is_array($bands)) {
            return;
        }

        foreach ($bands as &$band) {
            $grade = strtoupper((string) ($band['grade'] ?? ''));
            if (isset($map[$grade])) {
                $band['description'] = $map[$grade];
            }
        }

        $setting->update(['value' => json_encode(array_values($bands))]);
        Setting::flushCache();
    }
};
