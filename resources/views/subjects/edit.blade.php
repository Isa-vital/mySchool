<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit: {{ $subject->name }}</h2>
            <a href="{{ route('subjects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('subjects.update', $subject) }}">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $subject->code) }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $subject->name) }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        <option value="core" {{ old('type', $subject->type) === 'core' ? 'selected' : '' }}>Core</option>
                        <option value="elective" {{ old('type', $subject->type) === 'elective' ? 'selected' : '' }}>Elective</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="is_active" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="1" {{ old('is_active', $subject->is_active) ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !old('is_active', $subject->is_active) ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('description', $subject->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- CHANGED (A6): weighted assessment components (e.g. Paper 1 theory 60 / Paper 2 practical 40).
             Leave empty for a single-score subject. --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-800">Assessment Components (optional)</h3>
            <p class="text-xs text-gray-500 mt-0.5 mb-4">For subjects marked in separate papers/practicals. Each component gets its own marks column at grade entry; the subject grade combines them by weight. Leave blank for one score per exam. Avoid removing components mid-term — entered scores stay but lose their component link.</p>
            <div class="space-y-2">
                @php $componentRows = old('components', $subject->components->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'weight' => (float) $c->weight, 'max_score' => (float) $c->max_score])->all()); @endphp
                @foreach(array_pad($componentRows, count($componentRows) + 2, ['id' => '', 'name' => '', 'weight' => '', 'max_score' => '']) as $i => $row)
                <div class="flex gap-3 items-center">
                    <input type="hidden" name="components[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                    <div class="flex-1">
                        <input type="text" name="components[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" placeholder="e.g. Paper 1 (Theory)" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="w-28">
                        <input type="number" step="0.01" min="0.01" name="components[{{ $i }}][weight]" value="{{ $row['weight'] ?? '' }}" placeholder="Weight" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="w-28">
                        <input type="number" step="0.5" min="1" name="components[{{ $i }}][max_score]" value="{{ $row['max_score'] ?? '' }}" placeholder="Max /100" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('subjects.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Update Subject</button>
        </div>
    </form>
</x-app-layout>