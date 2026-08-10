<x-layouts.dashboard title="Edit Fee Structure">
    <x-card>
        <form method="POST" action="{{ route('fee-structures.update', $feeStructure) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            @include('fee-structures._form', ['feeStructure' => $feeStructure])
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('fee-structures.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
