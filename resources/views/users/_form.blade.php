@php($user = $user ?? null)
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="form-label">Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label">Password {{ $user ? '(leave blank to keep current)' : '' }}</label>
        <input type="password" name="password" class="form-input" {{ $user ? '' : 'required' }} minlength="8">
    </div>
    <div>
        <label class="form-label">Role</label>
        <select name="role" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}" @selected(old('role', $user?->roles->first()?->name) === $role->name)>{{ ucfirst(str_replace('-', ' ', $role->name)) }}</option>
            @endforeach
        </select>
    </div>
</div>
<div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true)) class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">
        Active account
    </label>
</div>
