<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Report Card: {{ $student->full_name }}</h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('report-cards.pdf', ['student' => $student->id, 'exam' => $exam->id]) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-green-600 hover:bg-green-700">Download PDF</a>
                <a href="{{ route('report-cards.index', ['class_id' => request('class_id'), 'exam_id' => $exam->id]) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border p-8 max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-6 border-b pb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ setting('school_name', 'MySchool') }}</h1>
            <p class="text-sm text-gray-600">{{ setting('school_motto', '') }}</p>
            <p class="text-lg font-semibold text-gray-800 mt-2">{{ $exam->name }}</p>
        </div>

        {{-- Student Info --}}
        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div><span class="text-gray-500">Name:</span> <strong>{{ $student->full_name }}</strong></div>
            <div><span class="text-gray-500">Admission No:</span> <strong>{{ $student->admission_number }}</strong></div>
            <div><span class="text-gray-500">Class:</span> <strong>{{ $enrollment->schoolClass->name ?? '-' }}</strong></div>
            <div><span class="text-gray-500">Section:</span> <strong>{{ $enrollment->section->name ?? '-' }}</strong></div>
            <div><span class="text-gray-500">Boarding Status:</span> <strong>{{ ucfirst($student->boarding_status ?? 'day') }}</strong></div>
            @if($enrollment?->subjectCombination)
            <div><span class="text-gray-500">Combination:</span> <strong>{{ $enrollment->subjectCombination->code }}</strong></div>
            @endif
        </div>

        {{-- Grades Table --}}
        <table class="min-w-full divide-y divide-gray-200 mb-6">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Marks</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Remark</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @php $total = 0; $count = 0; @endphp
                @foreach($grades as $i => $grade)
                @php $total += $grade->marks_obtained ?? 0; $count++; @endphp
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $grade->subject->name ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm text-center text-gray-900 font-medium">{{ $grade->marks_obtained ?? '-' }} / 100</td>
                    <td class="px-4 py-2 text-sm text-center font-bold" style="color: var(--primary-color);">{{ $grade->grade_letter ?? '-' }}@if($grade->achievement_level) <span class="text-xs text-gray-500">({{ $grade->achievement_level }})</span>@endif</td>
                    <td class="px-4 py-2 text-sm text-gray-600">{{ $grade->remarks ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="2" class="px-4 py-2 text-sm font-bold text-gray-900">Total / Average</td>
                    <td class="px-4 py-2 text-sm text-center font-bold text-gray-900">{{ $total }} / {{ $count * 100 }}</td>
                    <td class="px-4 py-2 text-sm text-center font-bold" style="color: var(--primary-color);">{{ $count > 0 ? round($total / $count, 1) : 0 }}%</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- Results summary: total, average, class position and Uganda national result --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Total</div>
                <div class="text-lg font-bold text-gray-900">{{ rtrim(rtrim(number_format($totalMarks, 1), '0'), '.') }}</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Average</div>
                <div class="text-lg font-bold" style="color: var(--primary-color);">{{ $average }}%</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Position</div>
                <div class="text-lg font-bold text-gray-900">{{ $position ? $position . ' / ' . $classSize : '-' }}</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">{{ $nationalExam ?? 'Conduct' }}</div>
                <div class="text-lg font-bold text-gray-900">{{ $nationalExam ? ($result ?? '-') : ($reportCard->conduct ?? '-') }}</div>
            </div>
        </div>

        @if($nationalExam)
        <div class="mb-6 rounded-lg bg-blue-50 border border-blue-100 p-4 text-sm text-blue-900">
            <strong>{{ $nationalExam }} projection:</strong>
            {{ $result ?? 'N/A' }}@if($aggregate !== null) (aggregate {{ $aggregate }})@endif.
            <span class="text-blue-700">Indicative only — based on entered marks.</span>
        </div>
        @endif

        {{-- Stored remarks --}}
        <div class="grid grid-cols-1 gap-3 mb-8 text-sm">
            <div><span class="text-gray-500">Class Teacher's Comment:</span> <span class="text-gray-900">{{ $reportCard->class_teacher_comment ?? '—' }}</span></div>
            <div><span class="text-gray-500">Head Teacher's Comment:</span> <span class="text-gray-900">{{ $reportCard->head_teacher_comment ?? '—' }}</span></div>
            <div><span class="text-gray-500">Conduct:</span> <span class="text-gray-900">{{ $reportCard->conduct ?? '—' }}</span>
                @if($reportCard->next_term_begins)
                <span class="ml-4 text-gray-500">Next term begins:</span> <span class="text-gray-900">{{ $reportCard->next_term_begins->format('d M Y') }}</span>
                @endif
            </div>
        </div>

        {{-- CHANGED: Uganda fit - replaced static signature block with editable remarks form --}}
        {{-- Remarks editor --}}
        <form method="POST" action="{{ route('report-cards.update', ['student' => $student->id, 'exam' => $exam->id]) }}" class="border-t pt-6 mb-8 print:hidden">
            @csrf
            @method('PUT')
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Edit Remarks</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="block text-gray-600 mb-1">Conduct</label>
                    <input type="text" name="conduct" value="{{ old('conduct', $reportCard->conduct) }}" class="w-full rounded-lg border-gray-300" placeholder="e.g. Excellent">
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">Next Term Begins</label>
                    <input type="date" name="next_term_begins" value="{{ old('next_term_begins', optional($reportCard->next_term_begins)->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-600 mb-1">Class Teacher's Comment</label>
                    <textarea name="class_teacher_comment" rows="2" class="w-full rounded-lg border-gray-300">{{ old('class_teacher_comment', $reportCard->class_teacher_comment) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-600 mb-1">Head Teacher's Comment</label>
                    <textarea name="head_teacher_comment" rows="2" class="w-full rounded-lg border-gray-300">{{ old('head_teacher_comment', $reportCard->head_teacher_comment) }}</textarea>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Save Remarks</button>
            </div>
        </form>

        {{-- Signature Area --}}
        <div class="grid grid-cols-3 gap-8 mt-12 pt-6 border-t text-sm text-center">
            <div>
                <div class="border-t border-gray-400 mt-8 pt-2">Class Teacher</div>
            </div>
            <div>
                <div class="border-t border-gray-400 mt-8 pt-2">Head Teacher</div>
            </div>
            <div>
                <div class="border-t border-gray-400 mt-8 pt-2">Parent/Guardian</div>
            </div>
        </div>
    </div>
</x-app-layout>