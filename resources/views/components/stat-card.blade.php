@props(['label', 'value', 'color' => 'emerald'])

@php
$colors = [
    'emerald' => 'bg-emerald-100 text-emerald-700',
    'blue' => 'bg-blue-100 text-blue-700',
    'amber' => 'bg-amber-100 text-amber-700',
    'red' => 'bg-red-100 text-red-700',
    'purple' => 'bg-purple-100 text-purple-700',
    'gray' => 'bg-gray-100 text-gray-700',
];
$colorClass = $colors[$color] ?? $colors['emerald'];
@endphp

<div class="stat-card">
    <div>
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $value }}</p>
    </div>
    <div class="flex h-11 w-11 items-center justify-center rounded-full {{ $colorClass }}">
        {{ $slot }}
    </div>
</div>
