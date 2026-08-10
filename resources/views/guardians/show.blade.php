<x-layouts.dashboard :title="$guardian->full_name">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('guardians.edit', $guardian) }}" class="btn-secondary">Edit</a>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Contact Information" class="lg:col-span-1">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $guardian->phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $guardian->email ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Occupation</dt><dd>{{ $guardian->occupation ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd class="text-right">{{ $guardian->address ?? '—' }}</dd></div>
            </dl>
        </x-card>
        <x-card title="Children" class="lg:col-span-2">
            <table class="table-base">
                <thead><tr><th>Name</th><th>Admission No.</th><th>Class</th><th>Relationship</th></tr></thead>
                <tbody>
                    @forelse($guardian->students as $s)
                        <tr>
                            <td><a href="{{ route('students.show', $s) }}" class="text-emerald-700 hover:underline">{{ $s->full_name }}</a></td>
                            <td>{{ $s->admission_number }}</td>
                            <td>{{ $s->currentClassArm?->full_name }}</td>
                            <td>{{ $s->pivot->relationship }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state title="No children linked" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
</x-layouts.dashboard>
