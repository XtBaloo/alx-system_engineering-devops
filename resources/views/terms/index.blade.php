<x-layouts.dashboard title="Terms">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('terms.create') }}" class="btn-primary">+ New Term</a>
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Session</th><th>Term</th><th>Start</th><th>End</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($terms as $t)
                    <tr>
                        <td>{{ $t->academicSession->name }}</td>
                        <td class="font-medium">{{ $t->name }}</td>
                        <td>{{ $t->start_date->format('d M Y') }}</td>
                        <td>{{ $t->end_date->format('d M Y') }}</td>
                        <td class="space-x-1">
                            @if($t->is_active)<span class="badge-green">Active</span>@endif
                            <span class="{{ $t->status === 'open' ? 'badge-blue' : 'badge-gray' }}">{{ ucfirst($t->status) }}</span>
                        </td>
                        <td class="space-x-3">
                            @unless($t->is_active)
                                <form method="POST" action="{{ route('terms.activate', $t) }}" class="inline">@csrf<button class="text-emerald-700 hover:underline">Activate</button></form>
                            @endunless
                            @if($t->isOpen())
                                <form method="POST" action="{{ route('terms.close', $t) }}" class="inline">@csrf<button class="text-amber-700 hover:underline">Close</button></form>
                                <a href="{{ route('terms.edit', $t) }}" class="text-emerald-700 hover:underline">Edit</a>
                            @else
                                <form method="POST" action="{{ route('terms.reopen', $t) }}" class="inline">@csrf<button class="text-blue-700 hover:underline">Reopen</button></form>
                            @endif
                            <x-delete-button :action="route('terms.destroy', $t)" class="inline" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="No terms yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $terms->links() }}</div>
</x-layouts.dashboard>
