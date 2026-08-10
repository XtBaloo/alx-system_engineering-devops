<table style="width:100%; border-collapse: collapse; margin-bottom: 12px;">
    <tr>
        <td style="width:60px;">
            @if($settings->logo_path)
                <img src="{{ $pdfMode ? public_path('storage/'.$settings->logo_path) : Storage::disk('public')->url($settings->logo_path) }}" style="height:50px; width:50px; border-radius:50%;">
            @endif
        </td>
        <td style="text-align:center;">
            <h1 style="margin:0; font-size:18px; color:#065f46;">{{ strtoupper($settings->school_name) }}</h1>
            <p style="margin:2px 0; font-size:10px;">{{ $settings->address }}</p>
            <p style="margin:2px 0; font-size:10px;">{{ $settings->phone }}</p>
            <h2 style="margin:6px 0 0; font-size:13px; text-decoration:underline;">PAYMENT RECEIPT</h2>
        </td>
        <td style="width:60px;"></td>
    </tr>
</table>

<table style="width:100%; border-collapse: collapse; font-size:12px; margin-bottom:12px;">
    <tr><td style="padding:2px 0;"><strong>Receipt No:</strong></td><td>{{ $payment->receipt_number }}</td></tr>
    <tr><td style="padding:2px 0;"><strong>Date:</strong></td><td>{{ $payment->payment_date->format('d M Y') }}</td></tr>
    <tr><td style="padding:2px 0;"><strong>Student:</strong></td><td>{{ $payment->studentFee->student->full_name }}</td></tr>
    <tr><td style="padding:2px 0;"><strong>Admission No:</strong></td><td>{{ $payment->studentFee->student->admission_number }}</td></tr>
    <tr><td style="padding:2px 0;"><strong>Class:</strong></td><td>{{ $payment->studentFee->student->currentClassArm?->full_name }}</td></tr>
    <tr><td style="padding:2px 0;"><strong>Fee Category:</strong></td><td>{{ $payment->studentFee->feeStructure->feeCategory->name }}</td></tr>
    <tr><td style="padding:2px 0;"><strong>Payment Method:</strong></td><td class="capitalize">{{ ucfirst(str_replace('_',' ',$payment->payment_method)) }}</td></tr>
</table>

<table style="width:100%; border-collapse: collapse; font-size:13px; margin-bottom:12px;" border="1" cellpadding="6">
    <tr><td><strong>Amount Paid</strong></td><td style="text-align:right; font-weight:bold;">₦{{ number_format($payment->amount, 2) }}</td></tr>
    <tr><td>Total Due</td><td style="text-align:right;">₦{{ number_format($payment->studentFee->amount_due, 2) }}</td></tr>
    <tr><td>Total Paid to Date</td><td style="text-align:right;">₦{{ number_format($payment->studentFee->amount_paid, 2) }}</td></tr>
    <tr><td><strong>Balance</strong></td><td style="text-align:right; font-weight:bold; color:#b91c1c;">₦{{ number_format($payment->studentFee->balance, 2) }}</td></tr>
</table>

<p style="font-size:11px;">Received by: {{ $payment->receivedBy?->name ?? 'System' }}</p>

<table style="width:100%; margin-top:30px; font-size:11px;">
    <tr>
        <td style="text-align:center; border-top:1px solid #333; padding-top:4px;">Authorized Signature</td>
    </tr>
</table>
