<x-layouts.dashboard title="Edit Grade">
    <x-card>
        <form method="POST" action="{{ route('grading-scales.update', $gradingScale) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            @include('grading-scales._form', ['gradingScale' => $gradingScale])
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('grading-scales.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
