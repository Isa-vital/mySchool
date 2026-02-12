<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Exam</h2>
            <a href="{{ route('exams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('exams.store') }}">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Exam Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Mid-Term Exam" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year <span class="text-red-500">*</span></label>
                    <select name="academic_year_id" id="academic_year_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term <span class="text-red-500">*</span></label>
                    <select name="term_id" id="term_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select Academic Year first</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Assign to Classes --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Assign to Classes</h3>
            <p class="text-xs text-gray-500 mb-4">Exam schedules will be auto-created for every subject assigned to each selected class.</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($classes as $class)
                    <label class="flex items-center space-x-2 p-2 border rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" {{ in_array($class->id, old('class_ids', [])) ? 'checked' : '' }} class="rounded text-blue-600">
                        <span class="text-sm text-gray-700">{{ $class->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('exams.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Create Exam</button>
        </div>
    </form>

    {{-- JS: Filter terms by selected academic year --}}
    <script>
        const termsByYear = @json($academicYears->mapWithKeys(fn($y) => [$y->id => $y->terms]));
        const yearSelect = document.getElementById('academic_year_id');
        const termSelect = document.getElementById('term_id');
        const oldTermId = '{{ old("term_id") }}';

        function populateTerms() {
            const yearId = yearSelect.value;
            termSelect.innerHTML = '<option value="">Select Term</option>';
            if (yearId && termsByYear[yearId]) {
                termsByYear[yearId].forEach(term => {
                    const opt = document.createElement('option');
                    opt.value = term.id;
                    opt.textContent = term.name;
                    if (oldTermId && oldTermId == term.id) opt.selected = true;
                    termSelect.appendChild(opt);
                });
            }
        }

        yearSelect.addEventListener('change', populateTerms);
        if (yearSelect.value) populateTerms();
    </script>
</x-app-layout>
