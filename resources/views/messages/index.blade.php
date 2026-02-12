<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Messages</h2>
            <a href="{{ route('messages.create') }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">+ New Message</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($received as $message)
                    <tr class="hover:bg-gray-50 {{ !$message->read_at ? 'bg-blue-50' : '' }}">
                        <td class="px-6 py-4 text-sm {{ !$message->read_at ? 'font-bold' : 'font-medium' }} text-gray-900">{{ $message->sender->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm {{ !$message->read_at ? 'font-semibold' : '' }} text-gray-700">{{ $message->subject ?: '(No Subject)' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $message->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('messages.show', $message) }}" class="text-sm font-medium" style="color: var(--primary-color);">Read</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No messages.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">{{ $received->links() }}</div>
    </div>
</x-app-layout>
