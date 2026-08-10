<x-layouts.dashboard title="Class Arms">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('class-arms.create') }}" class="btn-primary">+ New Class Arm</a>
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Class Arm</th><th>Class Teacher</th><th>Students</th><th>Capacity</th><th></th></tr></thead>
            <tbody>
                @forelse($classArms as $arm)
                    <tr>
                        <td class="font-medium">{{ $arm->schoolClass->name }} {{ $arm->name }}</td>
                        <td>{{ $arm->classTeacher?->full_name ?? '—' }}</td>
                        <td>{{ $arm->students_count }}</td>
                        <td>{{ $arm->capacity ?? '—' }}</td>
                        <td class="space-x-3">
                            <a href="{{ route('class-arms.edit', $arm) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('class-arms.destroy', $arm)" class="inline" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No class arms yet" description="e.g. JSS 1A, JSS 1B" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $classArms->links() }}</div>
</x-layouts.dashboard>
