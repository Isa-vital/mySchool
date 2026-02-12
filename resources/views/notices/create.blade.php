<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Notice</h2>
            <a href="{{ route('notices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <form method="POST" action="{{ route('notices.store') }}">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-lg border-gray-300 text-sm" required>
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                        <textarea name="content" rows="6" class="w-full rounded-lg border-gray-300 text-sm" required>{{ old('content') }}</textarea>
                        @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ audience: '{{ old('target_audience', 'all') }}' }">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target Audience <span class="text-red-500">*</span></label>
                            <select name="target_audience" x-model="audience" class="w-full rounded-lg border-gray-300 text-sm" required>
                                <option value="all">All</option>
                                <option value="staff">Staff</option>
                                <option value="students">Students</option>
                                <option value="parents">Parents</option>
                                <option value="specific_class">Specific Class</option>
                            </select>
                        </div>

                        <div x-show="audience === 'specific_class'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                            <select name="school_class_id" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                            <input type="date" name="publish_date" value="{{ old('publish_date', date('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                            <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1" id="is_published" {{ old('is_published', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                        <label for="is_published" class="ml-2 text-sm text-gray-700">Publish immediately</label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Create Notice</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
