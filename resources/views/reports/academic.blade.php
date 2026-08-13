<x-layouts.dashboard title="Academic / Result Report" :subtitle="$term?->name">
    <div class="mb-4 flex justify-end print:hidden">
        <button onclick="window.print()" class="btn-secondary">Print</button>
    </div>
    <x-card class="mb-6 print:hidden">
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
            <div>
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select w-56" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" @selected(request('subject_id') == $subj->id)>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-card>

    <x-card title="Grade Distribution" class="mb-6 max-w-2xl print:hidden">
        <x-chart type="bar" :labels="$gradeChart['labels']" :datasets="$gradeChart['datasets']" :height="220" />
    </x-card>

    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Student</th><th>Class</th><th>Subject</th><th>Total</th><th>Grade</th><th>Position</th></tr></thead>
            <tbody>
                @forelse($results as $r)
                    <tr>
                        <td>{{ $r->student?->full_name }}</td>
                        <td>{{ $r->classArm?->full_name }}</td>
                        <td>{{ $r->subject?->name }}</td>
                        <td>{{ $r->total_score }}</td>
                        <td>{{ $r->grade }}</td>
                        <td>{{ $r->position }}@if($r->subject_class_size)/{{ $r->subject_class_size }}@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="No published results found" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4 print:hidden">{{ $results->links() }}</div>
</x-layouts.dashboard>
