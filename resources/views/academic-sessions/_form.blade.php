@php($academicSession = $academicSession ?? null)
<div>
    <label class="form-label">Session Name (e.g. 2026/2027)</label>
    <input type="text" name="name" value="{{ old('name', $academicSession->name ?? '') }}" class="form-input" required>
</div>
<div>
    <label class="form-label">Start Date</label>
    <input type="date" name="start_date" value="{{ old('start_date', $academicSession?->start_date?->format('Y-m-d')) }}" class="form-input" required>
</div>
<div>
    <label class="form-label">End Date</label>
    <input type="date" name="end_date" value="{{ old('end_date', $academicSession?->end_date?->format('Y-m-d')) }}" class="form-input" required>
</div>
