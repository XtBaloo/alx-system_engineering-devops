<x-layouts.dashboard title="Edit Guardian">
    <x-card>
        <form method="POST" action="{{ route('guardians.update', $guardian) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('guardians._form', ['guardian' => $guardian])
            <div class="flex justify-end gap-3">
                <a href="{{ route('guardians.show', $guardian) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
