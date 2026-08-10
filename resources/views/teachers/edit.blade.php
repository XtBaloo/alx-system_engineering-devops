<x-layouts.dashboard title="Edit Teacher">
    <x-card>
        <form method="POST" action="{{ route('teachers.update', $teacher) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            @include('teachers._form', ['teacher' => $teacher])
            <div class="flex justify-end gap-3">
                <a href="{{ route('teachers.show', $teacher) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update Teacher</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
