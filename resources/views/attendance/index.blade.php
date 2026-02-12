<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mark Attendance</h2>
    </x-slot>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Class <span class="text-red-500">*</span></label>
                <select name="class_id" required onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Load Students</button>
            <a href="{{ route('attendance.report') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100">View Report</a>
        </form>
    </div>

    {{-- Attendance Form --}}
    @if(isset($students) && $students->count())
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="class_id" value="{{ request('class_id') }}">
            <input type="hidden" name="date" value="{{ request('date', date('Y-m-d')) }}">

            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-700">{{ $students->count() }} students</p>
                        <div class="flex items-center space-x-4 text-sm">
                            <button type="button" onclick="markAll('present')" class="text-green-600 hover:underline">All Present</button>
                            <button type="button" onclick="markAll('absent')" class="text-red-600 hover:underline">All Absent</button>
                        </div>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Present</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Absent</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Late</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remark</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($students as $student)
                            @php $existing = $existingAttendance[$student->id] ?? null; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600">
                                            {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $student->full_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $student->admission_number }}</p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="attendance[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <input type="radio" name="attendance[{{ $student->id }}][status]" value="present" class="attendance-radio text-green-600" {{ ($existing?->status ?? 'present') === 'present' ? 'checked' : '' }}>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <input type="radio" name="attendance[{{ $student->id }}][status]" value="absent" class="attendance-radio text-red-600" {{ ($existing?->status ?? '') === 'absent' ? 'checked' : '' }}>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <input type="radio" name="attendance[{{ $student->id }}][status]" value="late" class="attendance-radio text-yellow-600" {{ ($existing?->status ?? '') === 'late' ? 'checked' : '' }}>
                                </td>
                                <td class="px-6 py-3">
                                    <input type="text" name="attendance[{{ $student->id }}][remarks]" value="{{ $existing?->remarks }}" placeholder="Optional" class="w-full text-sm rounded border-gray-300">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t bg-gray-50 text-right">
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Save Attendance</button>
                </div>
            </div>
        </form>
    @elseif(request('class_id'))
        <div class="bg-white rounded-xl shadow-sm border p-12 text-center text-gray-500">No students enrolled in this class.</div>
    @endif

    <script>
        function markAll(status) {
            document.querySelectorAll(`.attendance-radio[value="${status}"]`).forEach(r => r.checked = true);
        }
    </script>
</x-app-layout>
