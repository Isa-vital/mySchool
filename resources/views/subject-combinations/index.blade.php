<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">A-Level Subject Combinations</h2>
            @can('subjects.create')
            <a href="{{ route('subject-combinations.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">+ New Combination</a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code or name…" class="w-64 rounded-lg border-gray-300 shadow-sm text-sm">
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Search</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Principal Subjects</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subsidiaries</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($combinations as $combination)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ $combination->code }}</td>
                    <td class="px-6 py-3 text-sm text-gray-700">{{ $combination->name }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600">
                        {{ $combination->subjects->where('pivot.is_principal', true)->pluck('name')->join(', ') ?: '—' }}
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600">
                        {{ $combination->subjects->where('pivot.is_principal', false)->pluck('name')->join(', ') ?: '—' }}
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600">{{ $combination->enrollments_count }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $combination->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $combination->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-right text-sm space-x-3">
                        @can('subjects.edit')
                        <a href="{{ route('subject-combinations.edit', $combination) }}" class="text-blue-600 hover:underline">Edit</a>
                        @endcan
                        @can('subjects.delete')
                        <form method="POST" action="{{ route('subject-combinations.destroy', $combination) }}" class="inline" onsubmit="return confirm('Delete this combination?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">No combinations yet. Create one to enroll S.5/S.6 students on it.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $combinations->links() }}</div>
</x-app-layout>