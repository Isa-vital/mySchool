<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Roles & Permissions</h2>
            @can('roles.create')
            <a href="{{ route('roles.create') }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">+ Add Role</a>
            @endcan
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $role->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $role->permissions_count }} permissions &bull; {{ $role->users_count }} users</p>
                    </div>
                    @if($role->name !== 'Super Admin')
                        <div class="flex space-x-2">
                            @can('roles.edit')
                            <a href="{{ route('roles.edit', $role) }}" class="text-sm font-medium" style="color: var(--primary-color);">Edit</a>
                            @endcan
                            @can('roles.delete')
                            <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                            </form>
                            @endcan
                        </div>
                    @else
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Protected</span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-1 mt-3">
                    @foreach($role->permissions->take(8) as $perm)
                        <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $perm->name }}</span>
                    @endforeach
                    @if($role->permissions->count() > 8)
                        <span class="px-2 py-0.5 text-xs bg-gray-200 text-gray-500 rounded">+{{ $role->permissions->count() - 8 }} more</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
