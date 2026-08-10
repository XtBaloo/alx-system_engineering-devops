<table style="width:100%; border-collapse: collapse; margin-bottom: 16px;">
    <tr>
        <td style="width:80px; vertical-align:top;">
            @if($settings->logo_path)
                <img src="{{ $pdfMode ? public_path('storage/'.$settings->logo_path) : Storage::disk('public')->url($settings->logo_path) }}" style="height:64px; width:64px; border-radius:50%;">
            @endif
        </td>
        <td style="text-align:center; vertical-align:top;">
            <h1 style="margin:0; font-size:22px; letter-spacing:1px; color:#065f46;">{{ strtoupper($settings->school_name) }}</h1>
            @if($settings->motto)<p style="margin:2px 0; font-style:italic; font-size:12px;">"{{ $settings->motto }}"</p>@endif
            <p style="margin:2px 0; font-size:11px;">{{ $settings->address }}</p>
            <p style="margin:2px 0; font-size:11px;">{{ $settings->phone }} {{ $settings->email ? '· '.$settings->email : '' }}</p>
            <h2 style="margin:8px 0 0; font-size:14px; text-decoration:underline;">TERMLY REPORT CARD</h2>
        </td>
        <td style="width:80px; text-align:right; vertical-align:top;">
            @if($student->photo_path)
                <img src="{{ $pdfMode ? public_path('storage/'.$student->photo_path) : Storage::disk('public')->url($student->photo_path) }}" style="height:70px; width:60px; object-fit:cover; border:1px solid #ccc;">
            @endif
        </td>
    </tr>
</table>

<table style="width:100%; border-collapse: collapse; font-size:12px; margin-bottom:14px;">
    <tr>
        <td style="padding:3px 0;"><strong>Name:</strong> {{ $student->full_name }}</td>
        <td style="padding:3px 0;"><strong>Admission No:</strong> {{ $student->admission_number }}</td>
    </tr>
    <tr>
        <td style="padding:3px 0;"><strong>Class:</strong> {{ $student->currentClassArm?->full_name }}</td>
        <td style="padding:3px 0;"><strong>Gender:</strong> {{ ucfirst($student->gender) }}</td>
    </tr>
    <tr>
        <td style="padding:3px 0;"><strong>Session:</strong> {{ $settings->currentAcademicSession?->name }}</td>
        <td style="padding:3px 0;"><strong>Term:</strong> {{ $term?->name }}</td>
    </tr>
    <tr>
        <td style="padding:3px 0;"><strong>No. in Class:</strong> {{ $classSize ?? '—' }}</td>
        <td style="padding:3px 0;"><strong>Attendance:</strong> {{ $attendance['present'] ?? 0 }} / {{ $totalDays ?: '—' }}</td>
    </tr>
</table>

<table style="width:100%; border-collapse: collapse; font-size:11px; margin-bottom:16px;" border="1" cellpadding="5">
    <thead>
        <tr style="background:#065f46; color:#fff;">
            <th style="text-align:left;">Subject</th>
            <th>CA/Assessment</th>
            <th>Exam</th>
            <th>Total</th>
            <th>Grade</th>
            <th>Remark</th>
            <th>Position</th>
        </tr>
    </thead>
    <tbody>
        @forelse($results as $r)
            <tr>
                <td>{{ $r->subject?->name }}</td>
                <td style="text-align:center;">{{ $r->assessment_total }}</td>
                <td style="text-align:center;">{{ $r->examination_score }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $r->total_score }}</td>
                <td style="text-align:center;">{{ $r->grade }}</td>
                <td style="text-align:center;">{{ $r->remark }}</td>
                <td style="text-align:center;">{{ $r->position }}@if($r->subject_class_size)/{{ $r->subject_class_size }}@endif</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center; padding:12px;">No published results available for this term yet.</td></tr>
        @endforelse
    </tbody>
    @if($results->isNotEmpty())
    <tfoot>
        <tr style="font-weight:bold; background:#f0fdf4;">
            <td>Average</td><td colspan="2"></td><td style="text-align:center;">{{ $average }}</td><td colspan="3"></td>
        </tr>
    </tfoot>
    @endif
</table>

<table style="width:100%; border-collapse: collapse; font-size:11px; margin-bottom:20px;">
    <tr>
        <td style="width:33%; vertical-align:top; padding-right:10px;">
            <strong>Attendance Summary</strong><br>
            Present: {{ $attendance['present'] ?? 0 }}<br>
            Absent: {{ $attendance['absent'] ?? 0 }}<br>
            Late: {{ $attendance['late'] ?? 0 }}<br>
            Excused: {{ $attendance['excused'] ?? 0 }}
        </td>
        <td style="width:33%; vertical-align:top; padding-right:10px;">
            <strong>Grading Key</strong><br>
            @foreach(\App\Models\GradingScale::orderBy('order')->get() as $g)
                {{ $g->grade }}: {{ $g->min_score }}-{{ $g->max_score }} ({{ $g->remark }})<br>
            @endforeach
        </td>
        <td style="width:33%; vertical-align:top;">
            <strong>Overall Average</strong><br>
            <span style="font-size:20px; font-weight:bold; color:#065f46;">{{ $average }}%</span>
        </td>
    </tr>
</table>

<table style="width:100%; border-collapse: collapse; font-size:11px;">
    <tr>
        <td style="vertical-align:top; padding-bottom:20px;">
            <strong>Teacher's Comment:</strong>
            <p>{{ $results->first()?->teacher_comment ?? 'Keep up the good work.' }}</p>
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;">
            <strong>Principal's Comment:</strong>
            <p>{{ $results->first()?->principal_comment ?? $settings->report_card_footer_note ?? 'A good result. Well done.' }}</p>
        </td>
    </tr>
</table>

<table style="width:100%; border-collapse: collapse; font-size:11px; margin-top:30px;">
    <tr>
        <td style="width:50%; text-align:center;">
            @if($settings->school_signature_path)
                <img src="{{ $pdfMode ? public_path('storage/'.$settings->school_signature_path) : Storage::disk('public')->url($settings->school_signature_path) }}" style="height:40px;"><br>
            @endif
            <div style="border-top:1px solid #333; margin-top:4px; padding-top:2px; width:70%; margin-left:auto; margin-right:auto;">Class Teacher's Signature</div>
        </td>
        <td style="width:50%; text-align:center;">
            @if($settings->principal_signature_path)
                <img src="{{ $pdfMode ? public_path('storage/'.$settings->principal_signature_path) : Storage::disk('public')->url($settings->principal_signature_path) }}" style="height:40px;"><br>
            @endif
            <div style="border-top:1px solid #333; margin-top:4px; padding-top:2px; width:70%; margin-left:auto; margin-right:auto;">{{ $settings->principal_name ?? 'Principal' }}'s Signature</div>
        </td>
    </tr>
</table>
