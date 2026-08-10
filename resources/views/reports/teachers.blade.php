<x-layouts.dashboard title="Teacher Report">
    <div class="mb-4 flex justify-end print:hidden">
        <button onclick="window.print()" class="btn-secondary">Print</button>
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Teacher ID</th><th>Name</th><th>Status</th><th>Assignments</th></tr></thead>
            <tbody>
                @forelse($teachers as $t)
                    <tr>
                        <td>{{ $t->teacher_id }}</td>
                        <td>{{ $t->full_name }}</td>
                        <td>{{ ucfirst(str_replace('_',' ',$t->status)) }}</td>
                        <td>{{ $t->teacher_assignments_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state title="No teachers found" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4 print:hidden">{{ $teachers->links() }}</div>
</x-layouts.dashboard>
