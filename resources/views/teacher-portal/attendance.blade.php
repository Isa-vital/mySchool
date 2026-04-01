<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.dashboard') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mark Attendance</h2>
        </div>
    </x-slot>

    {{-- Class/Date Selector --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('teacher.attendance') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($selectedClassId)
            @php $selectedClass = $classes->firstWhere('id', $selectedClassId); @endphp
            @if($selectedClass && $selectedClass->sections->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                <select name="section_id" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                    <option value="">All Sections</option>
                    @foreach($selectedClass->sections as $section)
                    <option value="{{ $section->id }}" {{ $selectedSectionId == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="date" value="{{ $date }}" max="{{ today()->format('Y-m-d') }}" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
            </div>
        </form>
    </div>

    {{-- Attendance Form --}}
    @if($students->isNotEmpty())
    <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adm No.</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($students as $i => $student)
                    @php $existing = $attendances->get($student->id); @endphp
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-6 py-3">
                            <input type="hidden" name="attendance[{{ $i }}][student_id]" value="{{ $student->id }}">
                            <p class="text-sm font-medium text-gray-800">{{ $student->full_name }}</p>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $student->admission_number }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                @foreach(['present', 'absent', 'late', 'excused'] as $status)
                                <label class="flex items-center gap-1 text-xs cursor-pointer">
                                    <input type="radio" name="attendance[{{ $i }}][status]" value="{{ $status }}"
                                        {{ ($existing && $existing->status === $status) || (!$existing && $status === 'present') ? 'checked' : '' }}
                                        class="text-sm">
                                    <span class="
                                                    {{ $status === 'present' ? 'text-green-600' : '' }}
                                                    {{ $status === 'absent' ? 'text-red-600' : '' }}
                                                    {{ $status === 'late' ? 'text-yellow-600' : '' }}
                                                    {{ $status === 'excused' ? 'text-blue-600' : '' }}
                                                ">{{ ucfirst($status) }}</span>
                                </label>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <input type="text" name="attendance[{{ $i }}][remarks]" value="{{ $existing->remarks ?? '' }}" class="w-full text-sm border-gray-300 rounded" placeholder="Optional">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
                <button type="submit" class="px-6 py-2 text-white text-sm font-medium rounded-lg" style="background-color: var(--primary-color);">
                    Save Attendance
                </button>
            </div>
        </div>
    </form>
    @elseif($selectedClassId)
    <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
        <p class="text-gray-400 text-sm">No students enrolled in this class</p>
    </div>
    @endif
</x-app-layout>