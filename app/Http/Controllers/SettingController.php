<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
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
}
