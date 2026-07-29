<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Report Card Verification - {{ setting('school_name', 'MySchool') }}</title>
    {{-- CHANGED (verification): standalone public page, no app layout/login required.
         Formatter-safe: no Blade expressions inside the style block. --}}
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, .08); max-width: 480px; width: 100%; overflow: hidden; }
        .banner { padding: 18px 24px; color: #fff; font-size: 18px; font-weight: 700; text-align: center; }
        .banner.valid { background: #16a34a; }
        .banner.invalid { background: #dc2626; }
        .body { padding: 24px; }
        .school { text-align: center; font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 16px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .row .label { color: #6b7280; }
        .row .value { color: #111827; font-weight: 600; text-align: right; }
        .note { margin-top: 16px; font-size: 12px; color: #9ca3af; text-align: center; }
        .serial { font-family: monospace; letter-spacing: 1px; }
    </style>
</head>

<body>
    <div class="card">
        @if($reportCard)
        <div class="banner valid">&#10003; VALID REPORT CARD</div>
        <div class="body">
            <div class="school">{{ setting('school_name', 'MySchool') }}</div>
            <div class="row">
                <span class="label">Student</span>
                <span class="value">{{ $reportCard->student->full_name ?? ($reportCard->student->first_name . ' ' . $reportCard->student->last_name) }}</span>
            </div>
            <div class="row">
                <span class="label">Class</span>
                <span class="value">{{ $enrollment->schoolClass->name ?? '-' }} {{ $enrollment->section->name ?? '' }}</span>
            </div>
            <div class="row">
                <span class="label">Report</span>
                <span class="value">{{ $reportCard->exam->name }}</span>
            </div>
            <div class="row">
                <span class="label">Term</span>
                <span class="value">{{ $reportCard->exam->term->name ?? '-' }}, {{ $reportCard->exam->academicYear->name ?? '-' }}</span>
            </div>
            @if($reportCard->average !== null)
            <div class="row">
                <span class="label">Average</span>
                <span class="value">{{ round($reportCard->average, 1) }}%</span>
            </div>
            @endif
            @if($reportCard->position)
            <div class="row">
                <span class="label">Position</span>
                <span class="value">{{ $reportCard->position }} of {{ $reportCard->class_size ?? '-' }}</span>
            </div>
            @endif
            <div class="row">
                <span class="label">Serial</span>
                <span class="value serial">{{ $reportCard->verification_code }}</span>
            </div>
            <div class="row">
                <span class="label">Issued</span>
                <span class="value">{{ $reportCard->updated_at?->format('d M Y') ?? '-' }}</span>
            </div>
            <p class="note">This confirmation is generated live from the school's official records. If the printed report differs from the details above, treat the printed copy as invalid.</p>
        </div>
        @else
        <div class="banner invalid">&#10007; NOT A VALID REPORT CARD</div>
        <div class="body">
            <div class="school">{{ setting('school_name', 'MySchool') }}</div>
            <p style="font-size:14px; color:#374151; text-align:center;">No report card matches this verification code. The document may be forged, or the code was mistyped.</p>
            <p class="note">Contact the school directly to confirm any document you suspect.</p>
        </div>
        @endif
    </div>
</body>

</html>
