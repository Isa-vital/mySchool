@props([
'action',
'students',
'existingGrades',
'subject',
'classId',
'className' => '',
'examName' => '',
'gradingRanges' => null,
'fullMarks' => 100,
'passMarks' => 40,
'exam' => null,
])

@php
// Normalise existing grades access (collection keyed by student_id).
$rows = $students->values()->map(function ($s) use ($existingGrades) {
$g = $existingGrades instanceof \Illuminate\Support\Collection
? $existingGrades->get($s->id)
: ($existingGrades[$s->id] ?? null);
return [
'student_id' => $s->id,
'name' => $s->full_name,
'adm' => $s->admission_number,
'marks' => ($g && $g->marks_obtained !== null) ? (string) (float) $g->marks_obtained : '',
'remarks' => $g->remarks ?? '',
];
})->values();

// CHANGED: accept both legacy object ranges (GradingScaleRange model instances)
// and new normalized array ranges from AssessmentGradingService::previewRangesForExam().
// CHANGED: old object-only mapping preserved for reference.
// $rangesPayload = collect($gradingRanges ?? [])->map(fn ($r) => [
//     'grade' => $r->grade,
//     'min' => (float) $r->min_mark,
//     'max' => (float) $r->max_mark,
// ])->values();
$rangesPayload = collect($gradingRanges ?? [])->map(function ($r) {
    if (is_array($r)) {
        return [
            'grade' => (string) ($r['grade'] ?? '-'),
            'min' => (float) ($r['min'] ?? $r['min_mark'] ?? 0),
            'max' => (float) ($r['max'] ?? $r['max_mark'] ?? 100),
        ];
    }

    return [
        'grade' => (string) ($r->grade ?? '-'),
        'min' => (float) ($r->min ?? $r->min_mark ?? 0),
        'max' => (float) ($r->max ?? $r->max_mark ?? 100),
    ];
})->values();
@endphp

<form method="POST" action="{{ $action }}" x-ref="form"
    x-data="gradeEntry({
        rows: {{ Illuminate\Support\Js::from($rows) }},
        ranges: {{ Illuminate\Support\Js::from($rangesPayload) }},
        fullMarks: {{ (float) $fullMarks }},
        passMarks: {{ (float) $passMarks }},
        subjectName: {{ Illuminate\Support\Js::from($subject->name ?? '') }}
    })"
    @submit.prevent="save()"
    @if($exam) data-exam-id="{{ $exam->id }}" @endif>
    @csrf
    <input type="hidden" name="class_id" value="{{ $classId }}">
    <input type="hidden" name="subject_id" value="{{ $subject->id ?? '' }}">

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        {{-- Sticky progress / context bar --}}
        <div class="sticky top-0 z-20 px-4 sm:px-6 py-3 border-b bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-gray-600">
                    <span class="font-semibold text-gray-900">{{ $subject->name ?? '' }}</span>
                    @if($className)<span class="text-gray-400">&middot;</span> {{ $className }}@endif
                    <span class="text-gray-400">&middot;</span>
                    Pass mark <span class="font-medium text-gray-700">{{ (int) $passMarks }}</span> / {{ (int) $fullMarks }}
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium text-gray-500" x-text="`${gradedCount} / ${rows.length} entered`"></span>
                    <div class="w-32 h-2 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full rounded-full transition-all" :style="`width: ${progress}%; background: var(--primary-color);`"></div>
                    </div>
                    <button type="button" @click="clearAll()" class="text-xs font-medium text-gray-500 hover:text-red-600">Clear</button>
                </div>
            </div>
            <p class="mt-1 text-xs text-gray-400">
                Tip: press <kbd class="px-1 py-0.5 bg-gray-100 border rounded">Enter</kbd> or
                <kbd class="px-1 py-0.5 bg-gray-100 border rounded">&darr;</kbd> to jump to the next student.
            </p>
        </div>

        {{-- Scrollable table --}}
        <div class="max-h-[60vh] overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Marks (0&ndash;{{ (int) $fullMarks }})</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="(row, i) in rows" :key="row.student_id">
                        <tr :class="isInvalid(row.marks) ? 'bg-red-50' : 'hover:bg-gray-50'">
                            <td class="px-4 sm:px-6 py-3 text-sm text-gray-500" x-text="i + 1"></td>
                            <td class="px-4 sm:px-6 py-3">
                                <input type="hidden" :name="`grades[${i}][student_id]`" :value="row.student_id">
                                <p class="text-sm font-medium text-gray-900" x-text="row.name"></p>
                                <p class="text-xs text-gray-500" x-text="row.adm"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-center">
                                <input type="number" min="0" :max="fullMarks" step="0.5"
                                    class="js-mark w-24 text-center rounded-lg shadow-sm text-sm"
                                    :class="isInvalid(row.marks) ? 'border-red-400 text-red-700 focus:border-red-500 focus:ring-red-500' : 'border-gray-300'"
                                    :name="`grades[${i}][marks_obtained]`"
                                    x-model="row.marks"
                                    @input="dirty = true"
                                    @focus="$event.target.select()"
                                    @keydown.enter.prevent="moveFocus($event, 1)"
                                    @keydown.arrow-down.prevent="moveFocus($event, 1)"
                                    @keydown.arrow-up.prevent="moveFocus($event, -1)"
                                    placeholder="&ndash;">
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="gradeChipClass(row.marks)"
                                    x-text="gradeFor(row.marks)"></span>
                            </td>
                            <td class="px-4 sm:px-6 py-3">
                                <input type="text" maxlength="500"
                                    class="w-full rounded-lg border-gray-300 shadow-sm text-sm"
                                    :name="`grades[${i}][remarks]`"
                                    x-model="row.remarks"
                                    @input="dirty = true"
                                    placeholder="Optional">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Sticky save bar --}}
        <div class="sticky bottom-0 z-20 px-4 sm:px-6 py-3 border-t bg-gray-50 flex items-center justify-between gap-3">
            <p class="text-sm" :class="invalidCount > 0 ? 'text-red-600 font-medium' : 'text-gray-500'"
                x-text="invalidCount > 0 ? `${invalidCount} mark(s) out of range` : `${gradedCount} of ${rows.length} students ready to save`"></p>
            <div class="flex items-center gap-3">
                @if($exam && !$exam->is_published)
                <button type="button" @click="publishExam()"
                    class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm hover:opacity-90"
                    style="background: #16a34a;">
                    Publish Exam
                </button>
                @endif
                <button type="submit"
                    class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm disabled:opacity-50"
                    :disabled="invalidCount > 0"
                    style="background: var(--primary-color);">
                    Save Grades
                </button>
            </div>
        </div>
    </div>
