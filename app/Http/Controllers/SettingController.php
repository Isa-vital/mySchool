<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected array $jsonScaleKeys = [
        'primary_achievement_levels',
        'olevel_competency_scale',
        'alevel_grade_scale',
    ];

    public function index()
    {
        $settings = Setting::allGrouped();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token', '_method');

        foreach ($data as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if (!$setting) continue;

            if (in_array($key, $this->jsonScaleKeys, true)) {
                $this->validateJsonScaleSetting($key, $value);
            }

            if ($setting->type === 'image' && $request->hasFile($key)) {
                // Delete old image
                if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }
                $path = $request->file($key)->store('settings', 'public');
                $setting->update(['value' => $path]);
            } elseif ($setting->type === 'boolean') {
                $setting->update(['value' => $value ? '1' : '0']);
            } elseif ($setting->type !== 'image') {
                $setting->update(['value' => $value]);
            }
        }

        // Handle boolean checkboxes that weren't submitted (unchecked)
        $booleanSettings = Setting::where('type', 'boolean')->get();
        foreach ($booleanSettings as $bs) {
            if (!array_key_exists($bs->key, $data)) {
                $bs->update(['value' => '0']);
            }
        }

        Setting::flushCache();

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    protected function validateJsonScaleSetting(string $key, mixed $value): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $decoded = json_decode($value, true);
        abort_unless(is_array($decoded), 422, "Invalid JSON provided for {$key}.");

        foreach ($decoded as $index => $item) {
            abort_unless(
                is_array($item) && isset($item['grade'], $item['min'], $item['max']),
                422,
                "Each grading band in {$key} must include grade, min and max (item " . ($index + 1) . ")."
            );
        }
    }
}
