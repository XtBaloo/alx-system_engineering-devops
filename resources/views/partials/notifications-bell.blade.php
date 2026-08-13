@php
    $unreadNotificationCount = auth()->user()->unreadNotifications()->count();
    $recentNotifications = auth()->user()->notifications()->latest()->take(8)->get();
@endphp

<x-dropdown align="right" width="w-80">
    <x-slot name="trigger">
        <button class="relative rounded-md p-2 text-gray-500 hover:bg-gray-100" aria-label="Notifications">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @if($unreadNotificationCount > 0)
                <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white">
                    {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                </span>
            @endif
        </button>
    </x-slot>

    <x-slot name="content">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2">
            <span class="text-sm font-semibold text-gray-900">Notifications</span>
            @if($unreadNotificationCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-emerald-700 hover:underline">Mark all read</button>
                </form>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($recentNotifications as $notification)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <button type="submit" class="block w-full px-4 py-3 text-left hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-emerald-50' }}">
                        <div class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</div>
                        <div class="mt-0.5 text-xs text-gray-500">{{ $notification->data['message'] ?? '' }}</div>
                        <div class="mt-1 text-[11px] text-gray-400">{{ $notification->created_at->diffForHumans() }}</div>
                    </button>
                </form>
            @empty
                <div class="px-4 py-8 text-center text-sm text-gray-400">You're all caught up.</div>
            @endforelse
        </div>

        <div class="border-t border-gray-100 px-4 py-2 text-center">
            <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-emerald-700 hover:underline">View all notifications</a>
        </div>
    </x-slot>
</x-dropdown>
