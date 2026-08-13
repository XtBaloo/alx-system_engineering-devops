<x-layouts.dashboard title="Take Attendance" :subtitle="$term?->name">
    <x-card class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">Class</label>
                <select name="class_arm_id" class="form-select w-56" onchange="this.form.submit()">
                    <option value="">-- Select class --</option>
                    @foreach($classArms as $arm)
                        <option value="{{ $arm->id }}" @selected(($classArm?->id ?? null) == $arm->id)>{{ $arm->full_name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('class_arm_id')" class="mt-1" />
            </div>
            <div>
                <label class="form-label">Date</label>
                <input type="date" name="date" value="{{ $date }}" max="{{ now()->format('Y-m-d') }}" class="form-input" onchange="this.form.submit()">
                <x-input-error :messages="$errors->get('date')" class="mt-1" />
            </div>
        </form>
    </x-card>

    @if(!$session || !$term)
        <x-empty-state title="No active academic session/term" description="Ask an administrator to activate a session and term first." />
    @elseif($classArm)
        <x-card :title="$classArm->full_name.' — '.\Illuminate\Support\Carbon::parse($date)->format('d M Y')">
            <form method="POST" action="{{ route('attendance.store') }}">
                @csrf
                <input type="hidden" name="class_arm_id" value="{{ $classArm->id }}">
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="mb-3 flex flex-wrap gap-2 text-sm">
                    <button type="button" class="btn-secondary" onclick="document.querySelectorAll('[data-status=present]').forEach(r=>r.checked=true)">Mark all Present</button>
                </div>

                <x-input-error :messages="$errors->get('statuses')" class="mb-2" />

                <div class="space-y-2">
                    @forelse($students as $s)
                        @php($current = $existing[$s->id]->status ?? 'present')
                        <div class="flex flex-col gap-2 rounded-md border border-gray-100 p-3 sm:flex-row sm:items-center sm:justify-between">
                            <span class="font-medium text-gray-800">{{ $s->full_name }} <span class="text-xs text-gray-500">({{ $s->admission_number }})</span></span>
                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                @foreach(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'] as $val => $label)
                                    <label class="flex items-center gap-1">
                                        <input type="radio" name="statuses[{{ $s->id }}]" value="{{ $val }}" data-status="{{ $val }}" @checked($current === $val) class="text-emerald-700 focus:ring-emerald-500" required>
                                        {{ $label }}
                                    </label>
                                @endforeach
                                <x-input-error :messages="$errors->get('statuses.'.$s->id)" class="basis-full" />
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="No active students in this class" />
                    @endforelse
                </div>

                @if($students->isNotEmpty())
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-primary">Save Attendance</button>
                </div>
                @endif
            </form>
        </x-card>
    @else
        <x-empty-state title="Select a class to take attendance" />
    @endif
</x-layouts.dashboard>
