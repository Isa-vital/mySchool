<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Card - {{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</title>
    {{-- CHANGED (UX/bulk PDF): styles and report body moved to shared partials
         (report-cards/partials/pdf-styles + pdf-body) so this single-student template and the
         new bulk whole-class template (report-cards/pdf-bulk) render identical reports. --}}
    @include('report-cards.partials.pdf-styles')
</head>

<body>
    @include('report-cards.partials.pdf-body')
</body>

</html>