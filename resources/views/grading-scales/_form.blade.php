@php($gradingScale = $gradingScale ?? null)
<div>
    <label class="form-label">Min Score</label>
    <input type="number" name="min_score" value="{{ old('min_score', $gradingScale->min_score ?? '') }}" class="form-input" required>
    <x-input-error :messages="$errors->get('min_score')" class="mt-1" />
</div>
<div>
    <label class="form-label">Max Score</label>
    <input type="number" name="max_score" value="{{ old('max_score', $gradingScale->max_score ?? '') }}" class="form-input" required>
    <x-input-error :messages="$errors->get('max_score')" class="mt-1" />
</div>
<div>
    <label class="form-label">Grade</label>
    <input type="text" name="grade" value="{{ old('grade', $gradingScale->grade ?? '') }}" class="form-input" required>
    <x-input-error :messages="$errors->get('grade')" class="mt-1" />
</div>
<div>
    <label class="form-label">Remark</label>
    <input type="text" name="remark" value="{{ old('remark', $gradingScale->remark ?? '') }}" class="form-input" required>
    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
</div>
<div>
    <label class="form-label">Grade Point (optional)</label>
    <input type="number" step="0.01" name="grade_point" value="{{ old('grade_point', $gradingScale->grade_point ?? '') }}" class="form-input">
    <x-input-error :messages="$errors->get('grade_point')" class="mt-1" />
</div>
<div>
    <label class="form-label">Display Order</label>
    <input type="number" name="order" value="{{ old('order', $gradingScale->order ?? 0) }}" class="form-input" required>
    <x-input-error :messages="$errors->get('order')" class="mt-1" />
</div>
