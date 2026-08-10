@props(['href', 'active' => false, 'icon' => null])

<a href="{{ $href }}"
   @class([
        'flex items-center gap-2 rounded-md px-3 py-2 transition',
        'bg-emerald-800 text-white font-medium' => $active,
        'text-emerald-100 hover:bg-emerald-900/60' => ! $active,
   ])
>
    {{ $slot }}
</a>
