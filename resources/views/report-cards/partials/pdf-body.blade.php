{{-- CHANGED (UX/bulk PDF): report body extracted from report-cards/pdf.blade.php so the
     single-student and bulk (whole class) PDF templates share one layout.
     Expects: $student, $exam, $formatted, $enrollment, $reportCard, $position, $classSize, $componentExams
     WARNING: do not run Format Document on this file, and never write literal
     angle-bracket tag names inside comments - the formatter re-parses everything
     after them and destroys the file (it happened here). --}}
@php
$badgePath = setting('school_badge') ? public_path('storage/' . setting('school_badge')) : null;
$logoPath = setting('school_logo') ? public_path('storage/' . setting('school_logo')) : null;
$studentPhotoPath = $student->photo ? public_path('storage/' . $student->photo) : null;
$badgeFile = $badgePath && file_exists($badgePath) ? $badgePath : ($logoPath && file_exists($logoPath) ? $logoPath : null);
$studentPhotoFile = $studentPhotoPath && file_exists($studentPhotoPath) ? $studentPhotoPath : null;
// Brand colour is applied via inline style attributes below (formatter-safe).
$brandColor = setting('primary_color', '#1e40af');
@endphp

<div class="header" style="border-bottom: 2px solid {{ $brandColor }};">
    <table class="header-table">
        <tr>
            <td class="header-left">
                @if($badgeFile)
                <img src="{{ $badgeFile }}" alt="School Badge" class="badge">
                @endif
            </td>
            <td class="header-middle">
                <h1 style="color: {{ $brandColor }};">{{ setting('school_name', 'MySchool') }}</h1>
                <p>{{ setting('school_address', '') }}</p>
                <p>{{ setting('school_phone', '') }} | {{ setting('school_email', '') }}</p>
                <div class="title" style="color: {{ $brandColor }};">{{ match ($formatted['format']) { 'o-level' => 'O-Level Progress Report', 'a-level' => 'A-Level Progress Report', default => 'Primary Progress Report' } }}</div>
            </td>
            <td class="header-right">
                @if($studentPhotoFile)
                <img src="{{ $studentPhotoFile }}" alt="Student Photo" class="student-photo">
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="info-grid">
    <div class="info-row">
        <div class="info-cell">
            <span class="label">Student Name:</span>
            <span class="value">{{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</span>
        </div>
        <div class="info-cell">
            <span class="label">Admission No:</span>
            <span class="value">{{ $student->admission_number ?? '-' }}</span>
        </div>
    </div>
    <div class="info-row">
        <div class="info-cell">
            <span class="label">Exam:</span>
            <span class="value">{{ $exam->name }}</span>
        </div>
        <div class="info-cell">
            <span class="label">Class:</span>
            <span class="value">{{ $enrollment->schoolClass->name ?? '-' }} {{ $enrollment->section->name ?? '' }}</span>
        </div>
    </div>
    <div class="info-row">
        <div class="info-cell">
            <span class="label">Gender / Age:</span>
            <span class="value">{{ $student->gender ?? '-' }} / {{ $student->date_of_birth ? $student->date_of_birth->age . ' yrs' : '-' }}</span>
        </div>
        <div class="info-cell">
            <span class="label">Boarding Status:</span>
            <span class="value">{{ ucfirst($student->boarding_status ?? 'day') }}</span>
        </div>
    </div>
    <div class="info-row">
        <div class="info-cell">
            <span class="label">Academic Year:</span>
            <span class="value">{{ $exam->academicYear->name ?? '-' }}</span>
        </div>
        <div class="info-cell">
            <span class="label">Term:</span>
            <span class="value">{{ $exam->term->name ?? '-' }}</span>
        </div>
    </div>
</div>

@if(($formatted['format'] ?? 'primary') === 'primary')
@php
// Dynamically collect exam names from report components (e.g., exams selected to appear on report)
$hasComponents = collect($formatted['subjects'] ?? [])->some(fn($s) => count($s['components'] ?? []) > 0);
$componentNames = [];
if ($hasComponents) {
foreach ($formatted['subjects'] ?? [] as $subject) {
foreach ($subject['components'] ?? [] as $comp) {
if (!in_array($comp['exam_name'], $componentNames)) {
$componentNames[] = $comp['exam_name'];
}
}
}
}
@endphp
<table class="grades">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:{{ $hasComponents ? 30 : 54 }}%;">Subject</th>
            @if($hasComponents)
            @foreach($componentNames as $compName)
            <th style="width:{{ floor(40 / count($componentNames)) }}%;">{{ substr($compName, 0, 15) }}</th>
            @endforeach
            @endif
            <th style="width:20%;">Overall</th>
            <th style="width:14%;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @foreach(($formatted['subjects'] ?? []) as $i => $subject)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $subject['subject'] }}</td>
            @if($hasComponents)
            @foreach($componentNames as $compName)
            @php
            $comp = collect($subject['components'] ?? [])->firstWhere('exam_name', $compName);
            @endphp
            <td>{{ $comp ? round($comp['percentage']) . '%' : '-' }}</td>
            @endforeach
            @endif
            <td><strong>{{ round($subject['marks']) }}%</strong></td>
            <td><strong>{{ $subject['grade'] }}</strong></td>
        </tr>
        @endforeach
    </tbody>
</table>
@elseif(($formatted['format'] ?? 'primary') === 'o-level')
@php
$hasOLevelComponents = collect($formatted['subjects'] ?? [])->some(fn($s) => count($s['components'] ?? []) > 0);
$oLevelCompNames = [];
if ($hasOLevelComponents) {
foreach ($formatted['subjects'] ?? [] as $subject) {
foreach ($subject['components'] ?? [] as $comp) {
if (!in_array($comp['exam_name'], $oLevelCompNames)) {
$oLevelCompNames[] = $comp['exam_name'];
}
}
}
}
@endphp
<table class="grades">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:{{ $hasOLevelComponents ? 25 : 59 }}%;">Subject</th>
            @if($hasOLevelComponents)
            @foreach($oLevelCompNames as $compName)
            <th style="width:{{ floor(45 / count($oLevelCompNames)) }}%;">{{ substr($compName, 0, 15) }}</th>
            @endforeach
            @endif
            <th style="width:14%;">Grade</th>
            <th style="width:12%;">Points</th>
        </tr>
    </thead>
    <tbody>
        @foreach(($formatted['subjects'] ?? []) as $i => $subject)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $subject['subject'] }}</td>
            @if($hasOLevelComponents)
            @foreach($oLevelCompNames as $compName)
            @php
            $comp = collect($subject['components'] ?? [])->firstWhere('exam_name', $compName);
            @endphp
            <td>{{ $comp ? $comp['grade'] . ' (' . $comp['points'] . 'pt)' : '-' }}</td>
            @endforeach
            @endif
            <td><strong>{{ $subject['grade'] }}</strong></td>
            <td><strong>{{ $subject['points'] }}</strong></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<table class="grades">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:54%;">Subject</th>
            <th style="width:20%;">Marks</th>
            <th style="width:10%;">Grade</th>
            <th style="width:10%;">Points</th>
        </tr>
    </thead>
    <tbody>
        @foreach(($formatted['subjects'] ?? []) as $i => $subject)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $subject['subject'] }}</td>
            <td>{{ $subject['marks'] }}</td>
            <td><strong>{{ $subject['grade'] }}</strong></td>
            <td>{{ $subject['points'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="summary">
    @if(($formatted['format'] ?? 'primary') === 'primary')
    <p><strong>Average:</strong> {{ $formatted['average'] }}%</p>
    <p><strong>Overall Performance:</strong> {{ $formatted['overall_grade'] }}</p>
    <p><strong>Subjects Taken:</strong> {{ count($formatted['subjects'] ?? []) }}</p>
    @elseif(($formatted['format'] ?? 'primary') === 'o-level')
    <p><strong>Total Points:</strong> {{ $formatted['total_points'] }}</p>
    <p><strong>Average Points:</strong> {{ $formatted['average_points'] }}</p>
    <p><strong>Overall Competency:</strong> {{ $formatted['overall_grade'] }}</p>
    @else
    <p><strong>Total Points:</strong> {{ $formatted['total_points'] }} / {{ $formatted['max_points'] }}</p>
    <p><strong>Raw Subject Points:</strong> {{ $formatted['raw_points'] }}</p>
    <p><strong>Average Subject Points:</strong> {{ $formatted['average_points'] }}</p>
    @endif
    <p><strong>Position in Class:</strong> {{ $position ? $position . ' out of ' . $classSize : '-' }}</p>
    <p><strong>Conduct:</strong> {{ $reportCard->conduct ?? '-' }}</p>
</div>

<div class="summary" style="background:#fff; border:1px solid #e5e7eb;">
    <p><strong>Class Teacher's Comment:</strong> {{ $reportCard->class_teacher_comment ?? '.....................................................' }}</p>
    <p><strong>Head Teacher's Comment:</strong> {{ $reportCard->head_teacher_comment ?? '.....................................................' }}</p>
    @if($reportCard->next_term_begins)
    <p><strong>Next Term Begins:</strong> {{ $reportCard->next_term_begins->format('d M Y') }}</p>
    @endif
    @if(($componentExams ?? collect())->count() > 1)
    <p><strong>Assessment Components:</strong>
        @foreach($componentExams as $componentExam)
        {{ $componentExam->name }}@if(!$loop->last), @endif
        @endforeach
    </p>
    @endif
</div>

<div class="footer">
    <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-label">Class Teacher</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-label">Head Teacher</div>
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-label">Parent / Guardian</div>
    </div>
</div>