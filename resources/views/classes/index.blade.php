<x-layouts.dashboard title="Classes">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('classes.create') }}" class="btn-primary">+ New Class</a>
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Name</th><th>Level</th><th>Arms</th><th>Order</th><th></th></tr></thead>
            <tbody>
                @forelse($classes as $c)
                    <tr>
                        <td class="font-medium">{{ $c->name }}</td>
                        <td class="capitalize">{{ str_replace('_', ' ', $c->level) }}</td>
                        <td>{{ $c->class_arms_count }}</td>
                        <td>{{ $c->order }}</td>
                        <td class="space-x-3">
                            <a href="{{ route('classes.edit', $c) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('classes.destroy', $c)" class="inline" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No classes yet" description="Add classes like JSS 1, SS 2, etc." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $classes->links() }}</div>
</x-layouts.dashboard>
