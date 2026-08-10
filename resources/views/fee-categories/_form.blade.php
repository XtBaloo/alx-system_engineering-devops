@php($feeCategory = $feeCategory ?? null)
<div>
    <label class="form-label">Name</label>
    <input type="text" name="name" value="{{ old('name', $feeCategory->name ?? '') }}" class="form-input" required>
</div>
<div>
    <label class="form-label">Code</label>
    <input type="text" name="code" value="{{ old('code', $feeCategory->code ?? '') }}" class="form-input" required>
</div>
<div class="sm:col-span-2">
    <label class="form-label">Description</label>
    <textarea name="description" rows="2" class="form-textarea">{{ old('description', $feeCategory->description ?? '') }}</textarea>
</div>
<div>
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
        <option value="active" @selected(old('status', $feeCategory->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $feeCategory->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
