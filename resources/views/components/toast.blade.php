@if(session('success') || session('error') || session('info'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition
        class="mb-4 flex items-start justify-between gap-3 rounded-md border px-4 py-3 text-sm shadow-sm
            {{ session('success') ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : '' }}
            {{ session('error') ? 'border-red-200 bg-red-50 text-red-800' : '' }}
            {{ session('info') ? 'border-blue-200 bg-blue-50 text-blue-800' : '' }}"
    >
        <span>{{ session('success') ?? session('error') ?? session('info') }}</span>
        <button @click="show = false" class="text-current opacity-60 hover:opacity-100">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold">Please fix the following:</p>
        <ul class="mt-1 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
