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
            {{-- CHANGED: show the EMIS Learner Identification Number; fall back to the
                 internal admission number for students without a LIN yet. --}}
            <span class="label">{{ $student->lin ? 'LIN:' : 'Admission No:' }}</span>
            <span class="value">{{ $student->lin ?: ($student->admission_number ?? '-') }}</span>
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
// activity columns: every logged slot across the class (A1..An, not a fixed count)
$activityCount = (int) collect($formatted['subjects'] ?? [])->map(fn($s) => empty($s['activities']) ? 0 : max(array_keys($s['activities'])))->max();
$caTotal = (int) ($formatted['ca_total'] ?? 20);
$eotTotal = (int) ($formatted['eot_total'] ?? 80);
@endphp
<table class="grades">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:{{ $hasOLevelComponents ? 16 : 22 }}%;">Subject</th>
            @if($hasOLevelComponents)
            @foreach($oLevelCompNames as $compName)
            <th>{{ substr($compName, 0, 12) }}</th>
            @endforeach
            @endif
            @for($n = 1; $n <= $activityCount; $n++)
                <th>A{{ $n }}</th>
                @endfor
                <th style="width:6%;">AVG</th>
                <th style="width:6%;">Ident</th>
                <th style="width:8%;">CA (/{{ $caTotal }})</th>
                <th style="width:8%;">EOT (/{{ $eotTotal }})</th>
                <th style="width:8%;">Final</th>
                <th style="width:7%;">Grade</th>
                <th style="width:7%;">Points</th>
        </tr>
    </thead>
    <tbody>
        @foreach(($formatted['subjects'] ?? []) as $i => $subject)
        @php
        // INCOMPLETE / NOT YET ASSESSED print explicitly — never a blank cell or a silent E
        $rowStatus = $subject['status'] ?? 'graded';
        $stateLabel = match ($rowStatus) { 'incomplete' => 'INCOMPLETE', 'not_yet_assessed' => 'NOT YET ASSESSED', default => null };
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $subject['subject'] }}</td>
            @if($hasOLevelComponents)
            @foreach($oLevelCompNames as $compName)
            @php
            $comp = collect($subject['components'] ?? [])->firstWhere('exam_name', $compName);
            @endphp
            <td>{{ $comp ? $comp['grade'] . ' (' . $comp['points'] . 'pt)' : '—' }}</td>
            @endforeach
            @endif
            @for($n = 1; $n <= $activityCount; $n++)
                <td>{{ isset($subject['activities'][$n]) ? number_format($subject['activities'][$n], 2) : '—' }}</td>
                @endfor
                <td>{{ $subject['activity_avg'] !== null ? number_format($subject['activity_avg'], 2) : '—' }}</td>
                <td>{{ $subject['identifier'] ?? '' }}</td>
                <td>{{ $subject['ca_mark'] !== null ? number_format($subject['ca_mark'], 2) : '—' }}</td>
                <td>{{ $subject['eot_raw_score'] !== null ? number_format($subject['eot_raw_score'], 2) : strtoupper($subject['eot_status'] ?? '—') }}</td>
                @if($stateLabel)
                <td colspan="3" style="color:#b45309; font-size:8px;"><strong>{{ $stateLabel }}</strong></td>
                @else
                <td><strong>{{ $subject['final_mark'] !== null ? round($subject['final_mark']) : '—' }}</strong></td>
                <td><strong>{{ $subject['grade'] }}</strong></td>
                <td><strong>{{ $subject['points'] }}</strong></td>
                @endif
        </tr>
        @endforeach
        <tr>
            {{-- Total Points states its own denominator: resolved subjects only --}}
            <td colspan="{{ 2 + ($hasOLevelComponents ? count($oLevelCompNames) : 0) + $activityCount + 4 }}" style="text-align:right;"><strong>Total Points:</strong></td>
            <td colspan="3"><strong>{{ $formatted['total_points'] }} / ({{ $formatted['resolved_subject_count'] }} subjects &times; {{ $formatted['max_points_per_subject'] }})</strong></td>
        </tr>
    </tbody>
