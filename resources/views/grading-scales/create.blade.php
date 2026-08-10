<x-layouts.dashboard title="Add Grade">
    <x-card>
        <form method="POST" action="{{ route('grading-scales.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @include('grading-scales._form')
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('grading-scales.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
