<x-layouts.dashboard title="Audit Logs">
    <x-card class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">Entity</label>
                <select name="entity_type" class="form-select w-64" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach($entityTypes as $type)
                        <option value="{{ class_basename($type) }}" @selected(request('entity_type') === class_basename($type))>{{ class_basename($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Action</label>
                <select name="action" class="form-select w-40" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach(['created','updated','deleted'] as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-card>
    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td><span class="badge-gray">{{ ucfirst($log->action) }}</span></td>
                        <td>{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No audit records yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4">{{ $logs->links() }}</div>
</x-layouts.dashboard>
