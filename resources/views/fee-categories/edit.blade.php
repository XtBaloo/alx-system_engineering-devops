<x-layouts.dashboard title="Edit Fee Category">
    <x-card>
        <form method="POST" action="{{ route('fee-categories.update', $feeCategory) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            @include('fee-categories._form', ['feeCategory' => $feeCategory])
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('fee-categories.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
