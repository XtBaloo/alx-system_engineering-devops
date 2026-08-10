<x-layouts.dashboard title="Edit Academic Session">
    <x-card>
        <form method="POST" action="{{ route('academic-sessions.update', $academicSession) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @csrf
            @method('PUT')
            @include('academic-sessions._form', ['academicSession' => $academicSession])
            <div class="sm:col-span-3 flex justify-end gap-3">
                <a href="{{ route('academic-sessions.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
