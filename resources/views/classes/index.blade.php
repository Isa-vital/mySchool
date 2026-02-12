<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Classes</h2>
            @can('classes.create')
            <a href="{{ route('classes.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Class
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($classes as $class)
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $class->name }}</h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $class->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $class->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="text-sm text-gray-600 mb-3">
                    <p>Level: {{ $class->level ?? '-' }}</p>
                    <p>Capacity: {{ $class->capacity ?? '-' }}</p>
                    <p>Sections: {{ $class->sections->count() }} ({{ $class->sections->pluck('name')->join(', ') ?: 'None' }})</p>
                    <p>Subjects: {{ $class->subjects->count() }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('classes.show', $class) }}" class="text-sm text-blue-600 hover:text-blue-800">Manage</a>
                    <a href="{{ route('classes.edit', $class) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                    <form action="{{ route('classes.destroy', $class) }}" method="POST" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500 bg-white rounded-xl shadow-sm border">No classes created yet.</div>
        @endforelse
    </div>
</x-app-layout>
