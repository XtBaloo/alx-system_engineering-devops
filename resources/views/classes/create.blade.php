<x-layouts.dashboard title="New Class">
    <x-card>
        <form method="POST" action="{{ route('classes.store') }}" class="space-y-4">
            @csrf
            @include('classes._form')
            <div class="flex justify-end gap-3">
                <a href="{{ route('classes.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
