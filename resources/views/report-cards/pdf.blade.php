<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card - {{ $student->full_name }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #1f2937; margin: 20px; }
    </style>
</head>
<body>
    @php($pdfMode = true)
    @include('report-cards._card')
</body>
</html>
