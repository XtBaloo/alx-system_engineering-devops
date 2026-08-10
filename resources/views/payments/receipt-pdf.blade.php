<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $payment->receipt_number }}</title>
    <style>body { font-family: 'DejaVu Sans', sans-serif; color: #1f2937; margin: 16px; }</style>
</head>
<body>
    @php($pdfMode = true)
    @include('payments._receipt')
</body>
</html>
