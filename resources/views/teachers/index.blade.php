<x-layouts.dashboard title="Teachers">
    <div class="mb-4 flex items-center justify-between gap-3">
        <form method="GET" class="flex-1 max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID..." class="form-input">
        </form>
        @can('create', \App\Models\Teacher::class)
        <a href="{{ route('teachers.create') }}" class="btn-primary">+ Add Teacher</a>
        @endcan
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Teacher ID</th><th>Name</th><th>Phone</th><th>Qualification</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($teachers as $t)
                    <tr>
                        <td>{{ $t->teacher_id }}</td>
                        <td class="font-medium"><a href="{{ route('teachers.show', $t) }}" class="text-emerald-700 hover:underline">{{ $t->full_name }}</a></td>
                        <td>{{ $t->phone ?? '—' }}</td>
                        <td>{{ $t->qualification ?? '—' }}</td>
                        <td><span class="{{ $t->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span></td>
                        <td class="space-x-3">
                            <a href="{{ route('teachers.edit', $t) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('teachers.destroy', $t)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="No teachers yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $teachers->links() }}</div>
</x-layouts.dashboard>
