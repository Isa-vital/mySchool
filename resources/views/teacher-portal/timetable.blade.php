<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.dashboard') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Timetable</h2>
        </div>
    </x-slot>

    @php
    $dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
    @endphp

    @forelse($slots as $dayNum => $daySlots)
    <div class="bg-white rounded-xl shadow-sm border mb-4">
        <div class="px-6 py-3 border-b bg-gray-50">
            <h3 class="font-semibold text-gray-800">{{ $dayNames[$dayNum] ?? "Day $dayNum" }}</h3>
        </div>
        <div class="divide-y">
            @foreach($daySlots->sortBy('start_time') as $slot)
            <div class="px-6 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $slot->subject->name ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">{{ $slot->schoolClass->name ?? '' }} {{ $slot->section->name ?? '' }}</p>
                </div>
                <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
        <p class="text-gray-400 text-sm">No timetable entries found</p>
    </div>
    @endforelse
</x-app-layout>