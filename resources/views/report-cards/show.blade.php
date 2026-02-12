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
                        <td class="px-4 py-2 text-sm text-center font-bold" style="color: var(--primary-color);">{{ $grade->grade_letter ?? '-' }}</td>
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
