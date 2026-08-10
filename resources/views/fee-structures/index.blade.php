<x-layouts.dashboard title="Fee Structures">
    <div class="mb-4 flex justify-end gap-3">
        <a href="{{ route('fee-categories.index') }}" class="btn-secondary">Manage Categories</a>
        <a href="{{ route('fee-structures.create') }}" class="btn-primary">+ New Fee Structure</a>
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Category</th><th>Class</th><th>Session</th><th>Term</th><th>Amount</th><th></th></tr></thead>
            <tbody>
                @forelse($structures as $s)
                    <tr>
                        <td class="font-medium">{{ $s->feeCategory->name }}</td>
                        <td>{{ $s->schoolClass?->name ?? 'All Classes' }}</td>
                        <td>{{ $s->academicSession->name }}</td>
                        <td>{{ $s->term->name }}</td>
                        <td>₦{{ number_format($s->amount, 2) }}</td>
                        <td class="space-x-3 whitespace-nowrap">
                            <form method="POST" action="{{ route('fee-structures.assign', $s) }}" class="inline" onsubmit="return confirm('Assign this fee to all eligible students?')">
                                @csrf
                                <button class="text-emerald-700 hover:underline">Assign to Students</button>
                            </form>
                            <a href="{{ route('fee-structures.edit', $s) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('fee-structures.destroy', $s)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="No fee structures yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $structures->links() }}</div>
</x-layouts.dashboard>
