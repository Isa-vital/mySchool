<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Parent Portal</h2>
    </x-slot>

    @if($data->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
        <svg class="w-12 h-12 text-yellow-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-gray-600">No students linked to your account. Please contact the school administration.</p>
    </div>
    @else
    <div class="space-y-6">
        @foreach($data as $item)
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-lg">{{ strtoupper(substr($item['student']->first_name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $item['student']->full_name }}</h3>
                        <p class="text-sm text-gray-500">
                            {{ $item['student']->admission_number }}
                            @if($item['enrollment'])
                            &mdash; {{ $item['enrollment']->schoolClass->name ?? '' }}
                            {{ $item['enrollment']->section->name ?? '' }}
                            @endif
                        </p>
                    </div>
                </div>
                <a href="{{ route('parent.child', $item['student']) }}" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">View Details &rarr;</a>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Today's Attendance --}}
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                    <div class="w-10 h-10 rounded-lg {{ $item['today_attendance'] && $item['today_attendance']->status === 'present' ? 'bg-green-100' : ($item['today_attendance'] ? 'bg-red-100' : 'bg-gray-100') }} flex items-center justify-center">
                        @if($item['today_attendance'])
                        @if($item['today_attendance']->status === 'present')
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        @endif
                        @else
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Today</p>
                        <p class="text-sm font-medium text-gray-700">
                            {{ $item['today_attendance'] ? ucfirst($item['today_attendance']->status) : 'Not marked' }}
                        </p>
                    </div>
                </div>

                {{-- Fees Balance --}}
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                    <div class="w-10 h-10 rounded-lg {{ $item['total_balance'] > 0 ? 'bg-orange-100' : 'bg-green-100' }} flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $item['total_balance'] > 0 ? 'text-orange-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Fees Balance</p>
                        <p class="text-sm font-medium text-gray-700">{{ setting('currency_symbol', 'UGX') }} {{ number_format($item['total_balance']) }}</p>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('parent.attendance', $item['student']) }}" class="flex-1 text-center px-3 py-2 text-xs font-medium rounded-lg border hover:bg-gray-50 transition">Attendance</a>
                    <a href="{{ route('parent.results', $item['student']) }}" class="flex-1 text-center px-3 py-2 text-xs font-medium rounded-lg border hover:bg-gray-50 transition">Results</a>
                    <a href="{{ route('parent.fees', $item['student']) }}" class="flex-1 text-center px-3 py-2 text-xs font-medium rounded-lg border hover:bg-gray-50 transition">Fees</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-app-layout>