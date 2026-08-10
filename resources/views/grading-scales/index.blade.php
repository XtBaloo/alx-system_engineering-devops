<x-layouts.dashboard title="Grading Scale">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('grading-scales.create') }}" class="btn-primary">+ Add Grade</a>
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Range</th><th>Grade</th><th>Remark</th><th>Grade Point</th><th></th></tr></thead>
            <tbody>
                @forelse($scales as $s)
                    <tr>
                        <td>{{ $s->min_score }} - {{ $s->max_score }}</td>
                        <td><span class="badge-green">{{ $s->grade }}</span></td>
                        <td>{{ $s->remark }}</td>
                        <td>{{ $s->grade_point }}</td>
                        <td class="space-x-3">
                            <a href="{{ route('grading-scales.edit', $s) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <x-delete-button :action="route('grading-scales.destroy', $s)" class="inline" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No grading scale configured" description="Add grade bands like A, B, C to start grading results." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.dashboard>
