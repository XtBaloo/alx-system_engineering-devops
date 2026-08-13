<x-layouts.dashboard title="Timetable">
    <x-card class="mb-6">
        <form method="GET" class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <label class="form-label">Class</label>
                <select name="class_arm_id" class="form-select w-56" onchange="this.form.submit()">
                    @forelse($classArms as $arm)
                        <option value="{{ $arm->id }}" @selected($classArmId == $arm->id)>{{ $arm->full_name }}</option>
                    @empty
                        <option value="">No classes yet</option>
                    @endforelse
                </select>
            </div>
            <a href="{{ route('timetable.create') }}" class="btn-primary">+ New Entry</a>
        </form>
    </x-card>

    @if(! $session)
        <x-empty-state title="No active academic session" description="Activate an academic session in Academic &rarr; Sessions before building a timetable." />
    @elseif($classArms->isEmpty())
        <x-empty-state title="No classes yet" description="Add classes and class arms before creating a timetable." />
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-7">
            @foreach($days as $value => $label)
                <div class="flex flex-col">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $label }}</h3>
                    <div class="flex-1 space-y-2">
                        @forelse($entries->get($value, collect()) as $entry)
                            <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                                <div class="text-xs font-medium text-emerald-700">{{ $entry->start_time->format('g:i A') }} &ndash; {{ $entry->end_time->format('g:i A') }}</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $entry->subject?->name }}</div>
                                <div class="text-xs text-gray-500">{{ $entry->teacher?->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $entry->room }}</div>
                                <div class="mt-2 flex gap-3 text-xs">
                                    <a href="{{ route('timetable.edit', $entry) }}" class="text-emerald-700 hover:underline">Edit</a>
                                    <x-delete-button :action="route('timetable.destroy', $entry)" class="inline text-xs" confirm="Remove this timetable entry?" />
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border-2 border-dashed border-gray-200 px-3 py-6 text-center text-xs text-gray-400">
                                No classes
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.dashboard>
