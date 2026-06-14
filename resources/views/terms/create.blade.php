<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Term &mdash; {{ $academicYear->name }}</h2>
            <a href="{{ route('academic-years.terms.index', $academicYear) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('academic-years.terms.store', $academicYear) }}" class="max-w-2xl">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Term Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Term 1" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <label class="inline-flex items-center">
                <input type="hidden" name="is_current" value="0">
                <input type="checkbox" name="is_current" value="1" {{ old('is_current') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm">
                <span class="ml-2 text-sm text-gray-700">Set as the current term</span>
            </label>
        </div>

        <div class="flex items-center justify-end space-x-3 max-w-2xl">
            <a href="{{ route('academic-years.terms.index', $academicYear) }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Create Term</button>
        </div>
    </form>
</x-app-layout>