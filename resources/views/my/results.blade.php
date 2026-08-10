<x-layouts.dashboard title="My Results">
    @forelse($results as $termName => $termResults)
        <x-card :title="$termName" class="mb-6">
            <table class="table-base">
                <thead><tr><th>Subject</th><th>CA/Assessment</th><th>Exam</th><th>Total</th><th>Grade</th><th>Remark</th><th>Position</th></tr></thead>
                <tbody>
                    @foreach($termResults as $r)
                        <tr>
                            <td>{{ $r->subject?->name }}</td>
                            <td>{{ $r->assessment_total }}</td>
                            <td>{{ $r->examination_score }}</td>
                            <td class="font-semibold">{{ $r->total_score }}</td>
                            <td><span class="badge-green">{{ $r->grade }}</span></td>
                            <td>{{ $r->remark }}</td>
                            <td>{{ $r->position }}@if($r->subject_class_size)/{{ $r->subject_class_size }}@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <a href="{{ route('report-cards.pdf', $student) }}" class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:underline">Download report card PDF &rarr;</a>
        </x-card>
    @empty
        <x-empty-state title="No published results yet" />
    @endforelse
</x-layouts.dashboard>
