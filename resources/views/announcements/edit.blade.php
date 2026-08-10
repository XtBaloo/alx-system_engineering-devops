<x-layouts.dashboard title="Edit Announcement">
    <x-card>
        <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-4" x-data="{ target: '{{ old('target', $announcement->target) }}' }">
            @csrf
            @method('PUT')
            @include('announcements._form', ['announcement' => $announcement])
            <div class="flex justify-end gap-3">
                <a href="{{ route('announcements.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
