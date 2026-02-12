<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Message</h2>
            <a href="{{ route('messages.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4 pb-4 border-b">
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ $message->subject ?: '(No Subject)' }}</p>
                    <p class="text-sm text-gray-500 mt-1">From: <span class="font-medium text-gray-700">{{ $message->sender->name ?? '-' }}</span></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-400">{{ $message->created_at->format('d M Y H:i') }}</p>
                    @if($message->read_at)
                        <p class="text-xs text-green-500">Read {{ $message->read_at->diffForHumans() }}</p>
                    @endif
                </div>
            </div>

            <div class="prose max-w-none text-gray-700">
                {!! nl2br(e($message->body)) !!}
            </div>
        </div>
    </div>
</x-app-layout>
