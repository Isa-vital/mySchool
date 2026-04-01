<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Teacher Portal</h2>
    </x-slot>

    {{-- Welcome --}}
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Welcome, {{ $staff->first_name }} {{ $staff->last_name }}</h3>
        <p class="text-sm text-gray-500 mt-1">{{ $staff->designation ?? 'Teacher' }} &mdash; {{ $staff->staff_id }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Today's Schedule --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Today's Schedule</h3>
                <a href="{{ route('teacher.timetable') }}" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">Full Timetable</a>
            </div>
            <div class="divide-y">
                @forelse($todaySlots as $slot)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $slot->subject->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $slot->schoolClass->name ?? '' }} {{ $slot->section->name ?? '' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-700">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-400 text-sm">No classes scheduled today</div>
                @endforelse
            </div>
        </div>

        {{-- My Classes --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h3 class="font-semibold text-gray-800">My Classes</h3>
            </div>
            <div class="divide-y">
                @forelse($myClasses as $class)
                <div class="px-6 py-3 flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-800">{{ $class->name }}</p>
                    <span class="text-xs text-gray-500">Level {{ $class->level }}</span>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-400 text-sm">No classes assigned</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <a href="{{ route('teacher.attendance') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <h4 class="font-semibold text-gray-800 group-hover:underline">Mark Attendance</h4>
            <p class="text-sm text-gray-500 mt-1">Record student attendance for your classes</p>
        </a>

        <a href="{{ route('teacher.grades') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <h4 class="font-semibold text-gray-800 group-hover:underline">Enter Grades</h4>
            <p class="text-sm text-gray-500 mt-1">Enter marks for exams and assessments</p>
        </a>

        <a href="{{ route('teacher.timetable') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h4 class="font-semibold text-gray-800 group-hover:underline">My Timetable</h4>
            <p class="text-sm text-gray-500 mt-1">View your weekly class schedule</p>
        </a>
    </div>
</x-app-layout>