<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Fee Structure</h2>
            <a href="{{ route('fee-structures.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('fee-structures.update', $feeStructure) }}">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fee Type <span class="text-red-500">*</span></label>
                    <select name="fee_type_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        @foreach($feeTypes as $t)
                            <option value="{{ $t->id }}" {{ old('fee_type_id', $feeStructure->fee_type_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $feeStructure->amount) }}" required step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                    <select name="school_class_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('school_class_id', $feeStructure->school_class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                    <select name="academic_year_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ old('academic_year_id', $feeStructure->academic_year_id) == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                    <select name="term_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">All Terms</option>
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ old('term_id', $feeStructure->term_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('fee-structures.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg shadow-sm" style="background: var(--primary-color);">Update</button>
        </div>
    </form>
</x-app-layout>
