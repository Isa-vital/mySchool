{{-- O-Level (UCE) activity/CA entry: per-activity scores (out of the configured
     activity max), teacher-entered identifier (1/2/3 — never derived), the EOT
     score and project work (own grade, never merged into the final mark). The
     final mark is COMPUTED (CA + EOT) on save, never typed. Shared by the admin
     Grades screen and the teacher portal via the $action parameter. --}}
@php
$action = $action ?? route('grades.save', $exam);
$projectMax = $projectMax ?? \App\Services\AssessmentGradingService::projectMaxScore();
@endphp
<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="olevel_entry" value="1">
    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <p class="text-sm text-gray-600">
                Exam: <strong>{{ $exam->name }}</strong> &middot; Subject: <strong>{{ $subject->name }}</strong>
                &middot; Activities scored out of <strong>{{ $activityMax }}</strong>
                &middot; CA <strong>/{{ (int) $caWeights['ca'] }}</strong> + EOT <strong>/{{ (int) $caWeights['eot'] }}</strong>
            </p>
            <p class="text-xs text-gray-500 mt-1">
                Blank activity slots are excluded from the CA average (never counted as 0).
                An extra activity column is always available; save to reveal the next one.
                Identifier is the teacher's 1/2/3 rating after each Activity of Integration — it is never computed.
            </p>
        </div>
        <div class="overflow-x-auto max-h-[65vh] overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        @for($n = 1; $n <= $activityColumns; $n++)
                            <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">A{{ $n }}</th>
                            @endfor
                            <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ident</th>
                            <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">EOT (/{{ (int) $caWeights['eot'] }})</th>
                            <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">EOT Status</th>
                            <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">Project (/{{ (int) $projectMax }})</th>
                            <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">Current</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($students as $i => $student)
                    @php
                    $existing = $existingGrades[$student->id] ?? null;
                    $studentActivities = $activityScores[$student->id] ?? [];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-3 py-2">
                            <p class="text-sm font-medium text-gray-900">{{ $student->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $student->admission_number }}</p>
                            <input type="hidden" name="grades[{{ $i }}][student_id]" value="{{ $student->id }}">
                        </td>
                        @for($n = 1; $n <= $activityColumns; $n++)
                            <td class="px-2 py-2 text-center">
                            <input type="number" name="grades[{{ $i }}][activities][{{ $n }}]"
                                value="{{ $studentActivities[$n] ?? '' }}"
                                min="0" max="{{ $activityMax }}" step="0.01"
                                class="w-16 text-center rounded border-gray-300 text-sm" placeholder="&mdash;">
                            </td>
                            @endfor
                            <td class="px-2 py-2 text-center">
                                <select name="grades[{{ $i }}][identifier]" class="w-16 rounded border-gray-300 text-sm">
                                    <option value="" @selected(($existing?->identifier) === null)>&mdash;</option>
                                    @foreach([1, 2, 3] as $identValue)
                                    <option value="{{ $identValue }}" @selected((int) ($existing?->identifier ?? 0) === $identValue)>{{ $identValue }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-2 py-2 text-center">
                                <input type="number" name="grades[{{ $i }}][eot_raw_score]"
                                    value="{{ $existing?->eot_raw_score !== null ? (float) $existing->eot_raw_score : '' }}"
                                    min="0" max="{{ (float) ($existing?->eot_max_score ?? $caWeights['eot']) }}" step="0.01"
                                    class="w-20 text-center rounded border-gray-300 text-sm" placeholder="&mdash;">
                            </td>
                            <td class="px-2 py-2 text-center">
                                <select name="grades[{{ $i }}][eot_status]" class="rounded border-gray-300 text-sm">
                                    <option value="scored" @selected(($existing?->eot_status ?? 'scored') === 'scored')>Scored</option>
                                    <option value="absent" @selected(($existing?->eot_status) === 'absent')>Absent</option>
                                    <option value="withheld" @selected(($existing?->eot_status) === 'withheld')>Withheld</option>
                                </select>
                            </td>
                            <td class="px-2 py-2 text-center">
                                <input type="number" name="grades[{{ $i }}][project_score_raw]"
                                    value="{{ $existing?->project_score_raw !== null ? (float) $existing->project_score_raw : '' }}"
                                    min="0" max="{{ (float) ($existing?->project_score_max ?? $projectMax) }}" step="0.01"
                                    class="w-16 text-center rounded border-gray-300 text-sm" placeholder="&mdash;">
                            </td>
                            <td class="px-2 py-2 text-center text-sm font-semibold {{ $existing?->grade_letter ? 'text-gray-900' : 'text-amber-600' }}">
                                @if($existing?->grade_letter)
                                {{ $existing->grade_letter }} ({{ (float) $existing->marks_obtained }})
                                @elseif($existing?->achievement_level)
                                <span class="text-[10px] uppercase">{{ $existing->achievement_level }}</span>
                                @else
                                &mdash;
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="grades[{{ $i }}][remarks]" value="{{ $existing?->remarks }}"
                                    class="w-full rounded border-gray-300 text-sm" placeholder="Optional">
                            </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t bg-gray-50 text-right">
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Save O-Level Assessment</button>
        </div>
    </div>
</form>