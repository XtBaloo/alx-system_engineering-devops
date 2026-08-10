<x-layouts.dashboard :title="'Report Card — '.$student->full_name">
    <div class="mb-4 flex justify-end gap-3 print:hidden">
        <a href="{{ route('report-cards.pdf', $student) }}" class="btn-secondary">Download PDF</a>
        <button onclick="window.print()" class="btn-primary">Print</button>
    </div>

    <div class="mx-auto max-w-3xl bg-white p-8 shadow print:shadow-none">
        @php($pdfMode = false)
        @include('report-cards._card')
    </div>
</x-layouts.dashboard>
