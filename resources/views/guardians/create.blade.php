<x-layouts.dashboard title="Add Guardian">
    <x-card>
        <form method="POST" action="{{ route('guardians.store') }}" class="space-y-4">
            @csrf
            @include('guardians._form')
            <div class="flex justify-end gap-3">
                <a href="{{ route('guardians.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
