<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Attendance Report</h2>
            <a href="{{ route('attendance.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to Attendance</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
            </div>
            <div class="w-40">
                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to', date('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Generate</button>
        </form>
    </div>

    @if(isset($stats))
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ $stats['present'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Present</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <p class="text-2xl font-bold text-red-600">{{ $stats['absent'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Absent</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['late'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Late</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <p class="text-2xl font-bold" style="color: var(--primary-color);">{{ $stats['rate'] ?? 0 }}%</p>
                <p class="text-sm text-gray-500">Attendance Rate</p>
            </div>
        </div>

        {{-- Detail Table --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remark</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($records as $record)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $record->date->format('d M Y') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $record->student->full_name ?? '-' }}</td>
                                <td class="px-6 py-3">
                                    @php
                                        $colors = ['present' => 'green', 'absent' => 'red', 'late' => 'yellow', 'excused' => 'blue'];
                                        $c = $colors[$record->status] ?? 'gray';
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $c }}-100 text-{{ $c }}-700 capitalize">{{ $record->status }}</span>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $record->remark ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($records->hasPages())
                <div class="px-6 py-4 border-t">{{ $records->links() }}</div>
            @endif
        </div>
    @endif
</x-app-layout>
