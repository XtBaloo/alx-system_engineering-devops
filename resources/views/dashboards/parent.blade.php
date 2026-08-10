<x-layouts.dashboard title="Parent Dashboard" :subtitle="$term?->name">
    @if($children->isEmpty())
        <x-empty-state title="No children linked to your account" description="Please contact the school administrator." />
    @else
        <div class="mb-6 flex flex-wrap gap-2">
            @foreach($children as $child)
                <a href="{{ route('dashboard', ['child' => $child->id]) }}"
                   class="{{ $selectedChild?->id === $child->id ? 'btn-primary' : 'btn-secondary' }}">
                    {{ $child->full_name }}
                </a>
            @endforeach
        </div>

        @if($selectedChild)
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <x-card title="Child Information">
                    <p class="font-semibold text-gray-900">{{ $selectedChild->full_name }}</p>
                    <p class="text-sm text-gray-500">{{ $selectedChild->admission_number }}</p>
                    <p class="text-sm text-gray-500">{{ $selectedChild->currentClassArm?->full_name }}</p>
                    <a href="{{ route('students.show', $selectedChild) }}" class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:underline">View full profile &rarr;</a>
                </x-card>

                <x-card title="Fee Status">
                    @php($balance = $selectedChild->studentFees()->when($term, fn($q) => $q->where('term_id', $term->id))->get()->sum(fn($f) => $f->balance))
                    <p class="text-2xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">₦{{ number_format($balance, 2) }}</p>
                    <p class="text-sm text-gray-500">Outstanding balance this term</p>
                    <a href="{{ route('student-fees.show', $selectedChild) }}" class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:underline">View payment history &rarr;</a>
                </x-card>

                <x-card title="Announcements">
                    @forelse($announcements as $a)
                        <div class="border-b border-gray-50 py-2 text-sm last:border-0">
                            <p class="font-medium text-gray-800">{{ $a->title }}</p>
                            <p class="text-xs text-gray-500">{{ $a->published_at?->diffForHumans() }}</p>
                        </div>
                    @empty
                        <x-empty-state title="No announcements yet" />
                    @endforelse
                </x-card>
            </div>
        @endif
    @endif
</x-layouts.dashboard>
