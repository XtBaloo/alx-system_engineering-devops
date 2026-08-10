<x-layouts.dashboard title="Payment Receipt">
    <div class="mb-4 flex justify-end gap-3 print:hidden">
        <a href="{{ route('payments.receipt.pdf', $payment) }}" class="btn-secondary">Download PDF</a>
        <button onclick="window.print()" class="btn-primary">Print</button>
    </div>
    <div class="mx-auto max-w-md bg-white p-6 shadow print:shadow-none">
        @php($pdfMode = false)
        @include('payments._receipt')
    </div>
</x-layouts.dashboard>
