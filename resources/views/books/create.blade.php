<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Book</h2>
            <a href="{{ route('books.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <form method="POST" action="{{ route('books.store') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-lg border-gray-300 text-sm" required>
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                        <input type="text" name="author" value="{{ old('author') }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ISBN</label>
                        <input type="text" name="isbn" value="{{ old('isbn') }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Publisher</label>
                        <input type="text" name="publisher" value="{{ old('publisher') }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Publish Year</label>
                        <input type="number" name="publish_year" value="{{ old('publish_year') }}" min="1900" max="{{ date('Y') + 1 }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="book_category_id" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('book_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Copies <span class="text-red-500">*</span></label>
                        <input type="number" name="total_copies" value="{{ old('total_copies', 1) }}" min="1" class="w-full rounded-lg border-gray-300 text-sm" required>
                        @error('total_copies') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shelf Location</label>
                        <input type="text" name="shelf_location" value="{{ old('shelf_location') }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 text-sm">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Add Book</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