</form>

@once
@push('scripts')
<script>
    function gradeEntry(config) {
        return {
            rows: config.rows,
            ranges: config.ranges,
            fullMarks: config.fullMarks,
            passMarks: config.passMarks,
            subjectName: config.subjectName,
            dirty: false,

            init() {
                this._guard = (e) => {
                    if (this.dirty) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                };
                window.addEventListener('beforeunload', this._guard);
            },

            hasValue(m) {
                return m !== '' && m !== null && m !== undefined && !isNaN(parseFloat(m));
            },
            get gradedCount() {
                return this.rows.filter(r => this.hasValue(r.marks)).length;
            },
            get invalidCount() {
                return this.rows.filter(r => this.isInvalid(r.marks)).length;
            },
            get progress() {
                return this.rows.length ? Math.round((this.gradedCount / this.rows.length) * 100) : 0;
            },
            isInvalid(m) {
                if (!this.hasValue(m)) return false;
                const v = parseFloat(m);
                return v < 0 || v > this.fullMarks;
            },
            gradeFor(m) {
                if (!this.hasValue(m)) return '\u2014';
                if (this.isInvalid(m)) return '!';
                const v = parseFloat(m);
                const r = this.ranges.find(x => v >= x.min && v <= x.max);
                return r ? r.grade : '?';
            },
            gradeChipClass(m) {
                if (!this.hasValue(m)) return 'bg-gray-100 text-gray-300';
                if (this.isInvalid(m)) return 'bg-red-100 text-red-700';
                return parseFloat(m) >= this.passMarks ?
                    'bg-green-100 text-green-700' :
                    'bg-amber-100 text-amber-700';
            },
            moveFocus(e, dir) {
                const inputs = Array.from(this.$root.querySelectorAll('input.js-mark'));
                const idx = inputs.indexOf(e.target);
                const next = inputs[idx + dir];
                if (next) {
                    next.focus();
                    next.select();
                }
            },
            clearAll() {
                Swal.fire({
                    title: 'Clear all marks?',
                    text: 'This only clears the form. Nothing is deleted until you save.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Clear',
                }).then(res => {
                    if (res.isConfirmed) {
                        this.rows.forEach(r => {
                            r.marks = '';
                            r.remarks = '';
                        });
                        this.dirty = true;
                    }
                });
            },
            save() {
                if (this.invalidCount > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fix invalid marks',
                        text: `${this.invalidCount} mark(s) are outside 0\u2013${this.fullMarks}.`,
                    });
                    return;
                }
                const primary = getComputedStyle(document.documentElement)
                    .getPropertyValue('--primary-color').trim() || '#1e40af';
                Swal.fire({
                    title: 'Save grades?',
                    html: `Saving <b>${this.gradedCount}</b> of <b>${this.rows.length}</b> students for <b>${this.subjectName}</b>.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save',
                    confirmButtonColor: primary,
                }).then(res => {
                    if (res.isConfirmed) {
                        this.dirty = false;
                        window.removeEventListener('beforeunload', this._guard);
                        this.$root.submit();
                    }
                });
            },
            publishExam() {
                const primary = getComputedStyle(document.documentElement)
                    .getPropertyValue('--primary-color').trim() || '#1e40af';
                Swal.fire({
                    title: 'Publish this exam?',
                    html: 'Guardians will receive an email with the exam results. <b>This cannot be undone easily.</b>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, publish',
                    confirmButtonColor: '#16a34a',
                }).then(res => {
                    if (res.isConfirmed) {
                        const examId = document.querySelector('[data-exam-id]')?.dataset.examId;
                        if (!examId) {
                            Swal.fire('Error', 'Exam ID not found', 'error');
                            return;
                        }
                        fetch(`/exams/${examId}/publish`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                    'Accept': 'application/json',
                                }
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Published!', 'Exam published and guardians notified.', 'success')
                                        .then(() => window.location.reload());
                                } else {
                                    Swal.fire('Error', data.message || 'Failed to publish exam', 'error');
                                }
                            })
                            .catch(err => {
                                Swal.fire('Error', 'Network error: ' + err.message, 'error');
                            });
                    }
                });
            },
        };
    }
</script>
@endpush
@endonce