<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Academic Years</h2>
            @can('academic_years.create')
            <a href="{{ route('academic-years.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Year
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($academicYears as $year)
        <div class="bg-white rounded-xl shadow-sm border p-6 {{ $year->is_current ? 'ring-2' : '' }}" style="{{ $year->is_current ? '--tw-ring-color: var(--primary-color)' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900">{{ $year->name }}</h3>
                @if($year->is_current)
                <span class="px-2 py-1 text-xs font-medium rounded-full text-white" style="background: var(--primary-color);">Current</span>
                @endif
            </div>
            <div class="text-sm text-gray-600 space-y-1 mb-4">
                <p>Start: {{ $year->start_date?->format('d M Y') }}</p>
                <p>End: {{ $year->end_date?->format('d M Y') }}</p>
            </div>
            <div class="flex items-center justify-between">
                @if(!$year->is_current)
                <form method="POST" action="{{ route('academic-years.set-current', $year) }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">Set as Current</button>
                </form>
                @else
                <span></span>
                @endif
                <div class="flex items-center space-x-2">
                    <a href="{{ route('academic-years.terms.index', $year) }}" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">Terms</a>
                    <a href="{{ route('academic-years.edit', $year) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                    <form action="{{ route('academic-years.destroy', $year) }}" method="POST" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500 bg-white rounded-xl shadow-sm border">No academic years created yet.</div>
        @endforelse
    </div>
</x-app-layout>