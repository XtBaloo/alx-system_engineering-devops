<x-layouts.dashboard title="Announcements">
    @can('create', \App\Models\Announcement::class)
    <div class="mb-4 flex justify-end">
        <a href="{{ route('announcements.create') }}" class="btn-primary">+ New Announcement</a>
    </div>
    @endcan

    <div class="space-y-4">
        @forelse($announcements as $a)
            <x-card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $a->title }}</h3>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $a->message }}</p>
                        <p class="mt-2 text-xs text-gray-500">
                            By {{ $a->author?->name }} &middot; {{ $a->published_at?->diffForHumans() }} &middot;
                            <span class="badge-gray">{{ $a->target === 'class' ? 'Class: '.$a->schoolClass?->name : ucfirst($a->target) }}</span>
                        </p>
                    </div>
                    @can('update', $a)
                    <div class="flex shrink-0 gap-3 text-sm">
                        <a href="{{ route('announcements.edit', $a) }}" class="text-emerald-700 hover:underline">Edit</a>
                        <x-delete-button :action="route('announcements.destroy', $a)" />
                    </div>
                    @endcan
                </div>
            </x-card>
        @empty
            <x-empty-state title="No announcements yet" />
        @endforelse
    </div>
    <div class="mt-4">{{ $announcements->links() }}</div>
</x-layouts.dashboard>
