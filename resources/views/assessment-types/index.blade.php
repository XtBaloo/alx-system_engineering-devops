<x-layouts.dashboard title="Assessment Components">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">
            Configured total:
            <span class="font-semibold {{ $totalConfigured == 100 ? 'text-emerald-700' : 'text-red-600' }}">{{ $totalConfigured }} / 100</span>
            (Examination component max score is set in <a href="{{ route('settings.edit') }}" class="text-emerald-700 hover:underline">School Settings</a>)
        </p>
        <a href="{{ route('assessment-types.create') }}" class="btn-primary">+ Add Component</a>
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Order</th><th>Name</th><th>Code</th><th>Max Score</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($types as $t)
                    <tr>
                        <td>{{ $t->order }}</td>
                        <td class="font-medium">{{ $t->name }}</td>
                        <td>{{ $t->code }}</td>
                        <td>{{ $t->max_score }}</td>
                        <td><span class="{{ $t->is_active ? 'badge-green' : 'badge-gray' }}">{{ $t->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="space-x-3">
                            <a href="{{ route('assessment-types.edit', $t) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('assessment-types.destroy', $t)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="No assessment components configured" description="e.g. CA 1 = 10, CA 2 = 10, Assignment = 10, Test = 10" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.dashboard>
