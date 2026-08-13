@php($settings = \App\Models\SchoolSetting::current())

<div class="flex h-full flex-col">
    <div class="flex items-center gap-3 border-b border-emerald-900 px-5 py-5">
        @if($settings->logo_path)
            <img src="{{ Storage::disk('public')->url($settings->logo_path) }}" alt="Logo" class="h-10 w-10 rounded-full object-cover bg-white">
        @else
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-700 font-bold">{{ collect(explode(' ', $settings->school_name))->map(fn ($word) => mb_substr($word, 0, 1))->take(3)->implode('') }}</div>
        @endif
        <div>
            <div class="text-sm font-bold leading-tight">{{ $settings->school_name }}</div>
            <div class="text-[11px] text-emerald-300">{{ $settings->motto }}</div>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
        <x-nav-item :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">Dashboard</x-nav-item>

        @canany(['manage-academic-structure'])
        <x-nav-group label="Academic" :active="request()->routeIs('academic-sessions.*','terms.*','classes.*','class-arms.*','subjects.*','teacher-assignments.*','timetable.index','timetable.create','timetable.edit')">
            <x-nav-item :href="route('academic-sessions.index')" :active="request()->routeIs('academic-sessions.*')">Sessions</x-nav-item>
            <x-nav-item :href="route('terms.index')" :active="request()->routeIs('terms.*')">Terms</x-nav-item>
            <x-nav-item :href="route('classes.index')" :active="request()->routeIs('classes.*')">Classes</x-nav-item>
            <x-nav-item :href="route('subjects.index')" :active="request()->routeIs('subjects.*')">Subjects</x-nav-item>
            <x-nav-item :href="route('teacher-assignments.index')" :active="request()->routeIs('teacher-assignments.*')">Teacher Assignments</x-nav-item>
            <x-nav-item :href="route('timetable.index')" :active="request()->routeIs('timetable.index','timetable.create','timetable.edit')">Timetable</x-nav-item>
        </x-nav-group>
        @endcanany

        @canany(['manage-students'])
        <x-nav-group label="Students" :active="request()->routeIs('students.*','guardians.*')">
            <x-nav-item :href="route('students.index')" :active="request()->routeIs('students.index','students.show','students.edit')">All Students</x-nav-item>
            <x-nav-item :href="route('students.create')" :active="request()->routeIs('students.create')">Add Student</x-nav-item>
            <x-nav-item :href="route('students.promotions')" :active="request()->routeIs('students.promot*')">Promotions</x-nav-item>
            <x-nav-item :href="route('guardians.index')" :active="request()->routeIs('guardians.*')">Guardians</x-nav-item>
        </x-nav-group>
        @endcanany

        @canany(['manage-teachers'])
        <x-nav-group label="Teachers" :active="request()->routeIs('teachers.*')">
            <x-nav-item :href="route('teachers.index')" :active="request()->routeIs('teachers.index','teachers.show','teachers.edit')">All Teachers</x-nav-item>
            <x-nav-item :href="route('teachers.create')" :active="request()->routeIs('teachers.create')">Add Teacher</x-nav-item>
        </x-nav-group>
        @endcanany

        @canany(['take-attendance', 'view-attendance'])
        <x-nav-group label="Attendance" :active="request()->routeIs('attendance.*')">
            @can('take-attendance')
            <x-nav-item :href="route('attendance.create')" :active="request()->routeIs('attendance.create')">Take Attendance</x-nav-item>
            @endcan
            <x-nav-item :href="route('attendance.index')" :active="request()->routeIs('attendance.index')">Attendance Records</x-nav-item>
            <x-nav-item :href="route('reports.attendance')" :active="request()->routeIs('reports.attendance')">Attendance Reports</x-nav-item>
        </x-nav-group>
        @endcanany

        @canany(['enter-scores', 'review-results', 'approve-results', 'publish-results', 'manage-assessment-config'])
        <x-nav-group label="Results" :active="request()->routeIs('assessment-types.*','scores.*','results.*','report-cards.*')">
            @can('manage-assessment-config')
            <x-nav-item :href="route('assessment-types.index')" :active="request()->routeIs('assessment-types.*')">Assessments</x-nav-item>
            @endcan
            @can('enter-scores')
            <x-nav-item :href="route('scores.create')" :active="request()->routeIs('scores.*')">Enter Scores</x-nav-item>
            @endcan
            <x-nav-item :href="route('results.index')" :active="request()->routeIs('results.index')">Review Results</x-nav-item>
            <x-nav-item :href="route('results.published')" :active="request()->routeIs('results.published')">Published Results</x-nav-item>
            <x-nav-item :href="route('report-cards.select')" :active="request()->routeIs('report-cards.*')">Report Cards</x-nav-item>
        </x-nav-group>
        @endcanany

        @canany(['manage-fees', 'manage-payments'])
        <x-nav-group label="Finance" :active="request()->routeIs('fee-categories.*','fee-structures.*','student-fees.*','payments.*')">
            <x-nav-item :href="route('fee-structures.index')" :active="request()->routeIs('fee-structures.*','fee-categories.*')">Fee Structures</x-nav-item>
            <x-nav-item :href="route('payments.index')" :active="request()->routeIs('payments.*')">Payments</x-nav-item>
            <x-nav-item :href="route('student-fees.outstanding')" :active="request()->routeIs('student-fees.outstanding')">Outstanding Fees</x-nav-item>
            <x-nav-item :href="route('reports.finance')" :active="request()->routeIs('reports.finance')">Financial Reports</x-nav-item>
        </x-nav-group>
        @endcanany

        <x-nav-group label="Communication" :active="request()->routeIs('announcements.*')">
            <x-nav-item :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">Announcements</x-nav-item>
        </x-nav-group>

        @can('view-reports')
        <x-nav-item :href="route('reports.index')" :active="request()->routeIs('reports.index')" icon="chart">Reports</x-nav-item>
        @endcan

        @can('view-timetable')
        <x-nav-item :href="route('timetable.mine')" :active="request()->routeIs('timetable.mine')" icon="calendar">My Timetable</x-nav-item>
        @endcan

        @if(auth()->user()->hasAnyRole(['student', 'parent']))
        <x-nav-group label="My School" :active="request()->routeIs('my.*')">
            <x-nav-item :href="route('my.results')" :active="request()->routeIs('my.results')">My Results</x-nav-item>
            <x-nav-item :href="route('my.attendance')" :active="request()->routeIs('my.attendance')">My Attendance</x-nav-item>
            <x-nav-item :href="route('my.fees')" :active="request()->routeIs('my.fees')">My Fees</x-nav-item>
        </x-nav-group>
        @endif

        @canany(['manage-settings', 'manage-users', 'view-audit-logs'])
        <x-nav-group label="Settings" :active="request()->routeIs('settings.*','users.*','audit-logs.*','grading-scales.*')">
            @can('manage-settings')
            <x-nav-item :href="route('settings.edit')" :active="request()->routeIs('settings.edit')">School Information</x-nav-item>
            <x-nav-item :href="route('grading-scales.index')" :active="request()->routeIs('grading-scales.*')">Grading</x-nav-item>
            @endcan
            @can('manage-users')
            <x-nav-item :href="route('users.index')" :active="request()->routeIs('users.*')">Users</x-nav-item>
            @endcan
            @can('view-audit-logs')
            <x-nav-item :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')">Audit Logs</x-nav-item>
            @endcan
        </x-nav-group>
        @endcanany
    </nav>

    <div class="border-t border-emerald-900 px-4 py-3 text-[11px] text-emerald-400">
        <div>&copy; {{ date('Y') }} {{ $settings->school_name }}</div>
        <div class="mt-0.5 text-emerald-600">Powered by SchoolHub</div>
    </div>
</div>
