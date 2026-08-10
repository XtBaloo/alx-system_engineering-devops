<x-layouts.dashboard title="Students">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, admission no, phone..." class="form-input w-64">
            <select name="class_arm_id" class="form-select w-48" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($classArms as $arm)
                    <option value="{{ $arm->id }}" @selected(request('class_arm_id') == $arm->id)>{{ $arm->full_name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select w-40" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach(['active','graduated','withdrawn','suspended','transferred','archived'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
        @can('create', \App\Models\Student::class)
        <a href="{{ route('students.create') }}" class="btn-primary">+ Add Student</a>
        @endcan
    </div>

    <x-card>
        <table class="table-base">
            <thead><tr><th>Admission No.</th><th>Name</th><th>Class</th><th>Gender</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($students as $s)
                    <tr>
                        <td>{{ $s->admission_number }}</td>
                        <td class="font-medium"><a href="{{ route('students.show', $s) }}" class="text-emerald-700 hover:underline">{{ $s->full_name }}</a></td>
                        <td>{{ $s->currentClassArm?->full_name ?? '—' }}</td>
                        <td class="capitalize">{{ $s->gender }}</td>
                        <td><span class="{{ $s->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($s->status) }}</span></td>
                        <td class="space-x-3">
                            <a href="{{ route('students.edit', $s) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('students.destroy', $s)" confirm="Archive this student record?" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="No students found" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $students->links() }}</div>
</x-layouts.dashboard>
