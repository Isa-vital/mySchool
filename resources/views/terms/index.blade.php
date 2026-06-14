<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Terms &mdash; {{ $academicYear->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">Manage the terms for this academic year.</p>
            </div>
            <div class="flex items-center space-x-3">
                @can('academic_years.create')
                <a href="{{ route('academic-years.terms.create', $academicYear) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Term
                </a>
                @endcan
                <a href="{{ route('academic-years.show', $academicYear) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to Year</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($terms as $term)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $term->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $term->start_date?->format('d M Y') ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $term->end_date?->format('d M Y') ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if($term->is_current)
                        <span class="px-2 py-1 text-xs font-medium rounded-full text-white" style="background: var(--primary-color);">Current</span>
                        @else
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-3">
                            @can('academic_years.edit')
                            @unless($term->is_current)
                            <form method="POST" action="{{ route('academic-years.terms.set-current', [$academicYear, $term]) }}">
                                @csrf
                                <button type="submit" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">Set Current</button>
                            </form>
                            @endunless
                            <a href="{{ route('academic-years.terms.edit', [$academicYear, $term]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                            @endcan
                            @can('academic_years.delete')
                            <form method="POST" action="{{ route('academic-years.terms.destroy', [$academicYear, $term]) }}" onsubmit="return confirm('Delete this term?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">No terms added for this academic year yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>