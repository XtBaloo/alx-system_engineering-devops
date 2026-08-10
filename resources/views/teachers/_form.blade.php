@php($teacher = $teacher ?? null)
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" value="{{ old('first_name', $teacher->first_name ?? '') }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" value="{{ old('last_name', $teacher->last_name ?? '') }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select" required>
            <option value="male" @selected(old('gender', $teacher->gender ?? '') === 'male')>Male</option>
            <option value="female" @selected(old('gender', $teacher->gender ?? '') === 'female')>Female</option>
        </select>
    </div>
    <div>
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $teacher?->date_of_birth?->format('Y-m-d')) }}" class="form-input">
    </div>
    <div>
        <label class="form-label">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $teacher->phone ?? '') }}" class="form-input">
    </div>
    <div>
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $teacher->email ?? '') }}" class="form-input">
    </div>
    <div class="sm:col-span-2">
        <label class="form-label">Address</label>
        <textarea name="address" rows="2" class="form-textarea">{{ old('address', $teacher->address ?? '') }}</textarea>
    </div>
    <div>
        <label class="form-label">Qualification</label>
        <input type="text" name="qualification" value="{{ old('qualification', $teacher->qualification ?? '') }}" class="form-input">
    </div>
    <div>
        <label class="form-label">Employment Date</label>
        <input type="date" name="employment_date" value="{{ old('employment_date', $teacher?->employment_date?->format('Y-m-d')) }}" class="form-input">
    </div>
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            @foreach(['active' => 'Active', 'on_leave' => 'On Leave', 'resigned' => 'Resigned', 'terminated' => 'Terminated'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $teacher->status ?? 'active') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Photograph</label>
        <input type="file" name="photo" class="text-sm">
    </div>
</div>

@unless($teacher)
<div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="create_login" value="1" class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">
        Create a login account for this teacher (requires email; a temporary password will be generated)
    </label>
</div>
@endunless
