<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Role: {{ $role->name }}</h2>
            <a href="{{ route('roles.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <form method="POST" action="{{ route('roles.update', $role) }}">
                @csrf @method('PUT')

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" class="w-full md:w-1/2 rounded-lg border-gray-300 text-sm" {{ $role->name === 'Super Admin' ? 'readonly' : '' }} required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Permissions</label>

                    <div x-data="{ selectAll: false }" class="mb-4">
                        <label class="flex items-center space-x-2 text-sm font-medium text-indigo-600 cursor-pointer">
                            <input type="checkbox" x-model="selectAll" @change="document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = selectAll)" class="rounded border-gray-300 text-indigo-600">
                            <span>Select All</span>
                        </label>
                    </div>

                    <div class="space-y-4">
                        @foreach($permissions as $group => $perms)
                            <div class="border rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-800 mb-2 capitalize">{{ $group }}</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($perms as $perm)
                                        <label class="flex items-center space-x-2 text-sm">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="perm-checkbox rounded border-gray-300 text-indigo-600" {{ in_array($perm->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}>
                                            <span>{{ $perm->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
