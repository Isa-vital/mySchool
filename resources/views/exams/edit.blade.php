<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit: {{ $exam->name }}</h2>
            <a href="{{ route('exams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('exams.update', $exam) }}">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Exam Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $exam->name) }}" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year <span class="text-red-500">*</span></label>
                    <select name="academic_year_id" id="academic_year_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id', $exam->academic_year_id) == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term <span class="text-red-500">*</span></label>
                    <select name="term_id" id="term_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select Term</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $exam->start_date?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $exam->end_date?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Card Format <span class="text-red-500">*</span></label>
                    <select name="assessment_format" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" required>
                        <option value="primary" {{ old('assessment_format', $exam->assessment_format ?? setting('report_card_format', 'primary')) === 'primary' ? 'selected' : '' }}>Primary</option>
                        <option value="o-level" {{ old('assessment_format', $exam->assessment_format ?? setting('report_card_format', 'primary')) === 'o-level' ? 'selected' : '' }}>O-Level</option>
                        <option value="a-level" {{ old('assessment_format', $exam->assessment_format ?? setting('report_card_format', 'primary')) === 'a-level' ? 'selected' : '' }}>A-Level</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Marks / Points</label>
                    <input type="number" name="max_points" min="1" max="500" value="{{ old('max_points', $exam->max_points ?? 100) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div class="flex items-center pt-6">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $exam->is_published) ? 'checked' : '' }} class="rounded text-green-600">
                        <span class="text-sm font-medium text-gray-700">Published</span>
                    </label>
                    <p class="text-xs text-gray-500 ml-2">(Students & guardians can view report cards)</p>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('description', $exam->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('exams.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Update Exam</button>
        </div>
    </form>

    {{-- JS: Filter terms by selected academic year --}}
    <script>
        const termsByYear = @json($academicYears - > mapWithKeys(fn($y) => [$y - > id => $y - > terms]));
        const yearSelect = document.getElementById('academic_year_id');
        const termSelect = document.getElementById('term_id');
        const currentTermId = '{{ old("term_id", $exam->term_id) }}';

        function populateTerms() {
            const yearId = yearSelect.value;
            termSelect.innerHTML = '<option value="">Select Term</option>';
            if (yearId && termsByYear[yearId]) {
                termsByYear[yearId].forEach(term => {
                    const opt = document.createElement('option');
                    opt.value = term.id;
                    opt.textContent = term.name;
                    if (currentTermId == term.id) opt.selected = true;
                    termSelect.appendChild(opt);
                });
            }
        }

        yearSelect.addEventListener('change', populateTerms);
        populateTerms();
    </script>
</x-app-layout>