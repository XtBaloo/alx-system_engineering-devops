<x-layouts.dashboard title="Outstanding Fees">
    <x-card class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">Class</label>
                <select name="class_arm_id" class="form-select w-56" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach($classArms as $arm)
                        <option value="{{ $arm->id }}" @selected(request('class_arm_id') == $arm->id)>{{ $arm->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ml-auto text-sm text-gray-600">
                Total outstanding (this page): <span class="font-semibold text-red-600">₦{{ number_format($totalOutstanding, 2) }}</span>
            </div>
        </form>
    </x-card>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Student</th><th>Class</th><th>Category</th><th>Term</th><th>Due</th><th>Paid</th><th>Balance</th><th></th></tr></thead>
            <tbody>
                @forelse($fees as $f)
                    <tr>
                        <td>{{ $f->student->full_name }}</td>
                        <td>{{ $f->student->currentClassArm?->full_name }}</td>
                        <td>{{ $f->feeStructure->feeCategory->name }}</td>
                        <td>{{ $f->term->name }}</td>
                        <td>₦{{ number_format($f->amount_due, 2) }}</td>
                        <td>₦{{ number_format($f->amount_paid, 2) }}</td>
                        <td class="font-semibold text-red-600">₦{{ number_format($f->balance, 2) }}</td>
                        <td><a href="{{ route('payments.create', ['student_id' => $f->student_id]) }}" class="text-emerald-700 hover:underline">Record Payment</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-empty-state title="No outstanding fees" description="All fees have been fully paid." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $fees->links() }}</div>
</x-layouts.dashboard>
