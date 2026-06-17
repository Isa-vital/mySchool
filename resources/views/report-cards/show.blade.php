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

        @if(($componentExams ?? collect())->count() > 1)
        <div class="mb-6 rounded-xl border bg-gray-50 p-4">
            <h3 class="text-sm font-semibold text-gray-800 mb-2">Report Components</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                @foreach($componentExams as $componentExam)
                <div class="rounded-lg border bg-white px-3 py-2">
                    <div class="font-medium text-gray-900">{{ $componentExam->name }}</div>
                    <div class="text-xs text-gray-500">{{ $componentExam->term->name ?? '-' }} / {{ $componentExam->academicYear->name ?? '-' }}</div>
                    <div class="text-xs text-gray-600 mt-1">Weight: {{ rtrim(rtrim(number_format((float) ($componentExam->pivot->weight ?? 0), 2, '.', ''), '0'), '.') }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Grades Table --}}
        {{-- Grades Table - Format varies by assessment type --}}
        @if($formatted['format'] === 'primary')
        {{-- PRIMARY FORMAT: Marks + Achievement Levels with component breakdown --}}
        @php
            // Dynamically collect exam names from report components (exams ticked to appear on report)
            $hasComponents = collect($formatted['subjects'])->some(fn($s) => count($s['components'] ?? []) > 0);
            $componentNames = [];
            if ($hasComponents) {
                foreach ($formatted['subjects'] as $subject) {
                    foreach ($subject['components'] ?? [] as $comp) {
                        if (!in_array($comp['exam_name'], $componentNames)) {
                            $componentNames[] = $comp['exam_name'];
                        }
                    }
                }
            }
        @endphp
        @if($hasComponents)
        {{-- Show component breakdown for composite reports (dynamically from selected exams) --}}
        <table class="min-w-full divide-y divide-gray-200 mb-6">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    @foreach($componentNames as $compName)
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ $compName }}</th>
                    @endforeach
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Overall</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($formatted['subjects'] as $i => $subject)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $subject['subject'] }}</td>
                    @foreach($componentNames as $compName)
                    @php
                        $comp = collect($subject['components'] ?? [])->firstWhere('exam_name', $compName);
                    @endphp
                    <td class="px-4 py-2 text-sm text-center">
                        @if($comp)
                        <div class="text-gray-900 font-medium">{{ round($comp['percentage']) }}%</div>
                        <div class="text-xs text-gray-500">{{ $comp['marks'] }}/{{ $comp['full_marks'] }}</div>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="px-4 py-2 text-sm text-center">
                        <div class="text-gray-900 font-medium">{{ round($subject['marks']) }}%</div>
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $subject['color'] }}">
                            {{ $subject['grade'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="{{ 2 + count($componentNames) }}" class="px-4 py-2 text-sm font-bold text-gray-900">Overall Performance</td>
                    <td class="px-4 py-2 text-sm text-center">
                        <div class="text-gray-900 font-bold">{{ $formatted['average'] }}%</div>
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $formatted['overall_grade'] === 'Excellent' || $formatted['overall_grade'] === 'Very Good' ? 'bg-green-100 text-green-800' : ($formatted['overall_grade'] === 'Good' ? 'bg-blue-100 text-blue-800' : ($formatted['overall_grade'] === 'Satisfactory' ? 'bg-yellow-100 text-yellow-800' : ($formatted['overall_grade'] === 'Fair' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'))) }}">
                            {{ $formatted['overall_grade'] }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
        @else
        {{-- Simple format without components --}}
        <table class="min-w-full divide-y divide-gray-200 mb-6">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Marks</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Achievement Level</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($formatted['subjects'] as $i => $subject)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $subject['subject'] }}</td>
                    <td class="px-4 py-2 text-sm text-center text-gray-900 font-medium">{{ $subject['marks'] }}/100</td>
                    <td class="px-4 py-2 text-sm text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $subject['color'] }}">
                            {{ $subject['grade'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="2" class="px-4 py-2 text-sm font-bold text-gray-900">Overall Performance</td>
                    <td class="px-4 py-2 text-sm text-center font-bold text-gray-900">{{ $formatted['average'] }}%</td>
                    <td class="px-4 py-2 text-sm text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $formatted['overall_grade'] === 'Excellent' || $formatted['overall_grade'] === 'Very Good' ? 'bg-green-100 text-green-800' : ($formatted['overall_grade'] === 'Good' ? 'bg-blue-100 text-blue-800' : ($formatted['overall_grade'] === 'Satisfactory' ? 'bg-yellow-100 text-yellow-800' : ($formatted['overall_grade'] === 'Fair' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'))) }}">
                            {{ $formatted['overall_grade'] }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
        @endif

        @elseif($formatted['format'] === 'o-level')
        {{-- O-LEVEL FORMAT: Competency-based with descriptors --}}
        @php
            $hasOLevelComponents = collect($formatted['subjects'])->some(fn($s) => count($s['components'] ?? []) > 0);
            $oLevelCompNames = [];
            if ($hasOLevelComponents) {
                foreach ($formatted['subjects'] as $subject) {
                    foreach ($subject['components'] ?? [] as $comp) {
                        if (!in_array($comp['exam_name'], $oLevelCompNames)) {
                            $oLevelCompNames[] = $comp['exam_name'];
                        }
                    }
                }
            }
        @endphp
        @if($hasOLevelComponents)
        {{-- Show O-Level component breakdown (multi-term competencies) --}}
        <table class="min-w-full divide-y divide-gray-200 mb-6">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    @foreach($oLevelCompNames as $compName)
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ $compName }}</th>
                    @endforeach
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Overall</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-xs">
                @foreach($formatted['subjects'] as $i => $subject)
                <tr>
                    <td class="px-4 py-2 text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-2 text-gray-900 font-medium">{{ $subject['subject'] }}</td>
                    @foreach($oLevelCompNames as $compName)
                    @php
                        $comp = collect($subject['components'] ?? [])->firstWhere('exam_name', $compName);
                    @endphp
                    <td class="px-4 py-2 text-center">
                        @if($comp)
                        <div class="font-semibold">{{ $comp['grade'] }}</div>
                        <div class="text-gray-600">{{ $comp['points'] }} pts</div>
                        <div class="text-gray-500 italic">{{ $comp['descriptor'] }}</div>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="px-4 py-2 text-center">
                        <div class="font-bold {{ $subject['color'] }}">{{ $subject['grade'] }}</div>
                        <div class="text-gray-600">{{ $subject['points'] }} pts</div>
                        <div class="text-gray-600 italic">{{ $subject['descriptor'] }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="{{ 2 + count($oLevelCompNames) }}" class="px-4 py-2 text-sm font-bold text-gray-900">Overall Achievement</td>
                    <td class="px-4 py-2 text-sm text-center">
                        <div class="font-bold">{{ $formatted['overall_grade'] }}</div>
                        <div class="text-gray-700 font-medium">{{ $formatted['total_points'] }} pts</div>
                        <div class="text-gray-600 italic">{{ $formatted['overall_descriptor'] ?? $formatted['overall_grade'] }}</div>
                    </td>
                </tr>
            </tfoot>
        </table>
        @else
        {{-- Simple O-Level format without components --}}
        <table class="min-w-full divide-y divide-gray-200 mb-6">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Points</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Descriptor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($formatted['subjects'] as $i => $subject)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $subject['subject'] }}</td>
                    <td class="px-4 py-2 text-sm text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $subject['color'] }}">
                            {{ $subject['grade'] }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-sm text-center font-bold text-gray-900">{{ $subject['points'] }}</td>
                    <td class="px-4 py-2 text-sm text-center text-gray-700">{{ $subject['descriptor'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="2" class="px-4 py-2 text-sm font-bold text-gray-900">Overall Competency</td>
                    <td class="px-4 py-2 text-sm text-center font-bold">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                            {{ $formatted['overall_grade'] }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-sm text-center font-bold text-gray-900">{{ $formatted['total_points'] }} pts</td>
                    <td class="px-4 py-2 text-sm text-center text-gray-700">{{ $formatted['overall_descriptor'] ?? $formatted['overall_grade'] }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        @elseif($formatted['format'] === 'a-level')
        {{-- A-LEVEL FORMAT: Grade Points with School Total out of configured max --}}
        <table class="min-w-full divide-y divide-gray-200 mb-6">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Marks</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Points</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($formatted['subjects'] as $i => $subject)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $subject['subject'] }}</td>
                    <td class="px-4 py-2 text-sm text-center text-gray-900 font-medium">{{ $subject['marks'] }}</td>
                    <td class="px-4 py-2 text-sm text-center font-bold text-gray-900">{{ $subject['grade'] }}</td>
                    <td class="px-4 py-2 text-sm text-center">
                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                            {{ $subject['points'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-4 py-2 text-sm font-bold text-gray-900">Total / Average</td>
                    <td class="px-4 py-2 text-sm text-center font-bold text-gray-900">{{ $formatted['average_points'] }}</td>
                    <td class="px-4 py-2 text-sm text-center font-bold text-gray-900">{{ $formatted['total_points'] }} / {{ $formatted['max_points'] }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        {{-- Results summary: total, average, class position and Uganda national result --}}
        {{-- Results summary - Format-specific display --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            @if($formatted['format'] === 'primary')
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Average</div>
                <div class="text-lg font-bold" style="color: var(--primary-color);">{{ $formatted['average'] }}%</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Overall Grade</div>
                <div class="text-lg font-bold text-gray-900">{{ $formatted['overall_grade'] }}</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Position</div>
                <div class="text-lg font-bold text-gray-900">{{ $formatted['position'] ? $formatted['position'] . ' / ' . $formatted['class_size'] : '-' }}</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Subjects</div>
                <div class="text-lg font-bold text-gray-900">{{ count($formatted['subjects']) }}</div>
            </div>
            @elseif($formatted['format'] === 'o-level')
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Total Points</div>
                <div class="text-lg font-bold" style="color: var(--primary-color);">{{ $formatted['total_points'] }} pts</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Average Points</div>
                <div class="text-lg font-bold text-gray-900">{{ $formatted['average_points'] }}</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Overall Competency</div>
                <div class="text-lg font-bold text-gray-900">{{ $formatted['overall_grade'] }}</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Position</div>
                <div class="text-lg font-bold text-gray-900">{{ $formatted['position'] ? $formatted['position'] . ' / ' . $formatted['class_size'] : '-' }}</div>
            </div>
            @elseif($formatted['format'] === 'a-level')
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Total Points</div>
                <div class="text-lg font-bold text-gray-900">{{ $formatted['total_points'] }} / {{ $formatted['max_points'] }}</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Average Points</div>
                <div class="text-lg font-bold" style="color: var(--primary-color);">{{ $formatted['average_points'] }}</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Subjects</div>
                <div class="text-lg font-bold text-gray-900">{{ $formatted['subject_count'] }}</div>
            </div>
            <div class="rounded-lg border p-3 text-center">
                <div class="text-xs text-gray-500 uppercase">Position</div>
                <div class="text-lg font-bold text-gray-900">{{ $formatted['position'] ? $formatted['position'] . ' / ' . $formatted['class_size'] : '-' }}</div>
            </div>
            @endif
        </div>

        {{-- CHANGED: removed UNEB projection block. Report body is now school-focused only. --}}
        {{-- @if($nationalExam)
        <div class="mb-6 rounded-lg bg-blue-50 border border-blue-100 p-4 text-sm text-blue-900">
            <strong>{{ $nationalExam }} projection:</strong>
        {{ $result ?? 'N/A' }}@if($aggregate !== null) (aggregate {{ $aggregate }})@endif.
        <span class="text-blue-700">Indicative only — based on entered marks.</span>
    </div>
    @endif --}}

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
    @can('report_cards.edit')
    <form method="POST" action="{{ route('report-cards.update', ['student' => $student->id, 'exam' => $exam->id]) }}" class="border-t pt-6 mb-8 print:hidden">
        @csrf
        @method('PUT')
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Edit Remarks</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            @if($formatted['remarks']['conduct_required'])
            <div>
                <label class="block text-gray-600 mb-1">Conduct</label>
                <input type="text" name="conduct" value="{{ old('conduct', $reportCard->conduct) }}" class="w-full rounded-lg border-gray-300" placeholder="e.g. Excellent">
            </div>
            @endif
            <div>
                <label class="block text-gray-600 mb-1">Next Term Begins</label>
                <input type="date" name="next_term_begins" value="{{ old('next_term_begins', optional($reportCard->next_term_begins)->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300">
            </div>
            @if($formatted['remarks']['teacher_comment_required'])
            <div class="md:col-span-2">
                <label class="block text-gray-600 mb-1">Class Teacher's Comment</label>
                <textarea name="class_teacher_comment" rows="2" class="w-full rounded-lg border-gray-300">{{ old('class_teacher_comment', $reportCard->class_teacher_comment) }}</textarea>
            </div>
            @endif
            @if($formatted['remarks']['head_comment_required'])
            <div class="md:col-span-2">
                <label class="block text-gray-600 mb-1">Head Teacher's Comment</label>
                <textarea name="head_teacher_comment" rows="2" class="w-full rounded-lg border-gray-300">{{ old('head_teacher_comment', $reportCard->head_teacher_comment) }}</textarea>
            </div>
            @endif
        </div>
        <div class="mt-3">
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Save Remarks</button>
        </div>
    </form>
    @endcan

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