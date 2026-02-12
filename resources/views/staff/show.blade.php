<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $staff->first_name }} {{ $staff->last_name }}</h2>
            <div class="flex items-center space-x-3">
                @can('staff.edit')
                <a href="{{ route('staff.edit', $staff) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Edit</a>
                @endcan
                <a href="{{ route('staff.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="text-center mb-6">
                <div class="w-24 h-24 rounded-full bg-gray-200 mx-auto flex items-center justify-center text-2xl font-bold text-gray-600 overflow-hidden">
                    @if($staff->photo)
                        <img src="{{ Storage::url($staff->photo) }}" class="w-full h-full object-cover" alt="">
                    @else
                        {{ strtoupper(substr($staff->first_name, 0, 1)) }}{{ strtoupper(substr($staff->last_name, 0, 1)) }}
                    @endif
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-3">{{ $staff->first_name }} {{ $staff->last_name }}</h3>
                <p class="text-sm text-gray-500">{{ $staff->staff_number }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ $staff->designation ?? '' }}</p>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Gender</span><span class="text-gray-900 capitalize">{{ $staff->gender ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Date of Birth</span><span class="text-gray-900">{{ $staff->date_of_birth?->format('d M Y') ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="text-gray-900">{{ $staff->phone ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="text-gray-900">{{ $staff->email ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Department</span><span class="text-gray-900">{{ $staff->department ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Qualification</span><span class="text-gray-900">{{ $staff->qualification ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Employment</span><span class="text-gray-900 capitalize">{{ $staff->employment_type ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Join Date</span><span class="text-gray-900">{{ $staff->join_date?->format('d M Y') ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="px-2 py-0.5 text-xs rounded-full {{ $staff->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $staff->is_active ? 'Active' : 'Inactive' }}</span></div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="font-semibold text-gray-800 mb-2">Address</h3>
                <p class="text-sm text-gray-600">{{ $staff->address ?? 'Not provided' }}</p>
            </div>

            @if($staff->user)
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="font-semibold text-gray-800 mb-3">User Account</h3>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-gray-500">Login Email</span><span class="text-gray-900">{{ $staff->user->email }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Roles</span><span class="text-gray-900">{{ $staff->user->getRoleNames()->join(', ') ?: 'None' }}</span></div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
