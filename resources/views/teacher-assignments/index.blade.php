<x-layouts.dashboard title="Teacher Assignments">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('teacher-assignments.create') }}" class="btn-primary">+ New Assignment</a>
    </div>
    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Teacher</th><th>Class</th><th>Subject</th><th>Session</th><th></th></tr></thead>
            <tbody>
                @forelse($assignments as $a)
                    <tr>
                        <td class="font-medium">{{ $a->teacher->full_name }}</td>
                        <td>{{ $a->classArm->full_name }}</td>
                        <td>{{ $a->subject->name }}</td>
                        <td>{{ $a->academicSession->name }}</td>
                        <td><x-delete-button :action="route('teacher-assignments.destroy', $a)" confirm="Remove this assignment?" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No assignments yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
    <div class="mt-4">{{ $assignments->links() }}</div>
</x-layouts.dashboard>
