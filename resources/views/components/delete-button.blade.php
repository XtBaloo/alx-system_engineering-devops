@props([
    'action',
    'confirm' => 'Are you sure you want to delete this record? This action cannot be undone.',
    'title' => 'Confirm deletion',
])

<span x-data="{ open: false }" class="inline">
    <button type="button" @click="open = true" {{ $attributes->class(['text-red-600 hover:text-red-800 text-sm font-medium']) }}>
        {{ $slot->isEmpty() ? 'Delete' : $slot }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div
            class="fixed inset-0 bg-gray-900/50"
            x-show="open"
            x-transition.opacity
            @click="open = false"
        ></div>

        <div
            class="relative w-full max-w-sm rounded-lg bg-white p-6 shadow-xl"
            x-show="open"
            x-transition
            @click.outside="open = false"
            @keydown.escape.window="open = false"
        >
            <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
            <p class="mt-2 text-sm text-gray-600">{{ $confirm }}</p>
            <div class="mt-5 flex justify-end gap-3">
                <button type="button" @click="open = false" class="btn-secondary">Cancel</button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</span>
