<x-layouts.dashboard title="My Attendance">
    <x-card class="mb-6" title="Summary">
        <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
            <div><dt class="text-gray-500">Present</dt><dd class="text-xl font-bold text-emerald-700">{{ $summary['present'] ?? 0 }}</dd></div>
            <div><dt class="text-gray-500">Absent</dt><dd class="text-xl font-bold text-red-600">{{ $summary['absent'] ?? 0 }}</dd></div>
            <div><dt class="text-gray-500">Late</dt><dd class="text-xl font-bold text-amber-600">{{ $summary['late'] ?? 0 }}</dd></div>
            <div><dt class="text-gray-500">Excused</dt><dd class="text-xl font-bold text-blue-600">{{ $summary['excused'] ?? 0 }}</dd></div>
        </dl>
    </x-card>
    <x-card title="History">
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Date</th><th>Term</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($attendances as $a)
                    <tr>
                        <td>{{ $a->date->format('d M Y') }}</td>
                        <td>{{ $a->term?->name }}</td>
                        <td>
                            @php($colors = ['present'=>'badge-green','absent'=>'badge-red','late'=>'badge-yellow','excused'=>'badge-blue'])
                            <span class="{{ $colors[$a->status] }}">{{ ucfirst($a->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state title="No attendance records yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4">{{ $attendances->links() }}</div>
</x-layouts.dashboard>
