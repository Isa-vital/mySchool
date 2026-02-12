<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $schoolClass->name }}</h2>
            <a href="{{ route('classes.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Class Info --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Class Details</h3>
            <div class="text-sm space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Level</span><span class="text-gray-900">{{ $schoolClass->level ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Capacity</span><span class="text-gray-900">{{ $schoolClass->capacity ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="px-2 py-0.5 text-xs rounded-full {{ $schoolClass->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $schoolClass->is_active ? 'Active' : 'Inactive' }}</span></div>
                <div><span class="text-gray-500">Description:</span><p class="text-gray-900 mt-1">{{ $schoolClass->description ?? '-' }}</p></div>
            </div>
        </div>

        {{-- Sections --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Sections</h3>
            </div>
            @if($schoolClass->sections->count())
                <div class="flex flex-wrap gap-2">
                    @foreach($schoolClass->sections as $section)
                        <span class="px-3 py-1.5 text-sm bg-gray-100 rounded-lg text-gray-700">{{ $section->name }}</span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No sections created.</p>
            @endif
        </div>

        {{-- Assigned Subjects --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Assigned Subjects</h3>
            @if($schoolClass->subjects->count())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($schoolClass->subjects as $subject)
                        <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg">
                            <span class="w-2 h-2 rounded-full" style="background: var(--primary-color);"></span>
                            <span class="text-sm text-gray-900">{{ $subject->name }}</span>
                            <span class="text-xs text-gray-500">({{ $subject->code }})</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No subjects assigned.</p>
            @endif
        </div>
    </div>
</x-app-layout>
