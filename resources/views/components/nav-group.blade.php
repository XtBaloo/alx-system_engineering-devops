@props(['label', 'active' => false])

<div x-data="{ open: @js($active) }">
    <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-emerald-300 hover:text-white">
        <span>{{ $label }}</span>
        <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </button>
    <div x-show="open" x-transition class="ml-2 space-y-1 border-l border-emerald-800 pl-2">
        {{ $slot }}
    </div>
</div>
