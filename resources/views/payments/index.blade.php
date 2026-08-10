<x-layouts.dashboard title="Payments">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search receipt no, student..." class="form-input w-64">
            <input type="date" name="date" value="{{ request('date') }}" class="form-input" onchange="this.form.submit()">
        </form>
        @can('recordPayment', \App\Models\StudentFee::class)
        <a href="{{ route('payments.create') }}" class="btn-primary">+ Record Payment</a>
        @endcan
    </div>
    <x-card>
        <table class="table-base">
            <thead><tr><th>Receipt No.</th><th>Student</th><th>Category</th><th>Amount</th><th>Method</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>{{ $p->receipt_number }}</td>
                        <td>{{ $p->studentFee?->student?->full_name }}</td>
                        <td>{{ $p->studentFee?->feeStructure?->feeCategory?->name }}</td>
                        <td>₦{{ number_format($p->amount, 2) }}</td>
                        <td class="capitalize">{{ str_replace('_',' ',$p->payment_method) }}</td>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                        <td><a href="{{ route('payments.receipt', $p) }}" class="text-emerald-700 hover:underline">Receipt</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state title="No payments recorded yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <div class="mt-4">{{ $payments->links() }}</div>
</x-layouts.dashboard>
