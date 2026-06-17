<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Enter Marks: {{ $reportExam->name }}
                @if($subject) — {{ $subject->name }}@endif
            </h2>
            <a href="{{ route('grades.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    {{-- Selectors --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('grades.enter-grid', $reportExam) }}" class="flex flex-wrap gap-4 items-end">
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Class <span class="text-red-500">*</span></label>
                <select name="class_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                <select name="subject_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ $selectedSubjectId == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Load</button>
        </form>
    </div>

    @if($subject && $students->count() && $componentExams->count())
    {{-- Info bar --}}
    <div class="mb-4 flex flex-wrap gap-3 text-sm text-gray-600 items-center">
        <span class="font-medium text-gray-800">{{ $reportExam->name }}</span>
        <span class="text-gray-400">·</span>
        <span>{{ $subject->name }}</span>
        <span class="text-gray-400">·</span>
        <span>{{ $students->count() }} students</span>
        <span class="text-gray-400">·</span>
        @foreach($componentExams as $compExam)
        <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
            {{ $compExam->name }}
            <span class="text-blue-400">/{{ $fullMarks[$compExam->id] ?? 100 }}</span>
            <span class="text-blue-300">(w:{{ number_format((float)($compExam->pivot->weight ?? 0), 1) }})</span>
        </span>
        @endforeach
    </div>

    <form method="POST" action="{{ route('grades.save-grid', $reportExam) }}"
          x-data="gradeGrid({{ $componentExams->count() }})"
          @submit.prevent="confirmSave()">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            {{-- Sticky header with save button --}}
            <div class="sticky top-0 z-20 px-4 sm:px-6 py-3 border-b bg-white flex items-center justify-between gap-4">
                <span class="text-sm text-gray-500">
                    Entered: <span class="font-semibold text-gray-900" x-text="enteredCount"></span> / {{ $students->count() }}
                    &nbsp;·&nbsp;
                    <span class="text-xs">Columns auto-scale to 100%</span>
                </span>
                <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white rounded-lg shadow transition-opacity"
                    style="background: var(--primary-color);"
                    :class="dirty ? 'opacity-100' : 'opacity-60'"
                    x-text="dirty ? 'Save Changes' : 'No Changes'">
                </button>
            </div>

            <div class="overflow-x-auto">
                @php
                    // CHANGED: precompute exams payload for Alpine to avoid Blade parser issues
                    // with nested inline arrays/functions inside HTML attributes.
                    $gridExamsPayload = $componentExams->map(function ($e) use ($fullMarks) {
                        return [
                            'id' => (int) $e->id,
                            'weight' => (float) ($e->pivot->weight ?? 0),
                            'full' => (float) ($fullMarks[$e->id] ?? 100),
                        ];
                    })->values();
                @endphp
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50 z-10">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sticky left-8 bg-gray-50 z-10 min-w-[160px]">Student</th>
                            @foreach($componentExams as $compExam)
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase min-w-[110px]">
                                <div>{{ $compExam->name }}</div>
                                <div class="font-normal text-gray-400 normal-case">/ {{ $fullMarks[$compExam->id] ?? 100 }} · w={{ number_format((float)($compExam->pivot->weight ?? 0), 1) }}</div>
                            </th>
                            @endforeach
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase min-w-[90px]">
                                <div>Weighted</div>
                                <div class="font-normal text-gray-400 normal-case">/ 100</div>
                            </th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase min-w-[80px]">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="grade-grid-body">
                        @foreach($students as $i => $student)
                        @php
                            $rowMarks = [];
                            foreach ($componentExams as $compExam) {
                                $rowMarks[$compExam->id] = $gridGrades[$student->id][$compExam->id] ?? null;
                            }
                        @endphp
                        <tr class="hover:bg-gray-50" x-data='gradeRow({{ $weightTotal }}, @json($gridExamsPayload))' @input="onInput(); $dispatch('row-updated')">
                            <td class="px-4 py-2 text-gray-400 sticky left-0 bg-white">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 sticky left-8 bg-white">
                                <div class="font-medium text-gray-900">{{ $student->full_name }}</div>
                                <div class="text-xs text-gray-400">{{ $student->admission_number }}</div>
                            </td>
                            @foreach($componentExams as $compExam)
                            <td class="px-3 py-2 text-center">
                                <input
                                    type="number"
                                    name="grades[{{ $student->id }}][{{ $compExam->id }}]"
                                    value="{{ $rowMarks[$compExam->id] ?? '' }}"
                                    min="0" max="{{ $fullMarks[$compExam->id] ?? 100 }}" step="0.5"
                                    data-exam-id="{{ $compExam->id }}"
                                    data-full="{{ $fullMarks[$compExam->id] ?? 100 }}"
                                    placeholder="–"
                                    class="w-20 text-center rounded border-gray-300 shadow-sm text-sm focus:ring-2 focus:ring-blue-300"
                                    @keydown.enter.prevent="$el.closest('tr').nextElementSibling?.querySelector('input[data-exam-id=\'{{ $compExam->id }}\']')?.focus()"
                                    @keydown.arrow-down.prevent="$el.closest('tr').nextElementSibling?.querySelector('input[data-exam-id=\'{{ $compExam->id }}\']')?.focus()"
                                    @keydown.arrow-up.prevent="$el.closest('tr').previousElementSibling?.querySelector('input[data-exam-id=\'{{ $compExam->id }}\']')?.focus()"
                                    x-ref="mark_{{ $compExam->id }}"
                                >
                            </td>
                            @endforeach
                            <td class="px-3 py-2 text-center font-bold text-gray-800">
                                <span x-text="weighted > 0 ? weighted.toFixed(1) : '–'"></span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold"
                                    :class="gradeClass"
                                    x-text="grade || '–'">
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50 text-right">
                <button type="submit"
                    class="px-6 py-2 text-sm font-semibold text-white rounded-lg shadow"
                    style="background: var(--primary-color);">
                    Save All Grades
                </button>
            </div>
        </div>
    </form>

    @elseif($selectedClassId && $selectedSubjectId && $componentExams->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center text-yellow-700">
        This report card exam has no component exams configured.
        <a href="{{ route('exams.edit', $reportExam) }}" class="underline ml-1">Set up components →</a>
    </div>
    @elseif($selectedClassId && $selectedSubjectId)
    <div class="bg-white rounded-xl shadow-sm border p-12 text-center text-gray-500">
        No active students enrolled in this class for {{ $reportExam->academicYear->name ?? 'this year' }}.
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border p-12 text-center text-gray-500">
        Select a class and subject above to enter marks.
    </div>
    @endif

@push('scripts')
<script>
    // Grading ranges from server for live grade preview
    const gradingRanges = @json(
        collect(\App\Services\AssessmentGradingService::rangesForExam($reportExam))->map(fn($r) => [
            'grade' => $r['grade'],
            'min'   => (float) $r['min'],
            'max'   => (float) $r['max'],
        ])->values()
    );

    function resolveGrade(pct) {
        for (const r of gradingRanges) {
            if (pct >= r.min && pct <= r.max) return r.grade;
        }
        return gradingRanges.length ? gradingRanges[gradingRanges.length - 1].grade : '–';
    }

    function gradeClass(g) {
        const colorMap = {
            'Excellent': 'bg-green-100 text-green-800',
            'Very Good': 'bg-green-100 text-green-800',
            'Good': 'bg-blue-100 text-blue-800',
            'Satisfactory': 'bg-yellow-100 text-yellow-800',
            'Fair': 'bg-orange-100 text-orange-800',
            'Poor': 'bg-red-100 text-red-800',
            'Fail': 'bg-red-100 text-red-800',
        };
        return colorMap[g] || 'bg-gray-100 text-gray-700';
    }

    // Per-row Alpine component
    function gradeRow(weightTotal, exams) {
        return {
            weighted: 0,
            grade: '',
            gradeClass: 'bg-gray-100 text-gray-700',
            onInput() {
                let sum = 0;
                let hasAny = false;
                for (const exam of exams) {
                    const inp = this.$el.querySelector(`input[data-exam-id="${exam.id}"]`);
                    const val = parseFloat(inp?.value);
                    if (!isNaN(val) && inp?.value !== '') {
                        const pct = exam.full > 0 ? (val / exam.full) * 100 : 0;
                        sum += pct * (exam.weight / weightTotal);
                        hasAny = true;
                    }
                }
                this.weighted = hasAny ? Math.round(sum * 10) / 10 : 0;
                this.grade = hasAny ? resolveGrade(this.weighted) : '';
                this.gradeClass = gradeClass(this.grade);
            },
        };
    }

    // Parent form component (dirty tracking + save confirmation)
    function gradeGrid(columnCount) {
        return {
            dirty: false,
            enteredCount: 0,
            init() {
                const form = this.$el;
                form.addEventListener('input', () => { this.dirty = true; this.countEntered(); });
                this.$nextTick(() => this.countEntered());
            },
            countEntered() {
                const inputs = this.$el.querySelectorAll('input[type="number"]');
                const rows = {};
                inputs.forEach(inp => {
                    const row = inp.closest('tr');
                    if (inp.value !== '' && row) {
                        rows[row.rowIndex] = true;
                    }
                });
                this.enteredCount = Object.keys(rows).length;
            },
            confirmSave() {
                if (!this.dirty) return;
                if (confirm('Save marks for all students?')) {
                    this.$el.submit();
                }
            },
        };
    }
</script>
@endpush
</x-app-layout>
