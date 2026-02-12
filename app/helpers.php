<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get or set a setting value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}
