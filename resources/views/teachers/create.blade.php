<x-layouts.dashboard title="Add Teacher">
    <x-card>
        <form method="POST" action="{{ route('teachers.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @include('teachers._form')
            <div class="flex justify-end gap-3">
                <a href="{{ route('teachers.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Teacher</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
