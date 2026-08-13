<x-layouts.dashboard title="New Timetable Entry">
    <x-card>
        <form method="POST" action="{{ route('timetable.store') }}" class="space-y-4">
            @csrf
            @include('timetable._form')
            <div class="flex justify-end gap-3">
                <a href="{{ route('timetable.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