</table>
@php
$projectRows = collect($formatted['subjects'] ?? [])->filter(fn($s) => $s['project_score_raw'] !== null)->values();
@endphp
@if($projectRows->isNotEmpty())
{{-- Project work: own score and grade — never merged into the subject's final mark --}}
<table class="grades" style="margin-top:6px;">
    <thead>
        <tr>
            <th style="width:60%;">Project Work — Subject</th>
            <th style="width:20%;">Score</th>
            <th style="width:20%;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($projectRows as $projectRow)
        <tr>
            <td>{{ $projectRow['subject'] }}</td>
            <td>{{ number_format($projectRow['project_score_raw'], 1) }} / {{ (int) $projectRow['project_score_max'] }}</td>
            <td><strong>{{ $projectRow['project_grade'] ?? '—' }}</strong></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@else
{{-- CHANGED (A-Level rebuild): UACE layout — principals (A-F, 6-0 pts) and subsidiaries (pass = 1 pt). --}}
@if(!empty($formatted['combination_code']))
<p style="font-size:10px; margin:2px 0 4px;"><strong>Combination:</strong> {{ $formatted['combination_code'] }} — {{ $formatted['combination_name'] }}</p>
@elseif(!($formatted['has_combination'] ?? true))
<p style="font-size:9px; color:#b45309; margin:2px 0 4px;">No subject combination assigned — all subjects graded as principals. Assign a combination for a correct UACE report.</p>
@endif
@if($formatted['paper_based'] ?? false)
{{-- CHANGED (UACE paper rebuild): paper-level grading — one row per PAPER grouped under
     its subject. INCOMPLETE / PENDING REVIEW print explicitly: never a blank or a guess. --}}
@if($formatted['provisional'] ?? false)
<p style="font-size:9px; color:#b45309; margin:2px 0 4px;"><strong>PROVISIONAL:</strong> one or more subjects await results or manual grade confirmation. Points and position are incomplete.</p>
@endif
<table class="grades">
    <thead>
        <tr>
            <th style="width:32%;">Principal Subject</th>
            <th style="width:22%;">Paper</th>
            <th style="width:12%;">Mark</th>
            <th style="width:12%;">Band</th>
            <th style="width:12%;">Grade</th>
            <th style="width:10%;">Points</th>
        </tr>
    </thead>
    <tbody>
        @foreach(($formatted['subjects'] ?? []) as $subject)
        @php
        $paperRows = $subject['papers'] ?? [];
        $rowCount = max(count($paperRows), 1);
        $gradeLabel = match ($subject['status'] ?? 'graded') {
        'graded' => $subject['grade'],
        'incomplete' => 'INCOMPLETE',
        default => 'PENDING REVIEW',
        };
        $pointsLabel = ($subject['status'] ?? '') === 'graded' ? $subject['points'] : '—';
        @endphp
        @forelse($paperRows as $paper)
        <tr>
            @if($loop->first)
            <td rowspan="{{ $rowCount }}" style="vertical-align:middle;"><strong>{{ $subject['subject'] }}</strong></td>
            @endif
            <td>{{ $paper['label'] }}</td>
            <td>{{ $paper['percentage'] !== null ? round($paper['percentage']) . '%' : '—' }}</td>
            <td>{{ $paper['band'] }}</td>
            @if($loop->first)
            <td rowspan="{{ $rowCount }}" style="vertical-align:middle; {{ ($subject['status'] ?? '') !== 'graded' ? 'color:#b45309; font-size:8px;' : '' }}"><strong>{{ $gradeLabel }}</strong></td>
            <td rowspan="{{ $rowCount }}" style="vertical-align:middle;">{{ $pointsLabel }}</td>
            @endif
        </tr>
        @empty
        <tr>
            <td><strong>{{ $subject['subject'] }}</strong></td>
            <td colspan="3">No papers recorded</td>
            <td style="color:#b45309; font-size:8px;"><strong>{{ $gradeLabel }}</strong></td>
            <td>{{ $pointsLabel }}</td>
        </tr>
        @endforelse
        @endforeach
    </tbody>
</table>
@if(count($formatted['subsidiaries'] ?? []) > 0)
<table class="grades" style="margin-top:6px;">
    <thead>
        <tr>
            <th style="width:54%;">Subsidiary Subject</th>
            <th style="width:22%;">Mark</th>
            <th style="width:12%;">Result</th>
            <th style="width:12%;">Points</th>
        </tr>
    </thead>
    <tbody>
        @foreach($formatted['subsidiaries'] as $subsidiary)
        @php
        $subPaper = ($subsidiary['papers'] ?? [])[0] ?? null;
        $subResult = match ($subsidiary['status'] ?? 'graded') {
        'graded' => $subsidiary['grade'],
        'incomplete' => 'INCOMPLETE',
        default => 'PENDING REVIEW',
        };
        @endphp
        <tr>
            <td>{{ $subsidiary['subject'] }}</td>
            <td>{{ $subPaper && $subPaper['percentage'] !== null ? round($subPaper['percentage']) . '%' : ($subPaper['band'] ?? '—') }}</td>
            <td style="{{ ($subsidiary['status'] ?? '') !== 'graded' ? 'color:#b45309; font-size:8px;' : '' }}"><strong>{{ $subResult }}</strong></td>
            <td>{{ ($subsidiary['status'] ?? '') === 'graded' ? $subsidiary['points'] : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@else
{{-- CHANGED (UACE paper rebuild): historical term without per-paper results — the old
     blended figures are shown as-is and clearly flagged. They are NEVER recomputed. --}}
@if($formatted['legacy'] ?? false)
<p style="font-size:8px; color:#6b7280; margin:2px 0 4px; font-style:italic;">Historical record — graded under the previous blended-percentage method (before paper-level UACE grading).</p>
@endif
<table class="grades">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:54%;">Principal Subject</th>
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
            <td>{{ $subject['marks'] }}%</td>
            <td><strong>{{ $subject['grade'] }}</strong></td>
            <td>{{ $subject['points'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@if(count($formatted['subsidiaries'] ?? []) > 0)
<table class="grades" style="margin-top:6px;">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:54%;">Subsidiary Subject</th>
            <th style="width:20%;">Marks</th>
            <th style="width:10%;">Result</th>
            <th style="width:10%;">Points</th>
        </tr>
    </thead>
    <tbody>
        @foreach($formatted['subsidiaries'] as $i => $subsidiary)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $subsidiary['subject'] }}</td>
            <td>{{ $subsidiary['marks'] }}%</td>
            <td><strong>{{ $subsidiary['result'] }}</strong></td>
            <td>{{ $subsidiary['points'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endif
@endif

<div class="summary">
    @if(($formatted['format'] ?? 'primary') === 'primary')
    <p><strong>Average:</strong> {{ $formatted['average'] }}%</p>
    <p><strong>Overall Performance:</strong> {{ $formatted['overall_grade'] }}</p>
    <p><strong>Subjects Taken:</strong> {{ count($formatted['subjects'] ?? []) }}</p>
    @elseif(($formatted['format'] ?? 'primary') === 'o-level')
    {{-- Total Points states its denominator: only subjects with a resolved grade count --}}
    <p><strong>Total Points:</strong> {{ $formatted['total_points'] }} / ({{ $formatted['resolved_subject_count'] }} subjects &times; {{ $formatted['max_points_per_subject'] }})</p>
    <p><strong>Average Points:</strong> {{ $formatted['average_points'] }}</p>
    <p><strong>Overall Competency:</strong> {{ $formatted['overall_grade'] ?? 'NOT YET ASSESSED' }}@if(!empty($formatted['overall_descriptor']) && $formatted['overall_grade']) — {{ $formatted['overall_descriptor'] }}@endif</p>
    @else
    <p><strong>Principal Points:</strong> {{ $formatted['principal_points'] }}</p>
    <p><strong>Subsidiary Points:</strong> {{ $formatted['subsidiary_points'] }}</p>
    <p><strong>Total Points:</strong> {{ $formatted['total_points'] }} / {{ $formatted['max_points'] }}@if($formatted['provisional'] ?? false) <span style="color:#b45309;">(Provisional)</span>@endif</p>
    @endif
    <p><strong>Position in Class:</strong> {{ $position ? $position . ' out of ' . $classSize : (($formatted['provisional'] ?? false) ? 'Pending (provisional result)' : '-') }}</p>
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

{{-- CHANGED (legend): grading key so the report is self-explanatory. Ranges come from
     the same configurable grading scales used to grade the marks (single source of truth). --}}
@if(!empty($gradingKey ?? []))
<table style="width:100%; border-collapse:collapse; margin-top:8px; font-size:8px;">
    <thead>
        <tr>
            <th colspan="{{ count($gradingKey) }}" style="text-align:left; padding:3px 5px; background:#f3f4f6; border:1px solid #e5e7eb; font-size:8.5px;">Grading Key</th>
        </tr>
        <tr>
            @foreach($gradingKey as $band)
            <th style="padding:2px 4px; border:1px solid #e5e7eb; background:#fafafa;">{{ $band['grade'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr>
            @foreach($gradingKey as $band)
            <td style="padding:2px 4px; border:1px solid #e5e7eb; text-align:center;">
                {{ rtrim(rtrim(number_format((float) $band['min'], 1), '0'), '.') }}&ndash;{{ rtrim(rtrim(number_format((float) $band['max'], 1), '0'), '.') }}%
                @if(isset($band['points']) && $band['points'] !== null)
                <br>{{ rtrim(rtrim(number_format((float) $band['points'], 1), '0'), '.') }} pts
                @endif
            </td>
            @endforeach
        </tr>
        @if(collect($gradingKey)->contains(fn($b) => !empty($b['description']) && $b['description'] !== $b['grade']))
        <tr>
            @foreach($gradingKey as $band)
            <td style="padding:2px 4px; border:1px solid #e5e7eb; text-align:center; color:#4b5563;">{{ $band['description'] ?? '' }}</td>
            @endforeach
        </tr>
        @endif
    </tbody>
</table>
@endif

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

{{-- CHANGED (verification): QR + serial linking to the public /verify page (anti-forgery).
     The SVG string is prepared server-side so this template stays formatter-safe. --}}
@if(!empty($verificationCode ?? null))
<table style="width:100%; margin-top:10px; border-top:1px solid #e5e7eb; border-collapse:collapse;">
    <tr>
        @if(!empty($verificationQr ?? null))
        <td style="width:70px; padding:6px 8px 0 0; vertical-align:top;">{!! $verificationQr !!}</td>
        @endif
        <td style="padding-top:6px; vertical-align:top; font-size:8px; color:#6b7280;">
            <strong style="color:#111827;">Verify this report:</strong> scan the QR code or visit
            {{ route('report.verify', $verificationCode) }}<br>
            Serial: <span style="font-family:monospace; letter-spacing:1px;">{{ $verificationCode }}</span> &mdash;
            details shown online must match this printed report exactly.
        </td>
    </tr>
</table>
@endif