@props(['title' => null, 'actions' => null])

<div {{ $attributes->class(['card']) }}>
    @if($title)
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
            @if($actions)<div>{{ $actions }}</div>@endif
        </div>
    @endif
    <div class="p-5">
        {{ $slot }}
    </div>
</div>
