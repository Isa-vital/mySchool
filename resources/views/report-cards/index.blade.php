<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Report Cards</h2>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        {{-- CHANGED (UI fix): the Term field was un-wrapped and a stray closing div sat
             before the Load button, breaking the page nesting (giant buttons, shifted
             content). Every field now lives in its own fixed-width wrapper. --}}
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-64">
                <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                <select name="term_id" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Select Term</option>
                    @foreach($terms as $term)
                    {{-- CHANGED (UX): defaults to the current term ($selectedTermId from controller) --}}
                    <option value="{{ $term->id }}" {{ (string) ($selectedTermId ?? '') === (string) $term->id ? 'selected' : '' }}>
                        {{ $term->name }}
                        @if($term->academicYear)
                        ({{ $term->academicYear->name }})
                        @endif
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Load</button>
        </form>
    </div>

    @if(isset($students) && $students->count())
    {{-- CHANGED (UX): bulk actions — edit all comments on one page, download all PDFs in one file --}}
    @if(isset($selectedExam) && $selectedExam)
    <div class="flex flex-wrap gap-3 mb-4">
        @can('report_cards.edit')
        <a href="{{ route('report-cards.bulk-comments', ['exam' => $selectedExam->id, 'class_id' => request('class_id'), 'term_id' => $selectedTermId]) }}"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-indigo-600 hover:bg-indigo-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit All Comments
        </a>
        @endcan
        @can('report_cards.generate')
        {{-- CHANGED (A3): bulk PDF is generated on the queue; this button starts the job
             and polls for progress instead of holding the request open until timeout. --}}
        <button type="button" id="bulk-pdf-btn"
            data-start-url="{{ route('report-cards.bulk-pdf', ['exam' => $selectedExam->id, 'class_id' => request('class_id')]) }}"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-green-600 hover:bg-green-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span id="bulk-pdf-label">Generate All PDFs ({{ $students->count() }})</span>
        </button>
        <script>
            document.getElementById('bulk-pdf-btn').addEventListener('click', function () {
                const btn = this;
                const label = document.getElementById('bulk-pdf-label');
                btn.disabled = true;
                label.textContent = 'Starting…';
                const reset = () => { btn.disabled = false; label.textContent = 'Generate All PDFs'; };
                fetch(btn.dataset.startUrl, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) throw new Error(data.message || 'Could not start generation');
                        const poll = setInterval(() => {
                            fetch(data.status_url, { headers: { 'Accept': 'application/json' } })
                                .then(r => r.json())
                                .then(s => {
                                    if (s.status === 'running' || s.status === 'pending') {
                                        label.textContent = 'Generating… ' + (s.done ?? 0) + '/' + (s.total ?? '?');
                                    } else if (s.status === 'done') {
                                        clearInterval(poll);
                                        label.textContent = 'Done — downloading…';
                                        window.location.href = s.url;
                                        setTimeout(reset, 3000);
                                    } else if (s.status === 'failed') {
                                        clearInterval(poll);
                                        reset();
                                        Swal.fire({ title: 'Generation failed', text: s.message || 'Is the queue worker running? (php artisan queue:work)', icon: 'error' });
                                    }
                                });
                        }, 2000);
                    })
                    .catch(err => { reset(); Swal.fire({ title: 'Error', text: err.message, icon: 'error' }); });
            });
        </script>
        @endcan
    </div>
    @endif
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission No</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($students as $i => $student)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $student->full_name }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600">{{ $student->admission_number }}</td>
                    <td class="px-6 py-3 text-right text-sm">
                        @if(isset($selectedExam) && $selectedExam)
                        <a href="{{ route('report-cards.show', ['student' => $student->id, 'exam' => $selectedExam->id, 'class_id' => request('class_id'), 'term_id' => request('term_id')]) }}" class="text-blue-600 hover:text-blue-800 mr-3">View</a>
                        <a href="{{ route('report-cards.pdf', ['student' => $student->id, 'exam' => $selectedExam->id, 'class_id' => request('class_id'), 'term_id' => request('term_id')]) }}" class="text-green-600 hover:text-green-800">PDF</a>
                        @else
                        <span class="text-gray-400">No report exam in term</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border p-12 text-center text-gray-500">Select a class and term to view report cards.</div>
    @endif
</x-app-layout>