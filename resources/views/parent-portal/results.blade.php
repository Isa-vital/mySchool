<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('parent.child', $student) }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Exam Results &mdash; {{ $student->full_name }}</h2>
        </div>
    </x-slot>

    @forelse($results as $result)
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">{{ $result['exam']->name }}</h3>
                <p class="text-sm text-gray-500">{{ $result['exam']->term->name ?? '' }} &mdash; {{ $result['exam']->academicYear->name ?? '' }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Average</p>
                <p class="text-xl font-bold" style="color: var(--primary-color);">{{ number_format($result['average'], 1) }}%</p>
            </div>
        </div>

        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Marks</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($result['grades'] as $grade)
                <tr>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $grade->subject->name ?? '-' }}</td>
                    <td class="px-6 py-3 text-sm text-center text-gray-700">{{ $grade->marks_obtained ?? '-' }}</td>
                    <td class="px-6 py-3 text-center">
                        <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-700">{{ $grade->grade_letter ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $grade->remarks ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-gray-400 text-sm">No published exam results yet</p>
    </div>
    @endforelse
</x-app-layout>