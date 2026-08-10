@props(['action', 'confirm' => 'Are you sure you want to delete this record?'])

<form method="POST" action="{{ $action }}" onsubmit="return confirm('{{ $confirm }}')">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->class(['text-red-600 hover:text-red-800 text-sm font-medium']) }}>
        {{ $slot->isEmpty() ? 'Delete' : $slot }}
    </button>
</form>
