@php($guardian = $guardian ?? null)
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" value="{{ old('first_name', $guardian->first_name ?? '') }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" value="{{ old('last_name', $guardian->last_name ?? '') }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $guardian->phone ?? '') }}" class="form-input">
    </div>
    <div>
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $guardian->email ?? '') }}" class="form-input">
    </div>
    <div>
        <label class="form-label">Occupation</label>
        <input type="text" name="occupation" value="{{ old('occupation', $guardian->occupation ?? '') }}" class="form-input">
    </div>
    <div class="sm:col-span-2">
        <label class="form-label">Address</label>
        <textarea name="address" rows="2" class="form-textarea">{{ old('address', $guardian->address ?? '') }}</textarea>
    </div>
</div>
@unless($guardian)
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="create_login" value="1" class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">
    Create a login account for this guardian (requires email)
</label>
@endunless
