<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Grades / Marks Entry</h2>
    </x-slot>

    {{-- PRIMARY: Report Card Grid Entry (BOT + MOT + EOT together) --}}
    @if($reportExams->count())
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-4">
        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
            📋 Report Card Entry (all component exams in one grid)
        </div>
        <form method="GET" action="{{ route('grades.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="w-56">
                <label class="block text-sm font-medium text-gray-700 mb-1">Report Card Exam</label>
                <select name="report_exam_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Report Exam</option>
                    @foreach($reportExams as $rexam)
                    <option value="{{ $rexam->id }}" {{ request('report_exam_id') == $rexam->id ? 'selected' : '' }}>
                        {{ $rexam->name }} ({{ $rexam->academicYear->name ?? '' }} {{ $rexam->term->name ?? '' }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select name="subject_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-5 py-2 text-sm font-semibold text-white rounded-lg shadow"
                style="background: var(--primary-color);">
                Open Grid →
            </button>
        </form>
        <p class="mt-2 text-xs text-gray-400">
            Enter marks for all component exams (e.g. BOT, MOT, EOT) at once. Weighted total and grade are calculated automatically.
        </p>
    </div>
    @endif

    {{-- SECONDARY: Single Exam Entry (existing flow) --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
            📝 Single Exam Entry
        </div>
        <form method="GET" action="{{ route('grades.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="w-56">
                <label class="block text-sm font-medium text-gray-700 mb-1">Exam</label>
                <select name="exam_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                        {{ $exam->name }} ({{ $exam->academicYear->name ?? '' }} {{ $exam->term->name ?? '' }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select name="subject_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Load Students</button>
        </form>
        <p class="mt-2 text-xs text-gray-400">Enter marks for a single exam at a time.</p>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700">
        <strong>How it works:</strong>
        Create individual exams (e.g. BOT, MOT, EOT), then create a <strong>Report Card</strong> exam that links them as components with weights (like coursework 40% + finals 60%). Use <em>Report Card Entry</em> above to enter all marks in one grid.
    </div>
</x-app-layout>
