<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Term Exam Setup</h2>
            <a href="{{ route('exams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    {{-- CHANGED (UX): one-page wizard — creates all the term's exam sets, their class
         schedules, and the composite report card exam in a single submission. --}}
    {{-- CHANGED (QA fix): rows are prefilled from the selected term's EXISTING exams so
         the wizard reuses them (matching is by exact name) instead of silently creating
         empty duplicates. Each row shows whether it reuses or creates an exam. --}}
    @php
    $termOptions = $academicYears->flatMap(fn($y) => $y->terms->map(fn($t) => ['id' => $t->id, 'label' => $t->name . ' (' . $y->name . ')']));
    @endphp

    <form method="POST" action="{{ route('exams.term-setup.store') }}" class="max-w-3xl mx-auto space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">1. Which term?</h3>
            <p class="text-xs text-gray-500 mb-3">Everything below is created inside this term.</p>
            <select name="term_id" id="term-select" required class="w-full md:w-80 rounded-lg border-gray-300 shadow-sm text-sm">
                <option value="">Select Term</option>
                @foreach($termOptions as $option)
                <option value="{{ $option['id'] }}" {{ (string) old('term_id', $currentTerm?->id) === (string) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                @endforeach
            </select>
            @error('term_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-semibold text-gray-800">2. Exam sets</h3>
                <button type="button" id="add-set-btn" class="px-3 py-1.5 text-xs font-medium text-white rounded-lg" style="background: var(--primary-color);">+ Add set</button>
            </div>
            <p class="text-xs text-gray-500 mb-3">Each set becomes an exam with schedules auto-created for every subject of the selected classes. Weights decide how sets combine on the report card (any scale — normalized automatically). Leave a name blank to skip it.</p>
            <div id="sets-list" class="space-y-2">
                {{-- rows rendered by the script below --}}
            </div>
            @error('sets') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">3. Report card</h3>
            <label class="flex items-center space-x-2 cursor-pointer mb-3">
                <input type="checkbox" name="create_report" value="1" {{ old('create_report', '1') ? 'checked' : '' }} class="rounded text-blue-600">
                <span class="text-sm font-medium text-gray-700">Create the composite report card exam (combines the sets above by weight)</span>
            </label>
            <input type="text" name="report_name" id="report-name-input" value="{{ old('report_name') }}" placeholder="Report exam name (default: “<Term> Report Card”)" class="w-full md:w-96 rounded-lg border-gray-300 shadow-sm text-sm">
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">4. Classes</h3>
            <p class="text-xs text-gray-500 mb-3">Leave all unticked to include <strong>every active class</strong>.</p>
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
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Set Up Term Exams</button>
        </div>
    </form>

    {{-- All constants below come from ExamController::termSetup(); keep this block free
         of PHP expressions (formatters corrupt Blade inside <script>). --}}
    <script>
        const termPrefill = @json($termPrefillData);
        const oldSets = @json($oldSetsData);

        const setsList = document.getElementById('sets-list');
        const termSelect = document.getElementById('term-select');
        const reportNameInput = document.getElementById('report-name-input');
        const defaultSets = [{
                name: 'Beginning of Term',
                weight: 20
            },
            {
                name: 'Mid Term',
                weight: 30
            },
            {
                name: 'End of Term',
                weight: 50
            },
        ];
        let rowCount = 0;

        function existingNamesForTerm() {
            const prefill = termPrefill[termSelect.value];
            return prefill ? prefill.sets.map(s => s.name.toLowerCase()) : [];
        }

        function updateBadge(row) {
            const name = row.querySelector('input[type=text]').value.trim().toLowerCase();
            const badge = row.querySelector('.set-badge');
            if (!name) {
                badge.textContent = 'skipped';
                badge.className = 'set-badge shrink-0 w-28 text-center px-2 py-1 text-[11px] font-medium rounded-full bg-gray-100 text-gray-500';
            } else if (existingNamesForTerm().includes(name)) {
                badge.textContent = '✓ reuses existing';
                badge.className = 'set-badge shrink-0 w-28 text-center px-2 py-1 text-[11px] font-medium rounded-full bg-green-100 text-green-700';
            } else {
                badge.textContent = 'new exam';
                badge.className = 'set-badge shrink-0 w-28 text-center px-2 py-1 text-[11px] font-medium rounded-full bg-blue-100 text-blue-700';
            }
        }

        function addRow(name, weight) {
            const idx = rowCount++;
            const row = document.createElement('div');
            row.className = 'flex gap-3 items-center set-row';
            row.innerHTML = `
                <div class="flex-1">
                    <input type="text" name="sets[${idx}][name]" value="" placeholder="Exam set name" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div class="w-28">
                    <input type="number" step="0.01" min="0" name="sets[${idx}][weight]" value="" placeholder="Weight" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <span class="set-badge shrink-0 w-28 text-center px-2 py-1 text-[11px] font-medium rounded-full bg-gray-100 text-gray-500"></span>`;
            const nameInput = row.querySelector('input[type=text]');
            nameInput.value = name ?? '';
            row.querySelector('input[type=number]').value = weight ?? '';
            nameInput.addEventListener('input', () => updateBadge(row));
            setsList.appendChild(row);
            updateBadge(row);
        }

        function renderRows(sets) {
            setsList.innerHTML = '';
            rowCount = 0;
            sets.forEach(s => addRow(s.name, s.weight));
        }

        function renderForTerm() {
            const prefill = termPrefill[termSelect.value];
            if (prefill && prefill.sets.length) {
                renderRows(prefill.sets);
                if (reportNameInput && !reportNameInput.value && prefill.report_name) {
                    reportNameInput.value = prefill.report_name;
                }
            } else {
                renderRows(defaultSets);
            }
        }

        document.getElementById('add-set-btn').addEventListener('click', () => addRow('', ''));
        termSelect.addEventListener('change', renderForTerm);

        // Old input (validation failure) wins over the term prefill.
        if (oldSets.length) {
            renderRows(oldSets);
        } else {
            renderForTerm();
        }
    </script>
</x-app-layout>