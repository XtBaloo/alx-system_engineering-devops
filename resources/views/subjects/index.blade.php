<x-layouts.dashboard title="Subjects">
    <div class="mb-4 flex items-center justify-between gap-3">
        <form method="GET" class="flex-1 max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subjects..." class="form-input">
        </form>
        <a href="{{ route('subjects.create') }}" class="btn-primary">+ New Subject</a>
    </div>
    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Name</th><th>Code</th><th>Category</th><th>Compulsory</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($subjects as $s)
                    <tr>
                        <td class="font-medium">{{ $s->name }}</td>
                        <td>{{ $s->code }}</td>
                        <td>{{ $s->category ?? '—' }}</td>
                        <td>{{ $s->is_compulsory ? 'Yes' : 'No' }}</td>
                        <td><span class="{{ $s->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($s->status) }}</span></td>
                        <td class="space-x-3">
                            <a href="{{ route('subjects.edit', $s) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('subjects.destroy', $s)" class="inline" confirm="Archive this subject?" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="No subjects yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4">{{ $subjects->links() }}</div>
</x-layouts.dashboard>
