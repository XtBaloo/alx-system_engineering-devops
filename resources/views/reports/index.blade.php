<x-layouts.dashboard title="Reports">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['route' => 'reports.students', 'title' => 'Student Reports', 'desc' => 'Browse and filter all student records.'],
            ['route' => 'reports.attendance', 'title' => 'Attendance Reports', 'desc' => 'Attendance totals per student.'],
            ['route' => 'reports.academic', 'title' => 'Academic / Result Reports', 'desc' => 'Published results across classes.'],
            ['route' => 'reports.finance', 'title' => 'Financial Reports', 'desc' => 'Fees collected, outstanding, and payments.'],
            ['route' => 'reports.teachers', 'title' => 'Teacher Reports', 'desc' => 'Teacher workload and assignments.'],
        ] as $r)
            <a href="{{ route($r['route']) }}" class="card block p-5 hover:border-emerald-300 hover:shadow-md">
                <h3 class="font-semibold text-gray-900">{{ $r['title'] }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $r['desc'] }}</p>
            </a>
        @endforeach
    </div>
</x-layouts.dashboard>
