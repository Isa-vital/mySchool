<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">UACE Grading Rulesets (Exam Cycles)</h2>
        </div>
    </x-slot>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4 text-sm text-blue-800">
        Paper band boundaries, combination rules and grade points are <strong>versioned per cycle</strong>.
        An active cycle is never edited in place — <strong>clone it</strong>, adjust the copy, then activate it.
        Exams stay pinned to the cycle they were graded under, so historical reports never change.
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Cycle list --}}
        <div class="lg:col-span-1 space-y-3">
            @foreach($cycles as $cycle)
            <div class="bg-white rounded-xl shadow-sm border p-4 {{ ($selected?->id ?? null) === $cycle->id ? 'ring-2' : '' }}" @if(($selected?->id ?? null) === $cycle->id) style="--tw-ring-color: var(--primary-color);" @endif>
                <a href="{{ route('uace-cycles.index', ['cycle_id' => $cycle->id]) }}" class="text-sm font-semibold text-gray-900 hover:underline">{{ $cycle->name }}</a>
                <div class="mt-1 text-xs text-gray-500">{{ $cycle->combinationRules->count() }} rules · {{ $cycle->bandBoundaries->count() }} bands</div>
                <div class="mt-2 flex items-center gap-2">
                    @if($cycle->is_active)
                    <span class="px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs font-semibold">Active</span>
                    @else
                    <form method="POST" action="{{ route('uace-cycles.activate', $cycle) }}">
                        @csrf
                        <button type="submit" class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-xs font-medium hover:bg-gray-200" onclick="return confirm('Activate this ruleset for all NEW exams?')">Activate</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach

            @if($selected)
            <form method="POST" action="{{ route('uace-cycles.clone', $selected) }}" class="bg-white rounded-xl shadow-sm border p-4">
                @csrf
                <label class="block text-xs font-medium text-gray-700 mb-1">Clone "{{ $selected->name }}" as:</label>
                <input type="text" name="name" required placeholder="e.g. UACE 2027 Revision" class="w-full rounded-lg border-gray-300 shadow-sm text-sm mb-2">
                <button type="submit" class="w-full px-3 py-1.5 text-xs font-medium text-white rounded-lg" style="background: var(--primary-color);">Clone into New Version</button>
            </form>
            @endif
        </div>

        {{-- Selected cycle detail --}}
        <div class="lg:col-span-3 space-y-6">
            @if($selected)
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Paper Band Boundaries — {{ $selected->name }}</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($selected->bandBoundaries as $band)
                    <div class="px-3 py-2 rounded-lg border bg-gray-50 text-center">
                        <div class="text-sm font-bold text-gray-900">{{ $band->band_code }}</div>
                        <div class="text-xs text-gray-500">{{ rtrim(rtrim(number_format((float) $band->min_pct, 1), '0'), '.') }}&ndash;{{ rtrim(rtrim(number_format((float) $band->max_pct, 1), '0'), '.') }}%</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Grade Points &amp; Subsidiary</h3>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach($selected->gradePoints as $point)
                    <div class="px-3 py-2 rounded-lg border bg-gray-50 text-center">
                        <div class="text-sm font-bold text-gray-900">{{ $point->grade_code }}</div>
                        <div class="text-xs text-gray-500">{{ $point->points }} pts</div>
                    </div>
                    @endforeach
                </div>
                @if($selected->subsidiaryConfig)
                <p class="text-xs text-gray-500">Subsidiary pass mark: <strong>{{ rtrim(rtrim(number_format((float) $selected->subsidiaryConfig->pass_threshold_pct, 1), '0'), '.') }}%</strong> — Pass = {{ $selected->subsidiaryConfig->pass_points }} pt, Fail = {{ $selected->subsidiaryConfig->fail_points }} pts.</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4 overflow-x-auto">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Combination Rules ({{ $selected->combinationRules->count() }})</h3>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Papers</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Matcher</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($selected->combinationRules->sortBy([['paper_count', 'asc'], ['rule_order', 'asc']]) as $rule)
                        <tr>
                            <td class="px-3 py-2 text-gray-700">{{ $rule->paper_count }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $rule->rule_order }}</td>
                            <td class="px-3 py-2 text-gray-500 text-xs">{{ $rule->subject_category_override ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $rule->description }}</td>
                            <td class="px-3 py-2 text-gray-500 text-xs font-mono">{{ json_encode($rule->matcher) }}</td>
                            <td class="px-3 py-2 text-center font-bold text-gray-900">{{ $rule->resulting_grade }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>