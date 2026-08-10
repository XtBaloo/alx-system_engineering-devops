<x-layouts.dashboard title="Guardians">
    <div class="mb-4 flex items-center justify-between gap-3">
        <form method="GET" class="flex-1 max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search guardians..." class="form-input">
        </form>
        <a href="{{ route('guardians.create') }}" class="btn-primary">+ Add Guardian</a>
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Children</th><th></th></tr></thead>
            <tbody>
                @forelse($guardians as $g)
                    <tr>
                        <td class="font-medium"><a href="{{ route('guardians.show', $g) }}" class="text-emerald-700 hover:underline">{{ $g->full_name }}</a></td>
                        <td>{{ $g->phone ?? '—' }}</td>
                        <td>{{ $g->email ?? '—' }}</td>
                        <td>{{ $g->students_count }}</td>
                        <td class="space-x-3">
                            <a href="{{ route('guardians.edit', $g) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('guardians.destroy', $g)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No guardians yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $guardians->links() }}</div>
</x-layouts.dashboard>
