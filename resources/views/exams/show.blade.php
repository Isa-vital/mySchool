<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $exam->name }}</h2>
            <div class="flex items-center space-x-3">
                {{-- CHANGED (A2): status workflow — draft → marks entry → locked → published --}}
                <span class="px-2.5 py-1 text-xs font-medium rounded-full
                    {{ match($exam->status) {
                        'published' => 'bg-green-100 text-green-700',
                        'locked' => 'bg-orange-100 text-orange-700',
                        'marks_entry_open' => 'bg-blue-100 text-blue-700',
                        default => 'bg-gray-100 text-gray-600',
                    } }}">{{ ucwords(str_replace('_', ' ', $exam->status)) }}</span>

                @can('exams.edit')
                @if($exam->status === 'draft')
                <form method="POST" action="{{ route('exams.status', $exam) }}" class="inline">
                    @csrf <input type="hidden" name="action" value="open">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700">Open Marks Entry</button>
                </form>
                @elseif($exam->status === 'marks_entry_open')
                <form method="POST" action="{{ route('exams.status', $exam) }}" class="inline" onsubmit="return confirm('Lock marks entry? Teachers will no longer be able to change marks.')">
                    @csrf <input type="hidden" name="action" value="lock">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-orange-500 hover:bg-orange-600">Lock Marks</button>
                </form>
                @endif
                @endcan

                @can('exams.moderate')
                @if($exam->status === 'locked')
                <form method="POST" action="{{ route('exams.status', $exam) }}" class="inline" onsubmit="return confirm('Unlock marks entry? This re-opens editing — the change is recorded in the audit log.')">
                    @csrf <input type="hidden" name="action" value="unlock">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Unlock</button>
                </form>
                @elseif($exam->status === 'published')
                <form method="POST" action="{{ route('exams.status', $exam) }}" class="inline" onsubmit="return confirm('Unpublish these results? Parents will no longer see them.')">
                    @csrf <input type="hidden" name="action" value="unpublish">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Unpublish</button>
                </form>
                @endif
                @endcan

                {{-- CHANGED (UX): quick actions — jump straight into marks entry / report cards / publish --}}
                @can('grades.create')
                @if($exam->acceptsMarks())
                <a href="{{ $exam->is_report_card ? route('grades.enter-grid', ['report_exam' => $exam->id]) : route('grades.enter', ['exam' => $exam->id]) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-emerald-600 hover:bg-emerald-700">Enter Marks</a>
                @endif
                @endcan
                @can('report_cards.view')
                @if($exam->is_report_card)
                <a href="{{ route('report-cards.index', ['term_id' => $exam->term_id]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Report Cards</a>
                @endif
                @endcan
                @can('exams.edit')
                {{-- CHANGED (A2): publish only offered when the exam is LOCKED --}}
                @if($exam->status === 'locked')
                <button type="button" id="publish-exam-btn" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-amber-500 hover:bg-amber-600">Publish</button>
                @endif
                <a href="{{ route('exams.edit', $exam) }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color: var(--primary-color);">Edit</a>
                @endcan
                <a href="{{ route('exams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Details --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Academic Year</p>
                    <p class="text-sm font-medium text-gray-900">{{ $exam->academicYear->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Term</p>
                    <p class="text-sm font-medium text-gray-900">{{ $exam->term->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Dates</p>
                    <p class="text-sm font-medium text-gray-900">
                        @if($exam->start_date)
                        {{ $exam->start_date->format('d M') }}{{ $exam->end_date ? ' – ' . $exam->end_date->format('d M Y') : '' }}
                        @else
                        -
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Status</p>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $exam->is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $exam->is_published ? 'Published' : 'Draft' }}</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Format</p>
                    {{-- CHANGED (A1): NULL means auto-detect per student's class --}}
                    <p class="text-sm font-medium text-gray-900">{{ $exam->assessment_format ? ucfirst($exam->assessment_format) : 'Auto (based on class)' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Report Type</p>
                    <p class="text-sm font-medium text-gray-900">{{ $exam->is_report_card ? 'Composite Report Card' : 'Single Exam' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Grading Profile</p>
                    <p class="text-sm font-medium text-gray-900">{{ $exam->gradingScale->name ?? 'Format Default' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Max Marks / Points</p>
                    <p class="text-sm font-medium text-gray-900">{{ $exam->max_points ?? 100 }}</p>
                </div>
            </div>
            @if($exam->description)
            <div class="mt-4 pt-4 border-t">
                <p class="text-xs text-gray-500 uppercase mb-1">Description</p>
                <p class="text-sm text-gray-700">{{ $exam->description }}</p>
            </div>
            @endif
        </div>

        @if($exam->is_report_card)
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-sm font-semibold text-gray-800">Report Card Components</h3>
            </div>
            @if($exam->reportComponents->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Weight</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($exam->reportComponents as $componentExam)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $componentExam->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $componentExam->term->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $componentExam->academicYear->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-center font-medium text-gray-900">{{ rtrim(rtrim(number_format((float) $componentExam->pivot->weight, 2, '.', ''), '0'), '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-8 text-center text-gray-400 text-sm">No component exams selected yet.</div>
            @endif
        </div>
        @endif

        {{-- Exam Schedules --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800">Exam Schedules ({{ $exam->schedules->count() }})</h3>
            </div>

            @if($exam->schedules->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Full Marks</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pass Marks</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                            @can('exams.edit')
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($exam->schedules->sortBy(['schoolClass.name', 'subject.name']) as $schedule)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $schedule->schoolClass->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $schedule->subject->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $schedule->full_marks }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $schedule->pass_marks }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $schedule->exam_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($schedule->start_time && $schedule->end_time)
                                {{ $schedule->start_time }} – {{ $schedule->end_time }}
                                @else
                                -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $schedule->room ?? '-' }}</td>
                            @can('exams.edit')
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('exam-schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Remove this schedule?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                                </form>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-8 text-center text-gray-400 text-sm">No schedules yet. Add one below.</div>
            @endif

            {{-- Add Schedule Form --}}
            @can('exams.edit')
            <div class="px-6 py-4 border-t bg-gray-50">
                <h4 class="text-xs font-semibold text-gray-600 uppercase mb-3">Add Schedule</h4>
                <form method="POST" action="{{ route('exams.schedules.store', $exam) }}">
                    @csrf
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <select name="school_class_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                <option value="">Class *</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="subject_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                <option value="">Subject *</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input type="number" name="full_marks" placeholder="Full Marks *" value="100" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <input type="number" name="pass_marks" placeholder="Pass Marks *" value="40" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <input type="date" name="exam_date" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="Date">
                        </div>
                        <div>
                            <input type="time" name="start_time" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="Start">
                        </div>
                        <div>
                            <input type="time" name="end_time" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="End">
                        </div>
                        <div>
                            <input type="text" name="room" placeholder="Room" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background: var(--primary-color);">Add Schedule</button>
                    </div>
                </form>
            </div>
            @endcan
        </div>

        {{-- Quick Actions --}}
        <div class="flex flex-wrap gap-3">
            @can('grades.create')
            @if(!$exam->is_report_card)
            <a href="{{ route('grades.enter', $exam) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Enter Grades
            </a>
            @endif
            @endcan
            @can('report_cards.view')
            @if($exam->is_published)
            <a href="{{ route('report-cards.index', ['exam_id' => $exam->id]) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-green-600 hover:bg-green-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                View Report Cards
            </a>
            @endif
            @endcan
        </div>

        {{-- CHANGED (UX): Marks completeness matrix — see what still needs marks and jump straight in --}}
        @can('grades.view')
        @if(count($completeness))
        @php
        $pendingCells = collect($completeness)->flatMap(fn($row) => $row['cells'])->reject(fn($c) => $c['complete'])->count();
        @endphp
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden" id="marks-progress">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800">Marks Progress</h3>
                @if($pendingCells === 0)
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">All marks entered</span>
                @else
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">{{ $pendingCells }} subject(s) pending</span>
                @endif
            </div>
            <div class="p-4 space-y-4">
                @foreach($completeness as $row)
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase mb-2">{{ $row['class']->name }} <span class="font-normal normal-case text-gray-400">({{ $row['total'] }} students)</span></p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($row['cells'] as $cell)
                        <a href="{{ $cell['url'] }}" title="Enter marks for {{ $cell['subject']->name }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-medium hover:shadow-sm transition
                                   {{ $cell['complete'] ? 'bg-green-50 border-green-200 text-green-700' : ($cell['entered'] > 0 ? 'bg-yellow-50 border-yellow-200 text-yellow-700' : 'bg-gray-50 border-gray-200 text-gray-600') }}">
                            @if($cell['complete'])
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            @endif
                            {{ $cell['subject']->name }}
                            <span class="opacity-70">{{ $cell['entered'] }}/{{ $row['total'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endcan
    </div>

    {{-- CHANGED (UX): publish confirmation — warns about pending marks and confirms before guardian emails go out --}}
    {{-- CHANGED (A2): only rendered when the exam is locked (publishing requires locked). --}}
    @can('exams.edit')
    @if($exam->status === 'locked')
    @php
    $pendingForPublish = collect($completeness ?? [])->flatMap(fn($row) => $row['cells'])->reject(fn($c) => $c['complete'])->count();
    $gradedStudentCount = $exam->grades->pluck('student_id')->unique()->count();
    @endphp
    <script>
        document.getElementById('publish-exam-btn')?.addEventListener('click', function() {
            const pending = {
                {
                    (int) $pendingForPublish
                }
            };
            const students = {
                {
                    (int) $gradedStudentCount
                }
            };
            Swal.fire({
                title: 'Publish results?',
                html: (pending > 0 ?
                        '<p class="text-amber-600 font-medium mb-2">⚠ ' + pending + ' subject(s) still have missing marks.</p>' :
                        '<p class="text-green-600 font-medium mb-2">All marks are entered.</p>') +
                    '<p>Guardians of ' + students + ' graded student(s) will be emailed immediately.</p>',
                icon: pending > 0 ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: 'Publish & notify',
                cancelButtonText: 'Not yet',
            }).then((result) => {
                if (!result.isConfirmed) return;
                fetch('{{ route('
                    exams.publish ', $exam) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                    }).then(r => r.json()).then(data => {
                    Swal.fire({
                            title: data.success ? 'Published' : 'Not published',
                            text: data.message,
                            icon: data.success ? 'success' : 'error'
                        })
                        .then(() => {
                            if (data.success) window.location.reload();
                        });
                }).catch(() => Swal.fire({
                    title: 'Error',
                    text: 'Could not publish. Try again.',
                    icon: 'error'
                }));
            });
        });
    </script>
    @endif
    @endcan
</x-app-layout>