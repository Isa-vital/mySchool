<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Message</h2>
            <a href="{{ route('messages.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <form method="POST" action="{{ route('messages.store') }}">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To <span class="text-red-500">*</span></label>
                        <select name="receiver_id" class="w-full rounded-lg border-gray-300 text-sm" required>
                            <option value="">Select Recipient</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('receiver_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('receiver_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                        <textarea name="body" rows="6" class="w-full rounded-lg border-gray-300 text-sm" required>{{ old('body') }}</textarea>
                        @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
