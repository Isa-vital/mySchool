<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Invoice</h2>
            <a href="{{ route('invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student <span class="text-red-500">*</span></label>
                    <select name="student_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>{{ $student->full_name }} ({{ $student->admission_number }})</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                    <select name="academic_year_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ old('academic_year_id', $currentYear?->id) == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                    <select name="term_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">Select Term</option>
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ old('term_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6" x-data="invoiceItems()">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Line Items</h3>
            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-12 gap-3 mb-3">
                    <div class="col-span-5">
                        <select :name="'items['+index+'][fee_type_id]'" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            <option value="">Select Fee Type</option>
                            @foreach($feeTypes as $ft)
                                <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-3">
                        <input type="text" :name="'items['+index+'][description]'" x-model="item.description" placeholder="Description" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div class="col-span-3">
                        <input type="number" :name="'items['+index+'][amount]'" x-model="item.amount" step="0.01" placeholder="Amount" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div class="col-span-1 flex items-center">
                        <button type="button" @click="items.splice(index, 1)" class="text-red-500 hover:text-red-700" x-show="items.length > 1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </template>
            <button type="button" @click="items.push({description:'', amount:''})" class="text-sm font-medium hover:underline" style="color: var(--primary-color);">+ Add Item</button>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('invoices.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Create Invoice</button>
        </div>
    </form>

    <script>
        function invoiceItems() {
            return { items: [{ description: '', amount: '' }] };
        }
    </script>
</x-app-layout>
