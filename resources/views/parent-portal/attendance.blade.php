<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('parent.child', $student) }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Attendance &mdash; {{ $student->full_name }}</h2>
        </div>
    </x-slot>

    {{-- Month Selector --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('parent.attendance', $student) }}" class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Month:</label>
            <input type="month" name="month" value="{{ $month }}" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Days Recorded</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $summary['present'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Present</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $summary['absent'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Absent</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $summary['late'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Late</p>
        </div>
    </div>

    {{-- Attendance Table --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($attendances as $att)
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-800">{{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($att->date)->format('l') }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $att->status === 'present' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $att->status === 'absent' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $att->status === 'late' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $att->status === 'excused' ? 'bg-blue-100 text-blue-700' : '' }}
                            ">{{ ucfirst($att->status) }}</span>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $att->remarks ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm">No attendance records for this month</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>