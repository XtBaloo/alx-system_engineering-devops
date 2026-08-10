<x-layouts.dashboard title="Edit Subject">
    <x-card>
        <form method="POST" action="{{ route('subjects.update', $subject) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            @include('subjects._form', ['subject' => $subject])
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('subjects.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
