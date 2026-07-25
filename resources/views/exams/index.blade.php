<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Exams</h2>
            @can('exams.create')
            <div class="flex items-center gap-2">
                {{-- CHANGED (UX): one-click term setup wizard --}}
                <a href="{{ route('exams.term-setup') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm bg-indigo-600 hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Term Setup
                </a>
                <a href="{{ route('exams.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Exam
                </a>
            </div>
            @endcan
        </div>
    </x-slot>

    {{-- CHANGED (UX): filter bar — defaults to the current academic year; filter by term/type --}}
    <form method="GET" action="{{ route('exams.index') }}" class="bg-white rounded-xl shadow-sm border p-4 mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Academic Year</label>
            <select name="academic_year_id" onchange="this.form.term_id.value=''; this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                <option value="all" {{ ($selectedYearId ?? null) === 'all' ? 'selected' : '' }}>All years</option>
                @foreach($academicYears as $year)
                <option value="{{ $year->id }}" {{ (string) ($selectedYearId ?? '') === (string) $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Term</label>
            <select name="term_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                <option value="">All terms</option>
                @foreach($academicYears as $year)
                @if(($selectedYearId ?? null) === 'all' || (string) ($selectedYearId ?? '') === (string) $year->id || $selectedTermId)
                @foreach($year->terms as $term)
                <option value="{{ $term->id }}" {{ (string) ($selectedTermId ?? '') === (string) $term->id ? 'selected' : '' }}>{{ $term->name }} ({{ $year->name }})</option>
                @endforeach
                @endif
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
            <select name="type" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                <option value="">All types</option>
                <option value="component" {{ request('type') === 'component' ? 'selected' : '' }}>Exam sets (BOT/MID/END…)</option>
                <option value="report" {{ request('type') === 'report' ? 'selected' : '' }}>Report card exams</option>
            </select>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($exams as $exam)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <a href="{{ route('exams.show', $exam) }}" class="hover:underline" style="color: var(--primary-color);">{{ $exam->name }}</a>
                            {{-- CHANGED (UX): type chip distinguishes report-card exams from exam sets --}}
                            @if($exam->is_report_card)
                            <span class="ml-1 px-2 py-0.5 text-[10px] font-medium rounded-full bg-purple-100 text-purple-700 align-middle">Report Card</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $exam->term->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $exam->academicYear->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($exam->start_date)
                            {{ $exam->start_date->format('d M') }}{{ $exam->end_date ? ' - ' . $exam->end_date->format('d M Y') : '' }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{-- CHANGED (A2 follow-up): show the real workflow status — the old
                                 Published/Draft binary displayed locked exams as "Draft". --}}
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ match($exam->status ?? 'draft') {
                                    'published' => 'bg-green-100 text-green-700',
                                    'locked' => 'bg-orange-100 text-orange-700',
                                    'marks_entry_open' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-yellow-100 text-yellow-700',
                                } }}">{{ ucwords(str_replace('_', ' ', $exam->status ?? 'draft')) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            {{-- CHANGED (UX): direct "Marks" shortcut into the right entry flow --}}
                            @can('grades.create')
                            <a href="{{ $exam->is_report_card ? route('grades.enter-grid', ['report_exam' => $exam->id]) : route('grades.enter', ['exam' => $exam->id]) }}" class="text-emerald-600 hover:text-emerald-800 mr-3">Marks</a>
                            @endcan
                            <a href="{{ route('exams.show', $exam) }}" class="text-blue-600 hover:text-blue-800 mr-3">View</a>
                            @can('exams.edit')
                            <a href="{{ route('exams.edit', $exam) }}" class="text-indigo-600 hover:text-indigo-800 mr-3">Edit</a>
                            @endcan
                            @can('exams.delete')
                            <form action="{{ route('exams.destroy', $exam) }}" method="POST" class="inline" onsubmit="return confirm('Delete this exam?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">No exams found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exams->hasPages())
        <div class="px-6 py-4 border-t">{{ $exams->links() }}</div>
        @endif
    </div>
</x-app-layout>