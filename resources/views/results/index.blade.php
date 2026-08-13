<x-layouts.dashboard title="Review Results" :subtitle="$term?->name">
    <x-card class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">Session</label>
                <select name="academic_session_id" class="form-select w-44" onchange="this.form.submit()">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}" @selected(request('academic_session_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Term</label>
                <select name="term_id" class="form-select w-48" onchange="this.form.submit()">
                    <option value="" @selected(! request('term_id'))>Current Term</option>
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" @selected(request('term_id') == $t->id)>{{ $t->academicSession?->name }} — {{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Class</label>
                <select name="class_arm_id" class="form-select w-48" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach($classArms as $arm)
                        <option value="{{ $arm->id }}" @selected(request('class_arm_id') == $arm->id)>{{ $arm->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select w-48" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" @selected(request('subject_id') == $subj->id)>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-40" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach(['draft','submitted','reviewed','approved','published'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Student</label>
                <input type="text" name="student" value="{{ request('student') }}" placeholder="Name or admission no." class="form-input w-48">
            </div>
            <div>
                <button type="submit" class="btn-secondary text-sm">Filter</button>
            </div>
        </form>
    </x-card>

    <x-card>
        <form method="POST" id="batch-form">
            @csrf
            <div class="mb-3 flex gap-2">
                <button type="submit" formaction="{{ route('results.submit-batch') }}" class="btn-secondary text-sm">Submit Selected</button>
                <button type="submit" formaction="{{ route('results.publish-batch') }}" class="btn-primary text-sm">Publish Selected</button>
            </div>
            <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="document.querySelectorAll('.result-check').forEach(c=>c.checked=this.checked)"></th>
                        <th>Student</th><th>Class</th><th>Subject</th><th>Total</th><th>Grade</th><th>Position</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $r)
                        <tr>
                            <td><input type="checkbox" name="result_ids[]" value="{{ $r->id }}" form="batch-form" class="result-check"></td>
                            <td><a href="{{ route('results.show', $r) }}" class="text-emerald-700 hover:underline">{{ $r->student?->full_name }}</a></td>
                            <td>{{ $r->classArm?->full_name }}</td>
                            <td>{{ $r->subject?->name }}</td>
                            <td>{{ $r->total_score }}</td>
                            <td>{{ $r->grade }}</td>
                            <td>{{ $r->position ?? '—' }}@if($r->subject_class_size) / {{ $r->subject_class_size }}@endif</td>
                            <td>
                                @php($colors = ['draft'=>'badge-gray','submitted'=>'badge-blue','reviewed'=>'badge-yellow','approved'=>'badge-yellow','published'=>'badge-green'])
                                <span class="{{ $colors[$r->status] }}">{{ ucfirst($r->status) }}</span>
                            </td>
                            <td class="space-x-2 whitespace-nowrap">
                                @if($r->status === 'draft')
                                    <form method="POST" action="{{ route('results.submit', $r) }}" class="inline">@csrf<button class="text-blue-700 hover:underline text-xs">Submit</button></form>
                                @elseif($r->status === 'submitted')
                                    @can('review-results')
                                    <form method="POST" action="{{ route('results.review', $r) }}" class="inline">@csrf<button class="text-amber-700 hover:underline text-xs">Review</button></form>
                                    @endcan
                                @elseif($r->status === 'reviewed')
                                    @can('approve-results')
                                    <form method="POST" action="{{ route('results.approve', $r) }}" class="inline">@csrf<button class="text-amber-700 hover:underline text-xs">Approve</button></form>
                                    @endcan
                                @elseif($r->status === 'approved')
                                    @can('publish-results')
                                    <form method="POST" action="{{ route('results.publish', $r) }}" class="inline">@csrf<button class="text-emerald-700 hover:underline text-xs">Publish</button></form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state title="No results found" description="Scores entered by teachers will appear here for review." /></td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </form>
    </x-card>
    <div class="mt-4">{{ $results->links() }}</div>
</x-layouts.dashboard>
