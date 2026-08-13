<x-layouts.dashboard title="Dashboard" :subtitle="$session ? $session->name.' · '.($term?->name ?? '') : 'No active session set'">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Students" :value="$stats['total_students']" color="emerald">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
        </x-stat-card>
        <x-stat-card label="Total Teachers" :value="$stats['total_teachers']" color="blue">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14z" /></svg>
        </x-stat-card>
        <x-stat-card label="Total Classes" :value="$stats['total_classes']" color="purple">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21H3m16 0H5m9-14v.01M14 12v.01M14 16v.01M10 8v.01M10 12v.01M10 16v.01" /></svg>
        </x-stat-card>
        <x-stat-card label="Present / Absent Today" :value="$stats['present_today'].' / '.$stats['absent_today']" color="amber">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </x-stat-card>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat-card label="Fees Collected (Current Term)" :value="'₦'.number_format($stats['fees_collected'], 2)" color="emerald">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </x-stat-card>
        <x-stat-card label="Outstanding Fees (Current Term)" :value="'₦'.number_format($stats['fees_outstanding'], 2)" color="red">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </x-stat-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Attendance Trend (Last 14 Days)" class="lg:col-span-2">
            <x-chart type="line" :labels="$attendanceTrendChart['labels']" :datasets="$attendanceTrendChart['datasets']" />
        </x-card>
        <x-card title="Fees: Collected vs Outstanding">
            <x-chart
                type="doughnut"
                :labels="['Collected', 'Outstanding']"
                :datasets="[['data' => [(float) $stats['fees_collected'], (float) $stats['fees_outstanding']], 'backgroundColor' => ['#059669', '#dc2626']]]"
            />
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Students by Class">
            <x-chart type="bar" :labels="$classDistributionChart['labels']" :datasets="$classDistributionChart['datasets']" />
        </x-card>
        <x-card title="Grade Distribution (Published Results)">
            <x-chart type="bar" :labels="$gradeDistributionChart['labels']" :datasets="$gradeDistributionChart['datasets']" />
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Recent Students" class="lg:col-span-1">
            @forelse($recentStudents as $s)
                <div class="flex items-center justify-between border-b border-gray-50 py-2 text-sm last:border-0">
                    <div>
                        <a href="{{ route('students.show', $s) }}" class="font-medium text-emerald-700 hover:underline">{{ $s->full_name }}</a>
                        <p class="text-xs text-gray-500">{{ $s->admission_number }}</p>
                    </div>
                    <span class="badge-gray">{{ ucfirst($s->status) }}</span>
                </div>
            @empty
                <x-empty-state title="No students yet" />
            @endforelse
        </x-card>

        <x-card title="Recent Payments" class="lg:col-span-1">
            @forelse($recentPayments as $p)
                <div class="flex items-center justify-between border-b border-gray-50 py-2 text-sm last:border-0">
                    <div>
                        <p class="font-medium text-gray-800">{{ $p->studentFee?->student?->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $p->receipt_number }}</p>
                    </div>
                    <span class="font-semibold text-emerald-700">₦{{ number_format($p->amount, 2) }}</span>
                </div>
            @empty
                <x-empty-state title="No payments yet" />
            @endforelse
        </x-card>

        <x-card title="Recent Announcements" class="lg:col-span-1">
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
