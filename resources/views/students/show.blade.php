<x-layouts.dashboard :title="$student->full_name">
    <div x-data="{ tab: 'overview' }">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                @if($student->photo_path)
                    <img src="{{ Storage::disk('public')->url($student->photo_path) }}" class="h-16 w-16 rounded-full object-cover">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-700">
                        {{ strtoupper(substr($student->first_name,0,1)) }}{{ strtoupper(substr($student->last_name,0,1)) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $student->full_name }}</h2>
                    <p class="text-sm text-gray-500">{{ $student->admission_number }} &middot; {{ $student->currentClassArm?->full_name ?? 'Unassigned' }}</p>
                </div>
                <span class="{{ $student->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($student->status) }}</span>
            </div>
            @can('update', $student)
            <a href="{{ route('students.edit', $student) }}" class="btn-secondary">Edit Student</a>
            @endcan
        </div>

        <div class="mb-4 flex gap-1 overflow-x-auto border-b border-gray-200">
            @foreach(['overview' => 'Overview', 'academic' => 'Academic', 'attendance' => 'Attendance', 'results' => 'Results', 'fees' => 'Fees', 'documents' => 'Documents'] as $key => $label)
                <button @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'border-emerald-700 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium">{{ $label }}</button>
            @endforeach
        </div>

        <div x-show="tab === 'overview'">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-card title="Personal Information">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Gender</dt><dd class="capitalize">{{ $student->gender }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Date of Birth</dt><dd>{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Nationality</dt><dd>{{ $student->nationality ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">State of Origin</dt><dd>{{ $student->state_of_origin ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">LGA</dt><dd>{{ $student->lga ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd class="text-right">{{ $student->address ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $student->phone ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $student->email ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Admission Date</dt><dd>{{ $student->admission_date?->format('d M Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Previous School</dt><dd>{{ $student->previous_school ?? '—' }}</dd></div>
                    </dl>
                </x-card>

                <x-card title="Parents / Guardians">
                    <div class="space-y-3">
                        @forelse($student->guardians as $g)
                            <div class="flex items-center justify-between rounded-md border border-gray-100 p-3 text-sm">
                                <div>
                                    <p class="font-medium">{{ $g->full_name }} <span class="text-xs text-gray-500">({{ $g->pivot->relationship }}{{ $g->pivot->is_primary ? ', primary' : '' }})</span></p>
                                    <p class="text-gray-500">{{ $g->phone }} &middot; {{ $g->email }}</p>
                                </div>
                                @can('update', $student)
                                <x-delete-button :action="route('students.guardians.detach', [$student, $g])" confirm="Unlink this guardian?">Unlink</x-delete-button>
                                @endcan
                            </div>
                        @empty
                            <x-empty-state title="No guardians linked" />
                        @endforelse
                    </div>

                    @can('update', $student)
                    <form method="POST" action="{{ route('students.guardians.attach', $student) }}" class="mt-4 grid grid-cols-1 gap-2 border-t border-gray-100 pt-4 sm:grid-cols-3">
                        @csrf
                        <select name="guardian_id" class="form-select sm:col-span-1">
                            <option value="">-- Select existing --</option>
                            @foreach($allGuardians as $g)
                                <option value="{{ $g->id }}">{{ $g->full_name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="relationship" placeholder="Relationship" class="form-input sm:col-span-1" required>
                        <button type="submit" class="btn-secondary sm:col-span-1">Link Guardian</button>
                    </form>
                    @endcan
                </x-card>
            </div>
        </div>

        <div x-show="tab === 'academic'" x-cloak>
            <x-card title="Enrollment History">
                <table class="table-base">
                    <thead><tr><th>Session</th><th>Class</th><th>Status</th><th>Promoted</th></tr></thead>
                    <tbody>
                        @forelse($student->enrollments as $e)
                            <tr>
                                <td>{{ $e->academicSession?->name }}</td>
                                <td>{{ $e->classArm?->full_name }}</td>
                                <td><span class="badge-gray">{{ ucfirst($e->status) }}</span></td>
                                <td>
                                    @if($e->promotedFrom)
                                        from {{ $e->promotedFrom->classArm?->full_name }}
                                        @can('promote', $student)
                                        <form method="POST" action="{{ route('students.reverse-promotion', [$student, $e]) }}" class="inline" onsubmit="return confirm('Reverse this promotion?')">
                                            @csrf
                                            <button class="ml-2 text-red-600 hover:underline">Reverse</button>
                                        </form>
                                        @endcan
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state title="No enrollment history" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>
        </div>

        <div x-show="tab === 'attendance'" x-cloak>
            <x-card title="Attendance Summary">
                <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                    <div><dt class="text-gray-500">Present</dt><dd class="text-xl font-bold text-emerald-700">{{ $attendanceSummary['present'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Absent</dt><dd class="text-xl font-bold text-red-600">{{ $attendanceSummary['absent'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Late</dt><dd class="text-xl font-bold text-amber-600">{{ $attendanceSummary['late'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Excused</dt><dd class="text-xl font-bold text-blue-600">{{ $attendanceSummary['excused'] ?? 0 }}</dd></div>
                </dl>
            </x-card>
        </div>

        <div x-show="tab === 'results'" x-cloak>
            <x-card title="Published Results">
                <table class="table-base">
                    <thead><tr><th>Term</th><th>Subject</th><th>Total</th><th>Grade</th><th>Position</th></tr></thead>
                    <tbody>
                        @forelse($results as $r)
                            <tr>
                                <td>{{ $r->term?->name }}</td>
                                <td>{{ $r->subject?->name }}</td>
                                <td>{{ $r->total_score }}</td>
                                <td><span class="badge-green">{{ $r->grade }}</span></td>
                                <td>{{ $r->position ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty-state title="No published results yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
                <a href="{{ route('report-cards.show', $student) }}" class="mt-4 inline-block text-sm font-medium text-emerald-700 hover:underline">View full report card &rarr;</a>
            </x-card>
        </div>

        <div x-show="tab === 'fees'" x-cloak>
            <x-card title="Fee Records">
                <table class="table-base">
                    <thead><tr><th>Term</th><th>Category</th><th>Due</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($studentFees as $fee)
                            <tr>
                                <td>{{ $fee->term?->name }}</td>
                                <td>{{ $fee->feeStructure?->feeCategory?->name }}</td>
                                <td>₦{{ number_format($fee->amount_due, 2) }}</td>
                                <td>₦{{ number_format($fee->amount_paid, 2) }}</td>
                                <td>₦{{ number_format($fee->balance, 2) }}</td>
                                <td><span class="{{ $fee->status === 'paid' ? 'badge-green' : ($fee->status === 'partial' ? 'badge-yellow' : 'badge-red') }}">{{ ucfirst($fee->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state title="No fee records yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
                <a href="{{ route('student-fees.show', $student) }}" class="mt-4 inline-block text-sm font-medium text-emerald-700 hover:underline">View full fee & payment history &rarr;</a>
            </x-card>
        </div>

        <div x-show="tab === 'documents'" x-cloak>
            <x-card title="Documents">
                <div class="space-y-2">
                    @forelse($student->documents as $doc)
                        <div class="flex items-center justify-between rounded-md border border-gray-100 p-3 text-sm">
                            <div>
                                <p class="font-medium">{{ $doc->title }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</p>
                            </div>
                            <div class="space-x-3">
                                <a href="{{ route('documents.download', $doc) }}" class="text-emerald-700 hover:underline">Download</a>
                                @can('delete', $doc)
                                <x-delete-button :action="route('documents.destroy', $doc)">Delete</x-delete-button>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="No documents uploaded" />
                    @endforelse
                </div>

                @can('create', \App\Models\Document::class)
                <form method="POST" action="{{ route('documents.store', $student) }}" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 gap-2 border-t border-gray-100 pt-4 sm:grid-cols-4">
                    @csrf
                    <input type="text" name="title" placeholder="Document title" class="form-input sm:col-span-1" required>
                    <select name="type" class="form-select sm:col-span-1" required>
                        <option value="birth_certificate">Birth Certificate</option>
                        <option value="admission">Admission Document</option>
                        <option value="previous_school_record">Previous School Record</option>
                        <option value="certificate">Certificate</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="file" name="file" class="text-sm sm:col-span-1" required>
                    <button type="submit" class="btn-secondary sm:col-span-1">Upload</button>
                </form>
                @endcan
            </x-card>
        </div>
    </div>
</x-layouts.dashboard>
