@php($assessmentType = $assessmentType ?? null)
<div>
    <label class="form-label">Name</label>
    <input type="text" name="name" value="{{ old('name', $assessmentType->name ?? '') }}" class="form-input" required placeholder="e.g. CA 1">
</div>
<div>
    <label class="form-label">Code</label>
    <input type="text" name="code" value="{{ old('code', $assessmentType->code ?? '') }}" class="form-input" required placeholder="e.g. CA1">
</div>
<div>
    <label class="form-label">Max Score</label>
    <input type="number" name="max_score" value="{{ old('max_score', $assessmentType->max_score ?? '') }}" class="form-input" required>
</div>
<div>
    <label class="form-label">Display Order</label>
    <input type="number" name="order" value="{{ old('order', $assessmentType->order ?? 0) }}" class="form-input" required>
</div>
<div class="sm:col-span-2">
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $assessmentType->is_active ?? true)) class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">
        Active (available for score entry)
    </label>
</div>
