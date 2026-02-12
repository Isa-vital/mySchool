<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $guardian->full_name ?? $guardian->first_name . ' ' . $guardian->last_name }}</h2>
            <div class="flex items-center space-x-3">
                @can('guardians.edit')
                <a href="{{ route('guardians.edit', $guardian) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Edit</a>
                @endcan
                <a href="{{ route('guardians.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Name</p>
                    <p class="text-sm font-medium text-gray-900">{{ $guardian->full_name ?? $guardian->first_name . ' ' . $guardian->last_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Relationship</p>
                    <p class="text-sm font-medium text-gray-900 capitalize">{{ $guardian->relationship ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Phone</p>
                    <p class="text-sm font-medium text-gray-900">{{ $guardian->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Email</p>
                    <p class="text-sm font-medium text-gray-900">{{ $guardian->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Occupation</p>
                    <p class="text-sm font-medium text-gray-900">{{ $guardian->occupation ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Address</p>
                    <p class="text-sm font-medium text-gray-900">{{ $guardian->address ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Linked Students --}}
        @if($guardian->students && $guardian->students->count())
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-sm font-semibold text-gray-800">Linked Students ({{ $guardian->students->count() }})</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Fee Balance</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($guardian->students as $student)
                    @php
                        $enrollment = $student->enrollments->first();
                        $balance = $student->invoices->sum('balance');
                    @endphp
                    <tr>
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $student->admission_number ?? '-' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">
                            @if($enrollment)
                                {{ $enrollment->schoolClass->name ?? '' }}{{ $enrollment->section ? ' - ' . $enrollment->section->name : '' }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm">
                            @php $sc = ['active' => 'green', 'graduated' => 'blue', 'transferred' => 'yellow', 'withdrawn' => 'red']; $c = $sc[$student->status] ?? 'gray'; @endphp
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $c }}-100 text-{{ $c }}-700 capitalize">{{ $student->status }}</span>
                        </td>
                        <td class="px-6 py-3 text-sm text-right {{ $balance > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                            UGX {{ number_format($balance) }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('students.show', $student) }}" class="text-sm font-medium" style="color: var(--primary-color);">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border p-6 text-center text-gray-500">
            No students linked to this guardian.
        </div>
        @endif
    </div>
</x-app-layout>
