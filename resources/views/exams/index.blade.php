<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Exams</h2>
            @can('exams.create')
            <a href="{{ route('exams.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Exam
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($exams as $exam)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <a href="{{ route('exams.show', $exam) }}" class="hover:underline" style="color: var(--primary-color);">{{ $exam->name }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $exam->term->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $exam->academicYear->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($exam->start_date)
                                    {{ $exam->start_date->format('d M') }}{{ $exam->end_date ? ' - ' . $exam->end_date->format('d M Y') : '' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $exam->is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $exam->is_published ? 'Published' : 'Draft' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="{{ route('exams.show', $exam) }}" class="text-blue-600 hover:text-blue-800 mr-3">View</a>
                                @can('exams.edit')
                                <a href="{{ route('exams.edit', $exam) }}" class="text-indigo-600 hover:text-indigo-800 mr-3">Edit</a>
                                @endcan
                                @can('exams.delete')
                                <form action="{{ route('exams.destroy', $exam) }}" method="POST" class="inline" onsubmit="return confirm('Delete this exam?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No exams found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exams->hasPages())
            <div class="px-6 py-4 border-t">{{ $exams->links() }}</div>
        @endif
    </div>
</x-app-layout>
