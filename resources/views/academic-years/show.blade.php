<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $academicYear->name }}</h2>
            <div class="flex items-center space-x-3">
                @can('academic_years.edit')
                <a href="{{ route('academic-years.edit', $academicYear) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Edit</a>
                @endcan
                <a href="{{ route('academic-years.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Name</p>
                    <p class="text-sm font-medium text-gray-900">{{ $academicYear->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Status</p>
                    @if($academicYear->is_current)
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Current</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Inactive</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Start Date</p>
                    <p class="text-sm font-medium text-gray-900">{{ $academicYear->start_date?->format('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">End Date</p>
                    <p class="text-sm font-medium text-gray-900">{{ $academicYear->end_date?->format('d M Y') ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
