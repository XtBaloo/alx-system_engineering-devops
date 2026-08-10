<x-layouts.dashboard title="My Dashboard" :subtitle="$term?->name">
    @if(!$student)
        <x-empty-state title="No student profile linked to your account" description="Please contact the school administrator." />
    @else
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="My Information" class="lg:col-span-1">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-700">
                    {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ $student->full_name }}</p>
                    <p class="text-sm text-gray-500">{{ $student->admission_number }}</p>
                    <p class="text-sm text-gray-500">{{ $student->currentClassArm?->full_name }}</p>
                </div>
            </div>
        </x-card>

        <x-card title="Attendance Summary">
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-gray-500">Present</dt><dd class="text-lg font-semibold text-emerald-700">{{ $attendanceSummary['present'] ?? 0 }}</dd></div>
                <div><dt class="text-gray-500">Absent</dt><dd class="text-lg font-semibold text-red-600">{{ $attendanceSummary['absent'] ?? 0 }}</dd></div>
                <div><dt class="text-gray-500">Late</dt><dd class="text-lg font-semibold text-amber-600">{{ $attendanceSummary['late'] ?? 0 }}</dd></div>
                <div><dt class="text-gray-500">Excused</dt><dd class="text-lg font-semibold text-blue-600">{{ $attendanceSummary['excused'] ?? 0 }}</dd></div>
            </dl>
            <a href="{{ route('my.attendance') }}" class="mt-4 inline-block text-sm font-medium text-emerald-700 hover:underline">View full history &rarr;</a>
        </x-card>

        <x-card title="Fee Status">
            @php($balance = $feeStatus->sum(fn($f) => $f->balance))
            <p class="text-2xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">₦{{ number_format($balance, 2) }}</p>
            <p class="text-sm text-gray-500">Outstanding balance this term</p>
            <a href="{{ route('my.fees') }}" class="mt-4 inline-block text-sm font-medium text-emerald-700 hover:underline">View fee details &rarr;</a>
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Latest Published Results">
            <table class="table-base">
                <thead><tr><th>Subject</th><th>Total</th><th>Grade</th></tr></thead>
                <tbody>
                    @forelse($latestResults as $r)
                        <tr><td>{{ $r->subject?->name }}</td><td>{{ $r->total_score }}</td><td><span class="badge-green">{{ $r->grade }}</span></td></tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state title="No published results yet" /></td></tr>
                    @endforelse
                </tbody>
            </table>
            <a href="{{ route('my.results') }}" class="mt-4 inline-block text-sm font-medium text-emerald-700 hover:underline">View full results &rarr;</a>
        </x-card>

        <x-card title="Announcements">
            @forelse($announcements as $a)
                <div class="border-b border-gray-50 py-2 text-sm last:border-0">
                    <p class="font-medium text-gray-800">{{ $a->title }}</p>
                    <p class="text-xs text-gray-500">{{ $a->published_at?->diffForHumans() }}</p>
                </div>
            @empty
                <x-empty-state title="No announcements yet" />
            @endforelse
        </x-card>
    </div>
    @endif
</x-layouts.dashboard>
