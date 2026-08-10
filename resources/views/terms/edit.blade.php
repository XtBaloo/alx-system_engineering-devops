<x-layouts.dashboard title="Edit Term">
    <x-card>
        <form method="POST" action="{{ route('terms.update', $term) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            @include('terms._form', ['term' => $term])
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('terms.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
