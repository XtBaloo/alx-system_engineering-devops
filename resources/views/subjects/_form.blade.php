@php($subject = $subject ?? null)
<div>
    <label class="form-label">Subject Name</label>
    <input type="text" name="name" value="{{ old('name', $subject->name ?? '') }}" class="form-input" required>
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>
<div>
    <label class="form-label">Subject Code</label>
    <input type="text" name="code" value="{{ old('code', $subject->code ?? '') }}" class="form-input" required>
    <x-input-error :messages="$errors->get('code')" class="mt-1" />
</div>
<div>
    <label class="form-label">Category</label>
    <input type="text" name="category" value="{{ old('category', $subject->category ?? '') }}" class="form-input" placeholder="e.g. Core, Science, Arts">
    <x-input-error :messages="$errors->get('category')" class="mt-1" />
</div>
<div>
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
        <option value="active" @selected(old('status', $subject->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $subject->status ?? '') === 'inactive')>Inactive</option>
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-1" />
</div>
<div class="sm:col-span-2">
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_compulsory" value="1" @checked(old('is_compulsory', $subject->is_compulsory ?? true)) class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">
        Compulsory subject
    </label>
</div>
