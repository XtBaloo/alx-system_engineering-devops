<x-layouts.dashboard title="Student Report">
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
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-40" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach(['active','graduated','withdrawn','suspended','transferred','archived'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-card>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Admission No.</th><th>Name</th><th>Class</th><th>Gender</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($students as $s)
                    <tr>
                        <td>{{ $s->admission_number }}</td>
                        <td>{{ $s->full_name }}</td>
                        <td>{{ $s->currentClassArm?->full_name }}</td>
                        <td class="capitalize">{{ $s->gender }}</td>
                        <td>{{ ucfirst($s->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No students found" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4 print:hidden">{{ $students->links() }}</div>
</x-layouts.dashboard>
