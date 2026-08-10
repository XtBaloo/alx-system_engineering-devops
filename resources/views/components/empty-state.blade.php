@props(['title' => 'No records found', 'description' => null])

<div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 px-6 py-14 text-center">
    <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h3.28a1 1 0 00.948-.684l.5-1.5A1 1 0 0110.72 2h2.56a1 1 0 01.948.684l.5 1.5a1 1 0 00.948.684H19a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
    <p class="mt-3 font-medium text-gray-700">{{ $title }}</p>
    @if($description)<p class="mt-1 text-sm text-gray-500">{{ $description }}</p>@endif
    @isset($action)<div class="mt-4">{{ $action }}</div>@endisset
</div>
