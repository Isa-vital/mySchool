<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card - {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e40af; padding-bottom: 15px; }
        .header h1 { font-size: 18px; color: #1e40af; }
        .header p { font-size: 10px; color: #666; margin-top: 3px; }
        .header .title { font-size: 14px; font-weight: bold; margin-top: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .info-grid { display: table; width: 100%; margin-bottom: 15px; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; width: 50%; padding: 3px 0; }
        .info-cell .label { color: #888; font-size: 9px; text-transform: uppercase; }
        .info-cell .value { font-weight: bold; font-size: 11px; }
        table.grades { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.grades th { background: #1e40af; color: #fff; padding: 8px 6px; font-size: 10px; text-transform: uppercase; text-align: left; }
        table.grades td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        table.grades tr:nth-child(even) { background: #f9fafb; }
        .summary { margin-top: 15px; padding: 10px; background: #f0f4ff; border-radius: 4px; }
        .summary p { margin-bottom: 4px; }
        .footer { margin-top: 40px; display: table; width: 100%; }
        .sig-block { display: table-cell; width: 33%; text-align: center; padding-top: 30px; }
        .sig-line { border-top: 1px solid #333; width: 80%; margin: 0 auto; }
        .sig-label { font-size: 9px; color: #666; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ setting('school_name', 'MySchool') }}</h1>
        <p>{{ setting('school_address', '') }}</p>
        <p>{{ setting('school_phone', '') }} | {{ setting('school_email', '') }}</p>
        <div class="title">Student Report Card</div>
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
                <span class="label">Academic Year:</span>
                <span class="value">{{ $exam->academicYear->name ?? '-' }}</span>
            </div>
            <div class="info-cell">
                <span class="label">Term:</span>
                <span class="value">{{ $exam->term->name ?? '-' }}</span>
            </div>
        </div>
    </div>

    <table class="grades">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:45%;">Subject</th>
                <th style="width:15%;">Score</th>
                <th style="width:15%;">Grade</th>
                <th style="width:20%;">Remark</th>
            </tr>
        </thead>
        <tbody>
            @php $totalScore = 0; $count = 0; @endphp
            @foreach($grades as $i => $grade)
                @php $totalScore += $grade->marks_obtained ?? 0; $count++; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $grade->subject->name ?? '-' }}</td>
                    <td>{{ $grade->marks_obtained ?? '-' }} / 100</td>
                    <td><strong>{{ $grade->grade_letter ?? '-' }}</strong></td>
                    <td>{{ $grade->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Total Score:</strong> {{ $totalScore }} / {{ $count * 100 }}</p>
        <p><strong>Average:</strong> {{ $count > 0 ? round($totalScore / $count, 1) : 0 }}%</p>
        <p><strong>Subjects Taken:</strong> {{ $count }}</p>
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
</body>
</html>
