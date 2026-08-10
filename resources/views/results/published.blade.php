<x-layouts.dashboard title="Published Results" :subtitle="$term?->name">
    <x-card class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">Class</label>
                <select name="class_arm_id" class="form-select w-56" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach($classArms as $arm)
                        <option value="{{ $arm->id }}" @selected(request('class_arm_id') == $arm->id)>{{ $arm->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-card>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Student</th><th>Class</th><th>Subject</th><th>Total</th><th>Grade</th><th>Position</th><th>Published</th></tr></thead>
            <tbody>
                @forelse($results as $r)
                    <tr>
                        <td>{{ $r->student?->full_name }}</td>
                        <td>{{ $r->classArm?->full_name }}</td>
                        <td>{{ $r->subject?->name }}</td>
                        <td>{{ $r->total_score }}</td>
                        <td><span class="badge-green">{{ $r->grade }}</span></td>
                        <td>{{ $r->position }}@if($r->subject_class_size) / {{ $r->subject_class_size }}@endif</td>
                        <td>{{ $r->published_at?->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state title="No published results yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $results->links() }}</div>
</x-layouts.dashboard>
