<x-layouts.dashboard :title="$teacher->full_name">
    <div class="mb-4 flex justify-end gap-3">
        @can('update', $teacher)
        <a href="{{ route('teachers.edit', $teacher) }}" class="btn-secondary">Edit</a>
        @endcan
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-1">
            <div class="flex flex-col items-center text-center">
                @if($teacher->photo_path)
                    <img src="{{ Storage::disk('public')->url($teacher->photo_path) }}" alt="{{ $teacher->full_name }}" class="h-24 w-24 rounded-full object-cover">
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-emerald-100 text-2xl font-bold text-emerald-700">
                        {{ strtoupper(substr($teacher->first_name,0,1)) }}{{ strtoupper(substr($teacher->last_name,0,1)) }}
                    </div>
                @endif
                <h2 class="mt-3 text-lg font-semibold">{{ $teacher->full_name }}</h2>
                <p class="text-sm text-gray-500">{{ $teacher->teacher_id }}</p>
                <span class="mt-2 {{ $teacher->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst(str_replace('_',' ',$teacher->status)) }}</span>
            </div>
            <dl class="mt-6 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Gender</dt><dd class="capitalize">{{ $teacher->gender }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $teacher->phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $teacher->email ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Qualification</dt><dd>{{ $teacher->qualification ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Employed</dt><dd>{{ $teacher->employment_date?->format('d M Y') ?? '—' }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Class & Subject Assignments" class="lg:col-span-2">
            <div class="overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Class</th><th>Subject</th><th>Session</th></tr></thead>
                <tbody>
                    @forelse($teacher->teacherAssignments as $a)
                        <tr><td>{{ $a->classArm?->full_name }}</td><td>{{ $a->subject?->name }}</td><td>{{ $a->academicSession?->name }}</td></tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state title="No assignments yet" /></td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </x-card>
    </div>
</x-layouts.dashboard>
