@php($schoolClass = $schoolClass ?? null)
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div>
        <label class="form-label">Class Name</label>
        <input type="text" name="name" value="{{ old('name', $schoolClass->name ?? '') }}" class="form-input" required placeholder="e.g. JSS 1">
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>
    <div>
        <label class="form-label">Level</label>
        <select name="level" class="form-select" required>
            @foreach(['nursery' => 'Nursery', 'primary' => 'Primary', 'junior_secondary' => 'Junior Secondary', 'senior_secondary' => 'Senior Secondary'] as $val => $label)
                <option value="{{ $val }}" @selected(old('level', $schoolClass->level ?? '') == $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('level')" class="mt-1" />
    </div>
    <div>
        <label class="form-label">Sort Order</label>
        <input type="number" name="order" value="{{ old('order', $schoolClass->order ?? 0) }}" class="form-input" required>
        <x-input-error :messages="$errors->get('order')" class="mt-1" />
    </div>
</div>

<div>
    <label class="form-label">Subjects offered by this class</label>
    <div class="grid grid-cols-2 gap-2 rounded-md border border-gray-200 p-3 sm:grid-cols-3 max-h-56 overflow-y-auto">
        @foreach($subjects as $subject)
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="subjects[]" value="{{ $subject->id }}"
                    @checked(collect(old('subjects', $schoolClass?->subjects->pluck('id')->toArray() ?? []))->contains($subject->id))
                    class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">
                {{ $subject->name }}
            </label>
        @endforeach
    </div>
</div>
