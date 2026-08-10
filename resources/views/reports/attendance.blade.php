<x-layouts.dashboard title="Attendance Report" :subtitle="$term?->name">
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
            <div><label class="form-label">From</label><input type="date" name="from" value="{{ request('from') }}" class="form-input"></div>
            <div><label class="form-label">To</label><input type="date" name="to" value="{{ request('to') }}" class="form-input"></div>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </x-card>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Student</th><th>Present</th><th>Absent</th><th>Late</th><th>Excused</th><th>Total Days</th><th>%</th></tr></thead>
            <tbody>
                @forelse($summary as $row)
                    <tr>
                        <td>{{ $row->first_name }} {{ $row->last_name }} ({{ $row->admission_number }})</td>
                        <td>{{ $row->present }}</td>
                        <td>{{ $row->absent }}</td>
                        <td>{{ $row->late }}</td>
                        <td>{{ $row->excused }}</td>
                        <td>{{ $row->total }}</td>
                        <td>{{ $row->total ? round(($row->present / $row->total) * 100) : 0 }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state title="No attendance data found" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4 print:hidden">{{ $summary->links() }}</div>
</x-layouts.dashboard>
