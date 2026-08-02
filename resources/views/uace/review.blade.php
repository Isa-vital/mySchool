<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">UACE Manual Review Queue</h2>
            <a href="{{ route('grades.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to Grades</a>
        </div>
    </x-slot>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4 text-sm text-blue-800">
        Band patterns the ruleset does not cover are <strong>never guessed</strong> — they wait here.
        Confirming a grade also records the pattern as a rule, so the same bands auto-resolve for every other student.
        <strong>Incomplete</strong> subjects (absent/withheld papers) cannot be resolved here — enter their papers first.
    </div>

    {{-- Sitting selector --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('uace-review.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="w-96">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sitting (exam with paper results)</label>
                <select name="exam_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ ($selectedExam?->id ?? null) == $exam->id ? 'selected' : '' }}>
                        {{ $exam->name }} — {{ $exam->term?->name }} {{ $exam->academicYear?->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Load</button>
        </form>
        @if($cycle)
        <p class="text-xs text-gray-500 mt-2">Ruleset: <strong>{{ $cycle->name }}</strong></p>
        @endif
    </div>

    @if($selectedExam)
    @if($pending->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border p-8 text-center text-sm text-gray-500">
        No subject results pending manual confirmation for this sitting.
    </div>
    @else
    <div class="mb-3 text-sm font-medium text-amber-700">{{ $pending->count() }} subject result(s) pending manual grade confirmation.</div>
    <div class="space-y-4">
        @foreach($pending as $item)
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">{{ $item['student']?->full_name }} — {{ $item['subject'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        @foreach($item['papers'] as $paper)
                        <span class="inline-block mr-3">{{ $paper['label'] }}:
                            {{ $paper['percentage'] !== null ? round($paper['percentage']) . '%' : '' }}
                            <strong class="{{ in_array($paper['band'], ['ABSENT','WITHHELD','MISSING']) ? 'text-amber-600' : 'text-gray-800' }}">{{ $paper['band'] }}</strong>
                        </span>
                        @endforeach
                    </div>
                </div>
                @if($item['status'] === 'unmatched')
                <form method="POST" action="{{ route('uace-review.resolve') }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="exam_id" value="{{ $selectedExam->id }}">
                    <input type="hidden" name="subject_id" value="{{ $item['subject_id'] }}">
                    <input type="hidden" name="bands" value="{{ implode(',', $item['bands']) }}">
                    <span class="px-2 py-1 rounded bg-amber-100 text-amber-800 text-xs font-semibold">UNMATCHED [{{ implode(', ', $item['bands']) }}]</span>
                    <select name="grade" required class="rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Grade…</option>
                        @foreach($gradeCodes as $code)
                        <option value="{{ $code }}">{{ $code }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-1 text-xs text-gray-600">
                        <input type="checkbox" name="confirm" value="1" required class="rounded border-gray-300"> Confirm
                    </label>
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white rounded-lg" style="background: var(--primary-color);">Save as Rule</button>
                </form>
                @else
                <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-xs font-semibold">INCOMPLETE — enter the missing paper(s)</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endif
</x-app-layout>