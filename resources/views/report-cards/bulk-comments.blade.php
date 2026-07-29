<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Report Card Comments — {{ $schoolClass->name }} ({{ $exam->name }})</h2>
            <a href="{{ route('report-cards.index', ['class_id' => $schoolClass->id, 'term_id' => $exam->term_id]) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    {{-- CHANGED (UX): bulk comments editor — fill conduct & comments for the whole class on one
         page instead of opening each student's report card individually. --}}
    <form method="POST" action="{{ route('report-cards.bulk-comments.save', ['exam' => $exam->id]) }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $schoolClass->id }}">

        <div class="bg-white rounded-xl shadow-sm border p-4 mb-4 flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Next Term Begins (applies to all)</label>
                <input type="date" name="next_term_begins" value="{{ old('next_term_begins', $nextTermBegins) }}" class="rounded-lg border-gray-300 text-sm">
            </div>
            <p class="text-xs text-gray-500 mb-2">Set once — it is saved on every student's report card below. Leave blank to keep existing values.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-8">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40">Conduct</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class Teacher's Comment</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Head Teacher's Comment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($students as $i => $student)
                        @php $card = $reportCards->get($student->id); @endphp
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}
                                <p class="text-xs text-gray-400 font-normal">{{ $student->lin ?: $student->admission_number }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="rows[{{ $student->id }}][conduct]" value="{{ old('rows.' . $student->id . '.conduct', $card?->conduct) }}" maxlength="100" placeholder="e.g. Good" class="w-full rounded-lg border-gray-300 text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <textarea name="rows[{{ $student->id }}][class_teacher_comment]" rows="2" maxlength="1000" placeholder="Class teacher's comment…" class="w-full rounded-lg border-gray-300 text-sm">{{ old('rows.' . $student->id . '.class_teacher_comment', $card?->class_teacher_comment) }}</textarea>
                            </td>
                            <td class="px-4 py-3">
                                <textarea name="rows[{{ $student->id }}][head_teacher_comment]" rows="2" maxlength="1000" placeholder="Head teacher's comment…" class="w-full rounded-lg border-gray-300 text-sm">{{ old('rows.' . $student->id . '.head_teacher_comment', $card?->head_teacher_comment) }}</textarea>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex items-center justify-between">
                <p class="text-xs text-gray-500">{{ $students->count() }} students — only rows you change are updated.</p>
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Save All Comments</button>
            </div>
        </div>
    </form>
</x-app-layout>