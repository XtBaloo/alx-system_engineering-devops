<x-layouts.dashboard title="My Dashboard" :subtitle="$session?->name">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Assigned Classes" :value="$classArms->count()" color="emerald">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" /></svg>
        </x-stat-card>
        <x-stat-card label="Assigned Subjects" :value="$subjects->count()" color="blue">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
        </x-stat-card>
        <x-stat-card label="Attendance Marked Today" :value="$todayAttendance" color="amber">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </x-stat-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="My Class / Subject Assignments">
            <div class="overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Class</th><th>Subject</th></tr></thead>
                <tbody>
                    @forelse($assignments as $a)
                        <tr>
                            <td>{{ $a->classArm?->full_name }}</td>
                            <td>{{ $a->subject?->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2"><x-empty-state title="No assignments yet" description="Ask an administrator to assign you to classes and subjects." /></td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="mt-4 flex gap-3">
                <a href="{{ route('attendance.create') }}" class="btn-primary">Take Attendance</a>
                <a href="{{ route('scores.create') }}" class="btn-secondary">Enter Scores</a>
            </div>
        </x-card>

        <x-card title="Recent Announcements">
            @forelse($recentAnnouncements as $a)
                <div class="border-b border-gray-50 py-2 text-sm last:border-0">
                    <p class="font-medium text-gray-800">{{ $a->title }}</p>
                    <p class="text-xs text-gray-500">{{ $a->published_at?->diffForHumans() }}</p>
                </div>
            @empty
                <x-empty-state title="No announcements yet" />
            @endforelse
        </x-card>
    </div>
</x-layouts.dashboard>
