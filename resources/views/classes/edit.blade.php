<x-layouts.dashboard title="Edit Class">
    <x-card>
        <form method="POST" action="{{ route('classes.update', $schoolClass) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('classes._form', ['schoolClass' => $schoolClass])
            <div class="flex justify-end gap-3">
                <a href="{{ route('classes.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>

    @if($schoolClass->classArms->isNotEmpty())
    <x-card title="Class Arms" class="mt-6">
        <ul class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach($schoolClass->classArms as $arm)
                <li class="rounded-md border border-gray-200 px-3 py-2 text-sm">{{ $schoolClass->name }} {{ $arm->name }}</li>
            @endforeach
        </ul>
    </x-card>
    @endif
</x-layouts.dashboard>
