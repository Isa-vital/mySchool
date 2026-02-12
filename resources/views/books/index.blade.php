<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Library - Books</h2>
            @can('books.create')
            <a href="{{ route('books.create') }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">+ Add Book</a>
            @endcan
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, author, ISBN..." class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <select name="category_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Filter</button>
                <a href="{{ route('books.index') }}" class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Copies</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Available</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($books as $book)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $book->title }}</p>
                            @if($book->isbn) <p class="text-xs text-gray-400">ISBN: {{ $book->isbn }}</p> @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $book->author ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $book->category->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-center text-gray-600">{{ $book->total_copies }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 text-xs rounded-full {{ $book->available_copies > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $book->available_copies }}</span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('books.show', $book) }}" class="text-sm font-medium" style="color: var(--primary-color);">View</a>
                            @can('books.edit')
                            <a href="{{ route('books.edit', $book) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Edit</a>
                            @endcan
                            @can('books.delete')
                            <form method="POST" action="{{ route('books.destroy', $book) }}" class="inline" onsubmit="return confirm('Delete this book?')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No books found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t">{{ $books->links() }}</div>
    </div>
</x-app-layout>
