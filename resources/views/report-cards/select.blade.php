<x-layouts.dashboard title="Report Cards">
    <x-card class="mb-6">
        <form method="GET">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student by name or admission number..." class="form-input">
        </form>
    </x-card>
    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Admission No.</th><th>Name</th><th>Class</th><th></th></tr></thead>
            <tbody>
                @forelse($students as $s)
                    <tr>
                        <td>{{ $s->admission_number }}</td>
                        <td class="font-medium">{{ $s->full_name }}</td>
                        <td>{{ $s->currentClassArm?->full_name }}</td>
                        <td class="space-x-3">
                            <a href="{{ route('report-cards.show', $s) }}" class="text-emerald-700 hover:underline">View</a>
                            <a href="{{ route('report-cards.pdf', $s) }}" class="text-emerald-700 hover:underline">Download PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state title="No students found" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4">{{ $students->links() }}</div>
</x-layouts.dashboard>
