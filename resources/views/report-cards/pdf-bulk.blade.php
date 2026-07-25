<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Cards - {{ $className }} - {{ $exam->name }}</title>
    {{-- CHANGED (UX): bulk whole-class report card PDF — one document, one page per student.
         Shares partials with the single-student template (report-cards/pdf). --}}
    @include('report-cards.partials.pdf-styles')
</head>

<body>
    @foreach($reports as $report)
    @include('report-cards.partials.pdf-body', $report)
    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach
</body>

</html>