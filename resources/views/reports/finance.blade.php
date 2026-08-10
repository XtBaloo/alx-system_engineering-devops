<x-layouts.dashboard title="Financial Report" :subtitle="$term?->name">
    <div class="mb-4 flex justify-end print:hidden">
        <button onclick="window.print()" class="btn-secondary">Print</button>
    </div>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Due" :value="'₦'.number_format($totalDue, 2)" color="blue" />
        <x-stat-card label="Total Collected" :value="'₦'.number_format($totalPaid, 2)" color="emerald" />
        <x-stat-card label="Outstanding" :value="'₦'.number_format($totalOutstanding, 2)" color="red" />
    </div>

    <x-card title="Breakdown by Category" class="mb-6">
        <table class="table-base">
            <thead><tr><th>Category</th><th>Due</th><th>Collected</th><th>Outstanding</th></tr></thead>
            <tbody>
                @forelse($byCategory as $name => $row)
                    <tr>
                        <td>{{ $name }}</td>
                        <td>₦{{ number_format($row['due'], 2) }}</td>
                        <td>₦{{ number_format($row['paid'], 2) }}</td>
                        <td>₦{{ number_format($row['due'] - $row['paid'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state title="No fee data yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <x-card title="Recent Payments">
        <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 print:hidden">
            <div><label class="form-label">From</label><input type="date" name="from" value="{{ request('from') }}" class="form-input"></div>
            <div><label class="form-label">To</label><input type="date" name="to" value="{{ request('to') }}" class="form-input"></div>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
        <table class="table-base">
            <thead><tr><th>Receipt No.</th><th>Student</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($recentPayments as $p)
                    <tr>
                        <td>{{ $p->receipt_number }}</td>
                        <td>{{ $p->studentFee?->student?->full_name }}</td>
                        <td>₦{{ number_format($p->amount, 2) }}</td>
                        <td class="capitalize">{{ str_replace('_',' ',$p->payment_method) }}</td>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="No payments found" /></td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4 print:hidden">{{ $recentPayments->links() }}</div>
    </x-card>
</x-layouts.dashboard>
