<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Subject</h2>
            <a href="{{ route('subjects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('subjects.store') }}">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        <option value="core" {{ old('type') === 'core' ? 'selected' : '' }}>Core</option>
                        <option value="elective" {{ old('type') === 'elective' ? 'selected' : '' }}>Elective</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- CHANGED (UACE paper rebuild): A-Level paper structure (see edit form). --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-800">A-Level (UACE) Settings</h3>
            <p class="text-xs text-gray-500 mt-0.5 mb-4">Only needed for subjects taught at S.5/S.6. Set the number of UACE papers for principal subjects, or tick Subsidiary for GP / Sub-Math / Sub-ICT (single paper, Pass/Fail).</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UACE Papers</label>
                    <select name="paper_count" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Not an A-Level principal</option>
                        @foreach([2, 3, 4] as $n)
                        <option value="{{ $n }}" {{ old('paper_count') === (string) $n ? 'selected' : '' }}>{{ $n }} papers</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="subject_category" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="non_science" {{ old('subject_category', 'non_science') === 'non_science' ? 'selected' : '' }}>Non-science / Arts</option>
                        <option value="science" {{ old('subject_category') === 'science' ? 'selected' : '' }}>Science</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Sciences use stricter fail rules in the combination table.</p>
                </div>
                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_subsidiary" value="1" class="rounded border-gray-300" {{ old('is_subsidiary') ? 'checked' : '' }}>
                        Subsidiary (GP / Sub-Math / Sub-ICT)
                    </label>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3">Paper names/codes can be added after creating the subject (edit page).</p>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('subjects.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Create Subject</button>
        </div>
    </form>
</x-app-layout>