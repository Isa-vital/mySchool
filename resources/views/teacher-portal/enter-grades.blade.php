<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.grades') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Enter Grades &mdash; {{ $exam->name }}</h2>
        </div>
    </x-slot>

    {{-- Class/Subject Selector --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('teacher.enter-grades', $exam) }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($subjects->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select name="subject_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ $selectedSubjectId == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </form>
    </div>

    {{-- Grades Form --}}
    @if($students->isNotEmpty() && $subject)
    {{-- CHANGED (A6): subjects with weighted components use the component-columns table --}}
    @if(($subjectComponents ?? collect())->isNotEmpty())
    @include('grades.partials.component-entry', [
    'action' => route('teacher.save-grades', $exam),
    'exam' => $exam,
    'subject' => $subject,
    'students' => $students,
    'subjectComponents' => $subjectComponents,
    'existingComponentMarks' => $existingComponentMarks,
    'selectedClassId' => $selectedClassId,
    ])
    @else
    @if($isOLevel ?? false)
    {{-- O-Level (UCE): shared activity/CA entry grid — same partial as the admin
         Grades screen; the final mark is computed (CA + EOT), never typed. --}}
    @include('grades.partials.olevel-entry', ['action' => route('teacher.save-grades', $exam)])
    @else
    {{-- CHANGED: replaced the static marks table with the shared <x-grade-entry-form> component
         (live grade preview, keyboard nav, progress, unsaved-changes guard, save confirmation). --}}
    <x-grade-entry-form
        :action="route('teacher.save-grades', $exam)"
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
    @endif {{-- closes the o-level vs standard entry branch --}}

    {{-- CHANGED: original static table preserved for reference
    <form method="POST" action="{{ route('teacher.save-grades', $exam) }}">
    @csrf
    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
    <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-3 border-b bg-gray-50">
            <p class="text-sm font-medium text-gray-700">{{ $subject->name }} &mdash; {{ $classes->firstWhere('id', $selectedClassId)->name ?? '' }}</p>
        </div>

        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adm No.</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Marks (0-100)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($students as $i => $student)
                @php $existing = $existingGrades->get($student->id); @endphp
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-3">
                        <input type="hidden" name="grades[{{ $i }}][student_id]" value="{{ $student->id }}">
                        <p class="text-sm font-medium text-gray-800">{{ $student->full_name }}</p>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $student->admission_number }}</td>
                    <td class="px-6 py-3">
                        <input type="number" name="grades[{{ $i }}][marks_obtained]" value="{{ $existing->marks_obtained ?? '' }}" min="0" max="100" step="0.1" class="w-24 mx-auto block text-sm text-center border-gray-300 rounded" placeholder="-">
                    </td>
                    <td class="px-6 py-3">
                        <input type="text" name="grades[{{ $i }}][remarks]" value="{{ $existing->remarks ?? '' }}" class="w-full text-sm border-gray-300 rounded" placeholder="Optional">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
            <button type="submit" class="px-6 py-2 text-white text-sm font-medium rounded-lg" style="background-color: var(--primary-color);">
                Save Grades
            </button>
        </div>
    </div>
    </form>
    --}}
    @endif {{-- CHANGED (A6): closes the component-vs-single entry branch --}}
    @elseif($selectedClassId && $selectedSubjectId)
    <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
        <p class="text-gray-400 text-sm">No students enrolled in this class</p>
    </div>
    @endif
</x-app-layout>