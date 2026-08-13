<x-layouts.dashboard title="Attendance Records">
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
            <div>
                <label class="form-label">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="form-input" onchange="this.form.submit()">
            </div>
        </form>
    </x-card>

    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Date</th><th>Student</th><th>Class</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($records as $r)
                    <tr>
                        <td>{{ $r->date->format('d M Y') }}</td>
                        <td>{{ $r->student?->full_name }}</td>
                        <td>{{ $r->classArm?->full_name }}</td>
                        <td>
                            @php($colors = ['present'=>'badge-green','absent'=>'badge-red','late'=>'badge-yellow','excused'=>'badge-blue'])
                            <span class="{{ $colors[$r->status] }}">{{ ucfirst($r->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state title="No attendance records found" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4">{{ $records->links() }}</div>
</x-layouts.dashboard>
