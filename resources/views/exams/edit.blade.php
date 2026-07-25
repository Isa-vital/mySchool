<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit: {{ $exam->name }}</h2>
            <a href="{{ route('exams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    {{-- CHANGED (rebuilt Jul 18): a code formatter reformatted this file as JavaScript and destroyed it.
         Rebuilt with ALL script data prepared in ExamController::edit() — no PHP expressions live in
         @php or <script> here, so a formatter cannot corrupt the page again.
         DO NOT RUN "FORMAT DOCUMENT" ON BLADE FILES. --}}

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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Card Format</label>
                    {{-- CHANGED (A1): auto-detect is the empty value (stored as NULL); format resolves
                         from each student's class (P.1-P.7 primary, S.1-S.4 o-level, S.5-S.6 a-level). --}}
                    <select name="assessment_format" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="" {{ old('assessment_format', $exam->assessment_format ?? '') === '' ? 'selected' : '' }}>Auto (based on class)</option>
                        <option value="primary" {{ old('assessment_format', $exam->assessment_format) === 'primary' ? 'selected' : '' }}>Primary</option>
                        <option value="o-level" {{ old('assessment_format', $exam->assessment_format) === 'o-level' ? 'selected' : '' }}>O-Level</option>
                        <option value="a-level" {{ old('assessment_format', $exam->assessment_format) === 'a-level' ? 'selected' : '' }}>A-Level</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Marks / Points</label>
                    <input type="number" name="max_points" min="1" max="500" value="{{ old('max_points', $exam->max_points ?? 100) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grading Profile</label>
                    <select name="grading_scale_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Use format default</option>
                        @foreach($gradingScales as $gradingScale)
                        <option value="{{ $gradingScale->id }}" {{ (string) old('grading_scale_id', $exam->grading_scale_id) === (string) $gradingScale->id ? 'selected' : '' }}>{{ $gradingScale->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center pt-6 space-x-6">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_report_card" id="is_report_card" value="1" {{ old('is_report_card', $exam->is_report_card) ? 'checked' : '' }} class="rounded text-blue-600">
                        <span class="text-sm font-medium text-gray-700">Use as report card exam</span>
                    </label>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('description', $exam->description) }}</textarea>
                </div>
            </div>
        </div>

        <div id="report-components-panel" class="bg-white rounded-xl shadow-sm border p-6 mb-6 {{ old('is_report_card', $exam->is_report_card) ? '' : 'hidden' }}">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Report Card Composition</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pick the exams that make up this report and give each a weight — e.g. Exam 1: 40, Exam 2: 40, Exam 3: 20. The total is normalized to 100 automatically.</p>
                </div>
                <button type="button" id="add-component-btn" class="px-3 py-1.5 text-xs font-medium text-white rounded-lg shrink-0" style="background: var(--primary-color);">+ Add exam set</button>
            </div>

            <div id="components-list" class="space-y-2">
                {{-- rows injected by JS --}}
            </div>

            <p class="text-xs text-gray-400 mt-3">Weights can be anything — 40/40/20, 1/2/3, percentages, or equal values.</p>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('exams.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Update Exam</button>
        </div>
    </form>

    {{-- All constants below come from ExamController::edit(); keep this block free of PHP expressions. --}}
    <script>
        const termsByYear = @json($termsByYearData);
        const examOptions = @json($examOptionsData);
        const savedComponents = @json($savedComponentsData);
        const existingComponents = @json($existingComponentsData);
        const preselectedTermId = @json($preselectedTermId);

        const yearSelect = document.getElementById('academic_year_id');
        const termSelect = document.getElementById('term_id');
        const reportCardCheckbox = document.getElementById('is_report_card');
        const reportComponentsPanel = document.getElementById('report-components-panel');
        const componentsList = document.getElementById('components-list');
        let componentCount = 0;

        function populateTerms() {
            const yearId = yearSelect.value;
            termSelect.innerHTML = '<option value="">Select Term</option>';
            if (yearId && termsByYear[yearId]) {
                termsByYear[yearId].forEach(term => {
                    const opt = document.createElement('option');
                    opt.value = term.id;
                    opt.textContent = term.name;
                    if (preselectedTermId && preselectedTermId == term.id) opt.selected = true;
                    termSelect.appendChild(opt);
                });
            }
        }

        function buildSelectHtml(name, selectedId) {
            let html = `<select name="${name}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm"><option value="">Select exam set</option>`;
            examOptions.forEach(e => {
                const sel = String(e.id) === String(selectedId) ? ' selected' : '';
                html += `<option value="${e.id}"${sel}>${e.label}</option>`;
            });
            html += '</select>';
            return html;
        }

        function addComponent(examId, weight) {
            const idx = componentCount++;
            const row = document.createElement('div');
            row.className = 'flex gap-3 items-center component-row';
            row.innerHTML = `
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-0.5">Exam Set</label>
                    ${buildSelectHtml('report_components[' + idx + '][exam_id]', examId ?? '')}
                </div>
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-500 mb-0.5">Weight</label>
                    <input type="number" step="0.01" min="0" name="report_components[${idx}][weight]"
                        value="${weight ?? ''}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="e.g. 40">
                </div>
                <div class="pt-5">
                    <button type="button" onclick="this.closest('.component-row').remove(); reindex();"
                        class="p-1.5 text-gray-400 hover:text-red-500 rounded" title="Remove">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>`;
            componentsList.appendChild(row);
        }

        function reindex() {
            componentsList.querySelectorAll('.component-row').forEach((row, i) => {
                row.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
                });
            });
            componentCount = componentsList.querySelectorAll('.component-row').length;
        }

        function toggleReportComponents() {
            reportComponentsPanel.classList.toggle('hidden', !reportCardCheckbox.checked);
        }

        document.getElementById('add-component-btn').addEventListener('click', () => addComponent());
        yearSelect.addEventListener('change', populateTerms);
        reportCardCheckbox.addEventListener('change', toggleReportComponents);
        populateTerms();
        toggleReportComponents();

        // Restore old input (validation failure) or existing saved components.
        const initialComponents = savedComponents.length ? savedComponents : existingComponents;
        if (initialComponents.length) {
            initialComponents.forEach(c => addComponent(c.exam_id, c.weight));
        } else {
            addComponent();
        }
    </script>
</x-app-layout>