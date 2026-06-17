<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Enter Grades: {{ $exam->name }} {{ $subject ? '- ' . $subject->name : '' }}</h2>
            <a href="{{ route('grades.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    {{-- Class & Subject Selector --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('grades.enter', $exam) }}" class="flex flex-wrap gap-4 items-end">
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
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

    @if($subject && $students->count())
    {{-- CHANGED: replaced the static marks table with the reusable <x-grade-entry-form> component
         (live grade preview, keyboard nav, progress bar, unsaved-changes guard, save confirmation). --}}
    <x-grade-entry-form
        :action="route('grades.save', $exam)"
        :students="$students"
        :existing-grades="$existingGrades"
        :subject="$subject"
        :class-id="$selectedClassId"
        :class-name="optional($classes->firstWhere('id', (int) $selectedClassId))->name"
        :exam-name="$exam->name"
        :grading-ranges="$gradingRanges"
        :full-marks="$fullMarks"
        :pass-marks="$passMarks"
        :exam="$exam" />

    {{-- CHANGED: original static table preserved for reference
    <form method="POST" action="{{ route('grades.save', $exam) }}">
    @csrf
    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <p class="text-sm text-gray-600">Exam: <strong>{{ $exam->name }}</strong> | Subject: <strong>{{ $subject->name }}</strong> | Students: <strong>{{ $students->count() }}</strong></p>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marks (0-100)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Grade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($students as $i => $student)
                @php $existing = $existingGrades[$student->id] ?? null; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $student->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $student->admission_number }}</p>
                        <input type="hidden" name="grades[{{ $i }}][student_id]" value="{{ $student->id }}">
                    </td>
                    <td class="px-6 py-3">
                        <input type="number" name="grades[{{ $i }}][marks_obtained]" value="{{ $existing?->marks_obtained }}" min="0" max="100" step="0.5"
                            class="w-24 rounded-lg border-gray-300 shadow-sm text-sm" placeholder="0">
                    </td>
                    <td class="px-6 py-3 text-sm font-semibold {{ $existing ? 'text-gray-900' : 'text-gray-300' }}">
                        {{ $existing?->grade_letter ?? '—' }}
                    </td>
                    <td class="px-6 py-3">
                        <input type="text" name="grades[{{ $i }}][remarks]" value="{{ $existing?->remarks }}" class="w-full rounded border-gray-300 text-sm" placeholder="Optional">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t bg-gray-50 text-right">
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Save Grades</button>
        </div>
    </div>
    </form>
    --}}
    @elseif($selectedClassId && $selectedSubjectId)
    <div class="bg-white rounded-xl shadow-sm border p-12 text-center text-gray-500">
        No students enrolled in the selected class for this exam's academic year.
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border p-12 text-center text-gray-500">
        Select a class and subject above to enter grades.
    </div>
    @endif
</x-app-layout>