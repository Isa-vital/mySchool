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

        {{-- CHANGED: Terms management section nested under the academic year --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Terms</h3>
                <a href="{{ route('academic-years.terms.index', $academicYear) }}" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">Manage Terms &rarr;</a>
            </div>
            @if($academicYear->terms->isEmpty())
            <p class="text-sm text-gray-500">No terms added yet. <a href="{{ route('academic-years.terms.create', $academicYear) }}" class="hover:underline" style="color: var(--primary-color);">Add the first term</a>.</p>
            @else
            <ul class="divide-y divide-gray-100">
                @foreach($academicYear->terms->sortBy('start_date') as $term)
                <li class="flex items-center justify-between py-2">
                    <div>
                        <span class="text-sm font-medium text-gray-900">{{ $term->name }}</span>
                        <span class="text-xs text-gray-500 ml-2">{{ $term->start_date?->format('d M Y') }} &ndash; {{ $term->end_date?->format('d M Y') }}</span>
                    </div>
                    @if($term->is_current)
                    <span class="px-2 py-1 text-xs font-medium rounded-full text-white" style="background: var(--primary-color);">Current</span>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</x-app-layout>