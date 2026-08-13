<x-layouts.dashboard title="Notifications">
    <div class="mb-4 flex justify-end">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn-secondary">Mark all as read</button>
        </form>
    </div>

    <x-card>
        <div class="divide-y divide-gray-100">
            @forelse($notifications as $notification)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <button type="submit" class="flex w-full items-start justify-between gap-4 px-1 py-4 text-left hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-emerald-50/60' }}">
                        <div>
                            <div class="flex items-center gap-2">
                                @unless($notification->read_at)
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-600"></span>
                                @endunless
                                <span class="text-sm font-semibold text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">{{ $notification->data['message'] ?? '' }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                    </button>
                </form>
            @empty
                <x-empty-state title="No notifications yet" description="You'll see updates about results, payments, and announcements here." />
            @endforelse
        </div>
    </x-card>
    <div class="mt-4">{{ $notifications->links() }}</div>
</x-layouts.dashboard>
