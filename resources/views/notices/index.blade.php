<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notices</h2>
            @can('notices.create')
            <a href="{{ route('notices.create') }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">+ New Notice</a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Audience</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($notices as $notice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $notice->title }}</p>
                            <p class="text-xs text-gray-500">By {{ $notice->author->name ?? 'System' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $notice->target_audience) }}</td>
                        <td class="px-6 py-4">
                            @if($notice->is_published)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Published</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Draft</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $notice->publish_date ? \Carbon\Carbon::parse($notice->publish_date)->format('d M Y') : $notice->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('notices.show', $notice) }}" class="text-sm font-medium" style="color: var(--primary-color);">View</a>
                            @can('notices.edit')
                            <a href="{{ route('notices.edit', $notice) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Edit</a>
                            @endcan
                            @can('notices.delete')
                            <form method="POST" action="{{ route('notices.destroy', $notice) }}" class="inline" onsubmit="return confirm('Delete this notice?')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No notices found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">{{ $notices->links() }}</div>
    </div>
</x-app-layout>
