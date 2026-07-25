{{-- CHANGED (A6): entry table for subjects assessed in multiple weighted components
     (e.g. Paper 1 theory + Paper 2 practical). One column per component; the combined
     subject grade is computed on read from raw scores + grading rules, never stored.
     Expects: $action, $exam, $subject, $students, $subjectComponents,
              $existingComponentMarks (keyed "student:component"), $selectedClassId --}}
<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                <strong>{{ $subject->name }}</strong> is assessed in {{ $subjectComponents->count() }} weighted components —
                @foreach($subjectComponents as $component)
                {{ $component->name }} (w={{ rtrim(rtrim(number_format((float) $component->weight, 2), '0'), '.') }}, /{{ rtrim(rtrim(number_format((float) $component->max_score, 2), '0'), '.') }}){{ $loop->last ? '' : ', ' }}
                @endforeach
            </p>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Save Scores</button>
        </div>
        <div class="overflow-x-auto" style="max-height: 60vh;">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-8">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        @foreach($subjectComponents as $component)
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            {{ $component->name }}
                            <span class="block font-normal normal-case text-gray-400">/{{ rtrim(rtrim(number_format((float) $component->max_score, 2), '0'), '.') }}</span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($students as $i => $student)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-2 text-sm font-medium text-gray-900 whitespace-nowrap">
                            {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}
                            <span class="block text-xs text-gray-400 font-normal">{{ $student->admission_number }}</span>
                        </td>
                        @foreach($subjectComponents as $component)
                        @php $existing = $existingComponentMarks->get($student->id . ':' . $component->id); @endphp
                        <td class="px-4 py-2 text-center">
                            <input type="number" step="0.5" min="0" max="{{ (float) $component->max_score }}"
                                name="component_marks[{{ $student->id }}][{{ $component->id }}]"
                                value="{{ $existing?->marks_obtained !== null && $existing ? (float) $existing->marks_obtained : '' }}"
                                class="w-24 rounded-lg border-gray-300 text-sm text-center">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t bg-gray-50 flex items-center justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Save Scores</button>
        </div>
    </div>
</form>