<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.dashboard') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Grades</h2>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold text-gray-800">Select an Exam to Enter Grades</h3>
        </div>
        <div class="divide-y">
            @forelse($exams as $exam)
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $exam->name }}</p>
                    <p class="text-xs text-gray-500">{{ $exam->term->name ?? '' }} &mdash; {{ $exam->academicYear->name ?? '' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-1 rounded-full {{ $exam->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $exam->is_published ? 'Published' : 'Draft' }}
                    </span>
                    <a href="{{ route('teacher.enter-grades', $exam) }}" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">Enter Grades &rarr;</a>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-400 text-sm">No exams created yet</div>
            @endforelse
        </div>
    </div>
</x-app-layout>