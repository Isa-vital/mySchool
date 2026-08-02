<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">A-Level Paper Marks: {{ $exam->name }} {{ $subject ? '- ' . $subject->name : '' }}</h2>
            <a href="{{ route('grades.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4 text-sm text-blue-800">
        Grading ruleset: <strong>{{ $cycle->name }}</strong>. Each paper is banded D1&ndash;F9 on its own;
        the subject grade comes from the official combination table &mdash; never from an average.
        Every paper must be resolved: a score, <strong>Absent</strong>, or <strong>Withheld</strong>.
    </div>

    @if(($noCombinationCount ?? 0) > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4 text-sm text-amber-800">
        <strong>{{ $noCombinationCount }} A-Level student(s) have no subject combination assigned</strong> — they are listed for every subject.
        Assign combinations from each student's edit page so only their real subjects appear here.
    </div>
    @endif

    {{-- Class & Subject Selector --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('uace-papers.enter', $exam) }}" class="flex flex-wrap gap-4 items-end">
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Class (S.5 / S.6)</label>
                <select name="class_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ ($class?->id ?? null) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-64">
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select name="subject_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ ($subject?->id ?? null) == $subj->id ? 'selected' : '' }}>
                        {{ $subj->name }}{{ $subj->is_subsidiary ? ' (Subsidiary)' : ($subj->paper_count ? ' (' . $subj->paper_count . ' papers)' : ' — papers not set') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Load</button>
        </form>
    </div>

    @if($class && $subject && $enrollments->count())
    <form method="POST" action="{{ route('uace-papers.save', $exam) }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $class->id }}">
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        @foreach($papers as $paper)
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ $paper['label'] }} (%)</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($enrollments as $i => $enrollment)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">
                            {{ $enrollment->student?->full_name }}
                            @if(!$enrollment->subject_combination_id)
                            <span class="ml-1 text-xs text-amber-600">(no combination)</span>
                            @endif
                        </td>
                        @foreach($papers as $paper)
                        @php
                        $n = $paper['paper_number'];
                        $row = ($existing[$enrollment->id] ?? collect())->get($n);
                        $oldPct = old("rows.{$enrollment->id}.{$n}.pct", $row?->raw_percentage !== null && $row?->status === 'scored' ? round((float) $row->raw_percentage, 1) : '');
                        $oldStatus = old("rows.{$enrollment->id}.{$n}.status", $row->status ?? 'scored');
                        @endphp
                        <td class="px-3 py-2 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <input type="number" step="0.1" min="0" max="100"
                                    name="rows[{{ $enrollment->id }}][{{ $n }}][pct]"
                                    value="{{ $oldPct }}"
                                    class="w-20 rounded-lg border-gray-300 shadow-sm text-sm text-center">
                                <select name="rows[{{ $enrollment->id }}][{{ $n }}][status]"
                                    class="rounded-lg border-gray-300 shadow-sm text-xs">
                                    <option value="scored" {{ $oldStatus === 'scored' ? 'selected' : '' }}>Scored</option>
                                    <option value="absent" {{ $oldStatus === 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="withheld" {{ $oldStatus === 'withheld' ? 'selected' : '' }}>Withheld</option>
                                </select>
                            </div>
                            @if($row && $row->status === 'scored' && $row->paper_grade)
                            <div class="text-xs text-gray-400 mt-0.5">saved: {{ $row->paper_grade }}</div>
                            @elseif($row && $row->status !== 'scored')
                            <div class="text-xs text-amber-500 mt-0.5">saved: {{ strtoupper($row->status) }}</div>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex items-center justify-between">
            <p class="text-xs text-gray-500">Students left completely blank (and never saved before) are skipped. Once any paper is filled, ALL papers for that student must be resolved.</p>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Save Paper Marks</button>
        </div>
    </form>
    @elseif($class && $subject)
    <div class="bg-white rounded-xl shadow-sm border p-8 text-center text-sm text-gray-500">No active students take this subject in {{ $class->name }}.</div>
    @endif
</x-app-layout>