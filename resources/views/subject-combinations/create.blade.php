<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Subject Combination</h2>
            <a href="{{ route('subject-combinations.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('subject-combinations.store') }}">
        @csrf
        @include('subject-combinations.partials.form-fields')
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Create Combination</button>
        </div>
    </form>
</x-app-layout>