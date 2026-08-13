<x-layouts.dashboard title="Result — {{ $result->student?->full_name }}" :subtitle="$result->subject?->name">
    <div class="mb-4">
        <a href="{{ route('results.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">&larr; Back to Results</a>
    </div>

    <x-card class="mb-6">
        <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Student</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $result->student?->full_name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Class</dt>
                <dd class="mt-1">{{ $result->classArm?->full_name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Subject</dt>
                <dd class="mt-1">{{ $result->subject?->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Session / Term</dt>
                <dd class="mt-1">{{ $result->academicSession?->name }} — {{ $result->term?->name }}</dd>
            </div>
        </dl>
    </x-card>

    <x-card title="Score Breakdown" class="mb-6">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Score</th>
                        <th>Max</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessmentTypes as $type)
                        <tr>
                            <td>{{ $type->name }}</td>
                            <td>{{ $assessmentScores->get($type->id)?->score ?? '—' }}</td>
                            <td>{{ $type->max_score }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state title="No assessment components configured" /></td></tr>
                    @endforelse
                    <tr>
                        <td>Exam</td>
                        <td>{{ $examinationScore?->score ?? '—' }}</td>
                        <td>{{ $examMaxScore }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-semibold">
                        <td>Total</td>
                        <td colspan="2">{{ $result->total_score }} ({{ $result->grade ?? '—' }}@if($result->remark) — {{ $result->remark }}@endif)</td>
                    </tr>
                    <tr>
                        <td>Position</td>
                        <td colspan="2">{{ $result->position ?? '—' }}@if($result->subject_class_size) / {{ $result->subject_class_size }}@endif</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($assessmentScores->isEmpty() && ! $examinationScore)
            <div class="mt-4">
                <x-empty-state title="No scores recorded yet" description="Scores entered by the subject teacher will appear here." />
            </div>
        @endif
    </x-card>

    <x-card title="Workflow Status">
        @php($colors = ['draft'=>'badge-gray','submitted'=>'badge-blue','reviewed'=>'badge-yellow','approved'=>'badge-yellow','published'=>'badge-green'])
        <div class="mb-4">
            <span class="{{ $colors[$result->status] }}">{{ ucfirst($result->status) }}</span>
        </div>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Submitted</dt>
                <dd class="mt-1">{{ $result->submittedBy?->name ?? '—' }} @if($result->submitted_at) · {{ $result->submitted_at->format('d M Y H:i') }} @endif</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Reviewed</dt>
                <dd class="mt-1">{{ $result->reviewedBy?->name ?? '—' }} @if($result->reviewed_at) · {{ $result->reviewed_at->format('d M Y H:i') }} @endif</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Approved</dt>
                <dd class="mt-1">{{ $result->approvedBy?->name ?? '—' }} @if($result->approved_at) · {{ $result->approved_at->format('d M Y H:i') }} @endif</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Published</dt>
                <dd class="mt-1">{{ $result->publishedBy?->name ?? '—' }} @if($result->published_at) · {{ $result->published_at->format('d M Y H:i') }} @endif</dd>
            </div>
        </dl>

        <div class="mt-5 flex gap-2">
            @if($result->status === 'draft')
                <form method="POST" action="{{ route('results.submit', $result) }}">@csrf<button class="btn-secondary text-sm">Submit for Review</button></form>
            @elseif($result->status === 'submitted')
                @can('review-results')
                <form method="POST" action="{{ route('results.review', $result) }}">@csrf<button class="btn-secondary text-sm">Mark Reviewed</button></form>
                @endcan
            @elseif($result->status === 'reviewed')
                @can('approve-results')
                <form method="POST" action="{{ route('results.approve', $result) }}">@csrf<button class="btn-secondary text-sm">Approve</button></form>
                @endcan
            @elseif($result->status === 'approved')
                @can('publish-results')
                <form method="POST" action="{{ route('results.publish', $result) }}">@csrf<button class="btn-primary text-sm">Publish</button></form>
                @endcan
            @endif
        </div>
    </x-card>
</x-layouts.dashboard>
