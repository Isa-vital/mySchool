<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $notice->title }}</h2>
            <div class="flex items-center space-x-3">
                @can('notices.edit')
                <a href="{{ route('notices.edit', $notice) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Edit</a>
                @endcan
                <a href="{{ route('notices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center space-x-4 mb-4">
                @if($notice->is_published)
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Published</span>
                @else
                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Draft</span>
                @endif
                <span class="text-sm text-gray-500 capitalize">Audience: {{ str_replace('_', ' ', $notice->target_audience) }}</span>
                <span class="text-sm text-gray-400">{{ $notice->created_at->format('d M Y') }}</span>
            </div>

            <div class="prose max-w-none text-gray-700">
                {!! nl2br(e($notice->content)) !!}
            </div>

            <div class="mt-6 pt-4 border-t text-xs text-gray-400">
                Created by {{ $notice->author->name ?? 'System' }}
                @if($notice->expiry_date) &bull; Expires: {{ \Carbon\Carbon::parse($notice->expiry_date)->format('d M Y') }} @endif
            </div>
        </div>
    </div>
</x-app-layout>
