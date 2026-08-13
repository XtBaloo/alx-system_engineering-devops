<x-layouts.dashboard title="My Fees">
    <x-card>
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Term</th><th>Category</th><th>Due</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($fees as $f)
                    <tr>
                        <td>{{ $f->term?->name }}</td>
                        <td>{{ $f->feeStructure?->feeCategory?->name }}</td>
                        <td>₦{{ number_format($f->amount_due, 2) }}</td>
                        <td>₦{{ number_format($f->amount_paid, 2) }}</td>
                        <td>₦{{ number_format($f->balance, 2) }}</td>
                        <td><span class="{{ $f->status === 'paid' ? 'badge-green' : ($f->status === 'partial' ? 'badge-yellow' : 'badge-red') }}">{{ ucfirst($f->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="No fee records yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>

    <x-card title="Payment History" class="mt-6">
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Receipt No.</th><th>Date</th><th>Amount</th><th>Method</th></tr></thead>
            <tbody>
                @forelse($fees->flatMap->payments->sortByDesc('payment_date') as $p)
                    <tr>
                        <td>{{ $p->receipt_number }}</td>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                        <td>₦{{ number_format($p->amount, 2) }}</td>
                        <td class="capitalize">{{ str_replace('_',' ',$p->payment_method) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state title="No payments yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
</x-layouts.dashboard>
