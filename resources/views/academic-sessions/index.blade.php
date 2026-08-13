<x-layouts.dashboard title="Academic Sessions">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('academic-sessions.create') }}" class="btn-primary">+ New Session</a>
    </div>
    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Session</th><th>Start</th><th>End</th><th>Terms</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($sessions as $s)
                    <tr>
                        <td class="font-medium">{{ $s->name }}</td>
                        <td>{{ $s->start_date->format('d M Y') }}</td>
                        <td>{{ $s->end_date->format('d M Y') }}</td>
                        <td>{{ $s->terms_count }}</td>
                        <td>@if($s->is_active)<span class="badge-green">Active</span>@else<span class="badge-gray">Inactive</span>@endif</td>
                        <td class="space-x-3">
                            @unless($s->is_active)
                                <form method="POST" action="{{ route('academic-sessions.activate', $s) }}" class="inline">
                                    @csrf
                                    <button class="text-emerald-700 hover:underline">Activate</button>
                                </form>
                            @endunless
                            <a href="{{ route('academic-sessions.edit', $s) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('academic-sessions.destroy', $s)" class="inline" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="No academic sessions yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4">{{ $sessions->links() }}</div>
</x-layouts.dashboard>
