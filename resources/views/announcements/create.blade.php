<x-layouts.dashboard title="New Announcement">
    <x-card>
        <form method="POST" action="{{ route('announcements.store') }}" class="space-y-4" x-data="{ target: 'everyone' }">
            @csrf
            @include('announcements._form')
            <div class="flex justify-end gap-3">
                <a href="{{ route('announcements.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
