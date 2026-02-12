<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Guardians</h2>
            @can('guardians.create')
            <a href="{{ route('guardians.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Guardian
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, phone or email..." class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Search</button>
            <a href="{{ route('guardians.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Clear</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Relationship</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Occupation</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($guardians as $guardian)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <a href="{{ route('guardians.show', $guardian) }}" class="hover:underline" style="color: var(--primary-color);">{{ $guardian->first_name }} {{ $guardian->last_name }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ $guardian->relationship ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $guardian->phone ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($guardian->students_count > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">{{ $guardian->students_count }} student{{ $guardian->students_count > 1 ? 's' : '' }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $guardian->occupation ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('guardians.edit', $guardian) }}" class="text-indigo-600 hover:text-indigo-800 mr-3">Edit</a>
                                <form action="{{ route('guardians.destroy', $guardian) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No guardians found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($guardians->hasPages())
            <div class="px-6 py-4 border-t">{{ $guardians->links() }}</div>
        @endif
    </div>
</x-app-layout>
