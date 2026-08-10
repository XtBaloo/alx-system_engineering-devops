<x-layouts.dashboard title="Edit Class Arm">
    <x-card>
        <form method="POST" action="{{ route('class-arms.update', $classArm) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            @include('class-arms._form', ['classArm' => $classArm])
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('class-arms.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
