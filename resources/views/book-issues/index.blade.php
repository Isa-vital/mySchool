<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Book Issues</h2>
    </x-slot>

    <div class="space-y-6">
        {{-- Issue Book Form --}}
        @can('book_issues.create')
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Issue a Book</h3>
            <form method="POST" action="{{ route('book-issues.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Book</label>
                    <select name="book_id" class="w-full rounded-lg border-gray-300 text-sm" required>
                        <option value="">Select Book</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->available_copies }} avail.)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Borrower Type</label>
                    <select name="borrower_type" class="w-full rounded-lg border-gray-300 text-sm" required>
                        <option value="App\Models\Student">Student</option>
                        <option value="App\Models\Staff">Staff</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Student / Staff</label>
                    <select name="borrower_id" class="w-full rounded-lg border-gray-300 text-sm" required>
                        <option value="">Select</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}">{{ $s->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Issue Date</label>
                    <input type="date" name="issue_date" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border-gray-300 text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" class="w-full rounded-lg border-gray-300 text-sm" required>
                </div>

                <div>
                    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Issue Book</button>
                </div>
            </form>
        </div>
        @endcan

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <form method="GET" class="flex items-center space-x-4">
                <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All Status</option>
                    @foreach(['issued', 'returned', 'overdue'] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Filter</button>
                <a href="{{ route('book-issues.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            </form>
        </div>

        {{-- Issues Table --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($issues as $issue)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $issue->book->title ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $issue->borrower->full_name ?? $issue->borrower->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $issue->issue_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm {{ $issue->status === 'issued' && $issue->due_date && $issue->due_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">{{ $issue->due_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $colors = ['issued' => 'bg-blue-100 text-blue-700', 'returned' => 'bg-green-100 text-green-700', 'overdue' => 'bg-red-100 text-red-700'];
                                @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $colors[$issue->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($issue->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($issue->status === 'issued')
                                    <form method="POST" action="{{ route('book-issues.return', $issue) }}" class="inline" onsubmit="return confirm('Mark as returned?')">
                                        @csrf @method('PATCH')
                                        <button class="text-sm font-medium text-green-600 hover:text-green-800">Return</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">{{ $issue->return_date?->format('d M Y') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No book issues found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-3 border-t">{{ $issues->links() }}</div>
        </div>
    </div>
</x-app-layout>
