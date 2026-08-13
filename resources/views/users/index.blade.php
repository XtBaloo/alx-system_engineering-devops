<x-layouts.dashboard title="Users">
    <div class="mb-4 flex items-center justify-between gap-3">
        <form method="GET" class="flex-1 max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="form-input">
        </form>
        <a href="{{ route('users.create') }}" class="btn-primary">+ Add User</a>
    </div>
    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td class="font-medium">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge-blue">{{ $u->roles->pluck('name')->map(fn($r) => ucfirst(str_replace('-',' ',$r)))->join(', ') ?: '—' }}</span></td>
                        <td><span class="{{ $u->is_active ? 'badge-green' : 'badge-gray' }}">{{ $u->is_active ? 'Active' : 'Disabled' }}</span></td>
                        <td class="space-x-3">
                            <a href="{{ route('users.edit', $u) }}" class="text-emerald-700 hover:underline">Edit</a>
                            @if($u->id !== auth()->id())
                            <x-delete-button :action="route('users.destroy', $u)" />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No users found" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.dashboard>
