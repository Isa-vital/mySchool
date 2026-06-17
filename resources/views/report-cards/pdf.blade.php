<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Card - {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            color: #1e40af;
        }

        .header p {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .header .title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            width: 50%;
            padding: 3px 0;
        }

        .info-cell .label {
            color: #888;
            font-size: 9px;
            text-transform: uppercase;
        }

        .info-cell .value {
            font-weight: bold;
            font-size: 11px;
        }

        table.grades {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.grades th {
            background: #1e40af;
            color: #fff;
            padding: 8px 6px;
            font-size: 10px;
            text-transform: uppercase;
            text-align: left;
        }

        table.grades td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }

        table.grades tr:nth-child(even) {
            background: #f9fafb;
        }

        .summary {
            margin-top: 15px;
            padding: 10px;
            background: #f0f4ff;
            border-radius: 4px;
        }

        .summary p {
            margin-bottom: 4px;
        }

        .footer {
            margin-top: 40px;
            display: table;
            width: 100%;
        }

        .sig-block {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding-top: 30px;
        }

        .sig-line {
            border-top: 1px solid #333;
            width: 80%;
            margin: 0 auto;
        }

        .sig-label {
            font-size: 9px;
            color: #666;
            margin-top: 4px;
        }
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

    @if(($formatted['format'] ?? 'primary') === 'primary')
    <table class="grades">
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th style="width:54%;">Subject</th>
                <th style="width:20%;">Marks</th>
                <th style="width:20%;">Achievement</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($formatted['subjects'] ?? []) as $i => $subject)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $subject['subject'] }}</td>
                <td>{{ $subject['marks'] }} / 100</td>
                <td><strong>{{ $subject['grade'] }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @elseif(($formatted['format'] ?? 'primary') === 'o-level')
    <table class="grades">
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th style="width:49%;">Subject</th>
                <th style="width:15%;">Marks</th>
                <th style="width:15%;">Grade</th>
                <th style="width:15%;">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($formatted['subjects'] ?? []) as $i => $subject)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $subject['subject'] }}</td>
                <td>{{ $subject['marks'] }} / 100</td>
                <td><strong>{{ $subject['grade'] }}</strong></td>
                <td>{{ $subject['points'] }}</td>
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
        <p><strong>Average:</strong> {{ $formatted['average'] }}%</p>
        <p><strong>Best-8 Aggregate:</strong> {{ $formatted['aggregate_points'] }}</p>
        <p><strong>Division:</strong> {{ $formatted['overall_grade'] }}</p>
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