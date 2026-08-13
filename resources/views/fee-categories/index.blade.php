<x-layouts.dashboard title="Fee Categories">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('fee-categories.create') }}" class="btn-primary">+ New Category</a>
    </div>
    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Name</th><th>Code</th><th>Structures</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($categories as $c)
                    <tr>
                        <td class="font-medium">{{ $c->name }}</td>
                        <td>{{ $c->code }}</td>
                        <td>{{ $c->fee_structures_count }}</td>
                        <td><span class="{{ $c->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($c->status) }}</span></td>
                        <td class="space-x-3">
                            <a href="{{ route('fee-categories.edit', $c) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('fee-categories.destroy', $c)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No fee categories yet" description="e.g. Tuition, Registration, Examination, ICT" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4">{{ $categories->links() }}</div>
</x-layouts.dashboard>
