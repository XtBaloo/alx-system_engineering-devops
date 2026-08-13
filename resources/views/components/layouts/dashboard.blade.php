<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - ' : '' }}{{ \App\Models\SchoolSetting::current()->school_name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900" x-data="{ sidebarOpen: false }">

    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden print:hidden" @click="sidebarOpen = false"></div>

    <div class="flex min-h-screen">
        <aside
            class="fixed inset-y-0 left-0 z-40 w-72 transform overflow-y-auto bg-emerald-950 text-emerald-50 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            @include('partials.sidebar')
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:pl-0">
            <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-gray-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 lg:hidden" aria-label="Toggle navigation menu" :aria-expanded="sidebarOpen.toString()">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Dashboard' }}</h1>
                        @isset($subtitle)<p class="text-xs text-gray-500">{{ $subtitle }}</p>@endisset
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden text-right text-xs text-gray-500 sm:block">
                        @php($session = \App\Models\SchoolSetting::current()->currentAcademicSession)
                        @php($term = \App\Models\SchoolSetting::current()->currentTerm)
                        @if($session)
                            <div class="font-medium text-gray-700">{{ $session->name }}</div>
                            <div>{{ $term?->name }}</div>
                        @endif
                    </div>
                    @include('partials.notifications-bell')

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-700 text-xs font-semibold text-white">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </header>

            <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
                <x-toast />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
