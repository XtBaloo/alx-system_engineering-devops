<x-layouts.dashboard title="Edit Timetable Entry">
    <x-card>
        <form method="POST" action="{{ route('timetable.update', $timetable) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('timetable._form')
            <div class="flex justify-end gap-3">
                <a href="{{ route('timetable.index', ['class_arm_id' => $timetable->class_arm_id]) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
