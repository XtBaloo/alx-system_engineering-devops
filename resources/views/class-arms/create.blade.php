<x-layouts.dashboard title="New Class Arm">
    <x-card>
        <form method="POST" action="{{ route('class-arms.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @include('class-arms._form')
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('class-arms.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
