<x-layouts.dashboard title="My Timetable">
    @if(! $session)
        <x-empty-state title="No active academic session" description="Your timetable will appear once the school activates an academic session." />
    @elseif($entries->isEmpty())
        <x-empty-state title="No classes scheduled" description="You have no timetable entries yet. Check back once the admin builds the timetable." />
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
                                <div class="text-xs text-gray-500">{{ $entry->classArm?->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $entry->room }}</div>
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
