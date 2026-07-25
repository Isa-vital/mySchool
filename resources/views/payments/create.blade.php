<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Record Payment</h2>
            <a href="{{ route('payments.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border p-6" x-data="paymentForm()">
            <form method="POST" action="{{ route('payments.store') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Student --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Student <span class="text-red-500">*</span></label>
                        <select name="student_id" x-model="studentId" @change="fetchInvoices()" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id', $selectedStudentId) == $student->id ? 'selected' : '' }}>{{ $student->full_name }} ({{ $student->admission_number }})</option>
                            @endforeach
                        </select>
                        @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Invoice --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Invoice (optional)</label>
                        <select name="invoice_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">No specific invoice</option>
                            @foreach($invoices as $inv)
                                <option value="{{ $inv->id }}" {{ old('invoice_id', request('invoice_id')) == $inv->id ? 'selected' : '' }}>{{ $inv->invoice_number }} — Bal: {{ setting('currency_symbol', 'UGX') }} {{ number_format($inv->balance) }}</option>
                            @endforeach
                        </select>
                        @error('invoice_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount ({{ setting('currency_symbol', 'UGX') }}) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" value="{{ old('amount') }}" min="1" class="w-full rounded-lg border-gray-300 text-sm" required>
                        @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Payment Method --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                        <select name="payment_method" class="w-full rounded-lg border-gray-300 text-sm" required>
                            <option value="">Select</option>
                            @foreach(['cash', 'bank_transfer', 'mobile_money', 'cheque'] as $method)
                                <option value="{{ $method }}" {{ old('payment_method') == $method ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $method)) }}</option>
                            @endforeach
                        </select>
                        @error('payment_method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Reference --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Transaction ID</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    {{-- Payment Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 text-sm" required>
                        @error('payment_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    {{-- CHANGED (permanent formatter fix): dynamic values moved into data attributes on
         #payment-form-data — a code formatter mangles Blade echoes inside <script> blocks
         (it broke exams/show the same way). KEEP PHP/BLADE EXPRESSIONS OUT OF THIS BLOCK. --}}
    <span id="payment-form-data" class="hidden"
        data-student-id="{{ old('student_id', $selectedStudentId) }}"
        data-create-url="{{ route('payments.create') }}"></span>
    <script>
        function paymentForm() {
            const cfg = document.getElementById('payment-form-data').dataset;
            return {
                studentId: cfg.studentId,
                fetchInvoices() {
                    if (this.studentId) {
                        window.location.href = cfg.createUrl + '?student_id=' + this.studentId;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
