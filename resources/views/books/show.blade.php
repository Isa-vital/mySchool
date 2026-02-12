<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $book->title }}</h2>
            <div class="flex items-center space-x-3">
                @can('books.edit')
                <a href="{{ route('books.edit', $book) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Edit</a>
                @endcan
                <a href="{{ route('books.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        {{-- Book details --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Author</p>
                    <p class="text-sm font-medium text-gray-900">{{ $book->author ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">ISBN</p>
                    <p class="text-sm font-medium text-gray-900">{{ $book->isbn ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Category</p>
                    <p class="text-sm font-medium text-gray-900">{{ $book->category->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Publisher</p>
                    <p class="text-sm font-medium text-gray-900">{{ $book->publisher ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Publish Year</p>
                    <p class="text-sm font-medium text-gray-900">{{ $book->publish_year ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Shelf Location</p>
                    <p class="text-sm font-medium text-gray-900">{{ $book->shelf_location ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Total Copies</p>
                    <p class="text-sm font-medium text-gray-900">{{ $book->total_copies }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Available</p>
                    <p class="text-sm font-bold {{ $book->available_copies > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $book->available_copies }}</p>
                </div>
            </div>
            @if($book->description)
                <div class="mt-4 pt-4 border-t">
                    <p class="text-xs text-gray-500 uppercase mb-1">Description</p>
                    <p class="text-sm text-gray-700">{{ $book->description }}</p>
                </div>
            @endif
        </div>

        {{-- Issue History --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-sm font-semibold text-gray-800">Issue History</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Returned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($book->issues as $issue)
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $issue->borrower->full_name ?? $issue->borrower->name ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $issue->issue_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $issue->due_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $issue->return_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $colors = ['issued' => 'bg-blue-100 text-blue-700', 'returned' => 'bg-green-100 text-green-700', 'overdue' => 'bg-red-100 text-red-700'];
                                @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $colors[$issue->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($issue->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-6 text-center text-gray-400">No issue history.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
