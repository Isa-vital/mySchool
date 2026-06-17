<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">System Settings</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
            @csrf

            @foreach($settings as $group => $items)
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 capitalize">{{ str_replace('_', ' ', $group) }}</h3>

                <div class="space-y-5">
                    @foreach($items as $setting)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="setting_{{ $setting->key }}">
                            {{ $setting->label ?? ucwords(str_replace('_', ' ', $setting->key)) }}
                        </label>

                        @if($setting->type === 'text')
                        <input type="text" name="{{ $setting->key }}" id="setting_{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}" class="w-full rounded-lg border-gray-300 text-sm">
                        @elseif($setting->type === 'textarea')
                        <textarea name="{{ $setting->key }}" id="setting_{{ $setting->key }}" rows="3" class="w-full rounded-lg border-gray-300 text-sm">{{ old($setting->key, $setting->value) }}</textarea>
                        @elseif($setting->type === 'number')
                        <input type="number" name="{{ $setting->key }}" id="setting_{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}" class="w-full rounded-lg border-gray-300 text-sm">
                        @elseif($setting->type === 'email')
                        <input type="email" name="{{ $setting->key }}" id="setting_{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}" class="w-full rounded-lg border-gray-300 text-sm">
                        @elseif($setting->type === 'color')
                        <div class="flex items-center space-x-3">
                            <input type="color" name="{{ $setting->key }}" id="setting_{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}" class="h-10 w-16 rounded border-gray-300 cursor-pointer">
                            <span class="text-sm text-gray-500">{{ $setting->value }}</span>
                        </div>
                        @elseif($setting->type === 'select')
                        @php
                        $decodedOptions = json_decode($setting->options ?? '', true);
                        $selectOptions = is_array($decodedOptions)
                        ? $decodedOptions
                        : collect(explode(',', $setting->options ?? ''))
                        ->filter(fn ($opt) => trim($opt) !== '')
                        ->mapWithKeys(fn ($opt) => [trim($opt) => trim($opt)])
                        ->all();
                        @endphp
                        <select name="{{ $setting->key }}" id="setting_{{ $setting->key }}" class="w-full rounded-lg border-gray-300 text-sm">
                            @foreach($selectOptions as $optValue => $optLabel)
                            <option value="{{ $optValue }}" {{ old($setting->key, $setting->value) == $optValue ? 'selected' : '' }}>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                        @elseif($setting->type === 'boolean')
                        <div class="flex items-center">
                            <input type="hidden" name="{{ $setting->key }}" value="0">
                            <input type="checkbox" name="{{ $setting->key }}" id="setting_{{ $setting->key }}" value="1" {{ old($setting->key, $setting->value) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                            <label for="setting_{{ $setting->key }}" class="ml-2 text-sm text-gray-600">Enabled</label>
                        </div>
                        @elseif($setting->type === 'image')
                        <div class="flex items-center space-x-4">
                            @if($setting->value)
                            <img src="{{ Storage::url($setting->value) }}" alt="{{ $setting->key }}" class="h-16 w-16 object-cover rounded-lg border">
                            @endif
                            <input type="file" name="{{ $setting->key }}" id="setting_{{ $setting->key }}" accept="image/*" class="text-sm text-gray-600">
                        </div>
                        @else
                        <input type="text" name="{{ $setting->key }}" id="setting_{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}" class="w-full rounded-lg border-gray-300 text-sm">
                        @endif

                        @if($setting->description)
                        <p class="text-xs text-gray-400 mt-1">{{ $setting->description }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <div class="flex justify-end mb-8">
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Save Settings</button>
            </div>
        </form>
    </div>
</x-app-layout>