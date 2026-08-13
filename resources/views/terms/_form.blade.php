@php($term = $term ?? null)
<div>
    <label class="form-label">Academic Session</label>
    <select name="academic_session_id" class="form-select" required>
        <option value="">-- Select --</option>
        @foreach($sessions as $s)
            <option value="{{ $s->id }}" @selected(old('academic_session_id', $term->academic_session_id ?? '') == $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('academic_session_id')" class="mt-1" />
</div>
<div>
    <label class="form-label">Term</label>
    <select name="name" class="form-select" required>
        @foreach(['First Term', 'Second Term', 'Third Term'] as $n)
            <option value="{{ $n }}" @selected(old('name', $term->name ?? '') == $n)>{{ $n }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>
<div>
    <label class="form-label">Start Date</label>
    <input type="date" name="start_date" value="{{ old('start_date', $term?->start_date?->format('Y-m-d')) }}" class="form-input" required>
    <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
</div>
<div>
    <label class="form-label">End Date</label>
    <input type="date" name="end_date" value="{{ old('end_date', $term?->end_date?->format('Y-m-d')) }}" class="form-input" required>
    <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
</div>
