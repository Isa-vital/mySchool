<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Report Cards</h2>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
                    {{-- CHANGED: replaced Exam selector with Term selector as requested. --}}
                    {{--
                    <label class="block text-sm font-medium text-gray-700 mb-1">Exam</label>
                    <select name="exam_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select Exam</option>
                        @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                            {{ $exam->name }}
                            @if($exam->is_report_card)
                            (Report)
                            @elseif(!$exam->is_published)
                            (Draft)
                            @endif
                        </option>
                        @endforeach
                    </select>
                    --}}
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                    <select name="term_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select Term</option>
                        @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                            {{ $term->name }}
                            @if($term->academicYear)
                            ({{ $term->academicYear->name }})
                        @endif
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Load</button>
        </form>
    </div>

    @if(isset($students) && $students->count())
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($students as $i => $student)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $student->full_name }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600">{{ $student->admission_number }}</td>
                    <td class="px-6 py-3 text-right text-sm">
                        @if(isset($selectedExam) && $selectedExam)
                        <a href="{{ route('report-cards.show', ['student' => $student->id, 'exam' => $selectedExam->id, 'class_id' => request('class_id'), 'term_id' => request('term_id')]) }}" class="text-blue-600 hover:text-blue-800 mr-3">View</a>
                        <a href="{{ route('report-cards.pdf', ['student' => $student->id, 'exam' => $selectedExam->id, 'class_id' => request('class_id'), 'term_id' => request('term_id')]) }}" class="text-green-600 hover:text-green-800">PDF</a>
                        @else
                        <span class="text-gray-400">No report exam in term</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border p-12 text-center text-gray-500">Select a class and term to view report cards.</div>
    @endif
</x-app-layout>