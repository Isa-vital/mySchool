<x-mail::message>
    # Exam Results Published

    Dear {{ $student->full_name ?? 'Parent/Guardian' }},

    The results for **{{ $exam->name }}** ({{ $exam->academicYear->name ?? '' }} - {{ $exam->term->name ?? '' }}) have been published.

    You can now view the detailed report card by logging into the school portal.

    @php
    $grades = \App\Models\Grade::where('student_id', $student->id)
    ->where('exam_id', $exam->id)
    ->with('subject')
    ->get();
    @endphp

    @if($grades->count())
    **Results Summary:**

    | Subject | Marks | Grade |
    |---------|-------|-------|
    @foreach($grades as $grade)
    | {{ $grade->subject->name ?? '' }} | {{ $grade->marks_obtained }} | {{ $grade->grade_letter ?? '-' }} |
    @endforeach

    **Average:** {{ round($grades->avg('marks_obtained'), 1) }}%
    @endif

    Regards,
    {{ setting('school_name', config('app.name')) }}
</x-mail::message>