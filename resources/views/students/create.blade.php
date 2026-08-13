<x-layouts.dashboard title="Admit New Student">
    <x-card>
        <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-input" required>
                    <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}" class="form-input">
                    <x-input-error :messages="$errors->get('middle_name')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-input" required>
                    <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-input">
                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Photograph</label>
                    <input type="file" name="photo" class="text-sm">
                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', 'Nigerian') }}" class="form-input">
                    <x-input-error :messages="$errors->get('nationality')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">State of Origin</label>
                    <input type="text" name="state_of_origin" value="{{ old('state_of_origin') }}" class="form-input">
                    <x-input-error :messages="$errors->get('state_of_origin')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">LGA</label>
                    <input type="text" name="lga" value="{{ old('lga') }}" class="form-input">
                    <x-input-error :messages="$errors->get('lga')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-textarea">{{ old('address') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input">
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Admission Date</label>
                    <input type="date" name="admission_date" value="{{ old('admission_date', now()->format('Y-m-d')) }}" class="form-input" required>
                    <x-input-error :messages="$errors->get('admission_date')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Previous School</label>
                    <input type="text" name="previous_school" value="{{ old('previous_school') }}" class="form-input">
                    <x-input-error :messages="$errors->get('previous_school')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Class</label>
                    <select name="current_class_arm_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        @foreach($classArms as $arm)
                            <option value="{{ $arm->id }}" @selected(old('current_class_arm_id') == $arm->id)>{{ $arm->full_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('current_class_arm_id')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" selected>Active</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="create_login" value="1" class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">
                    Create a login account for this student (requires email)
                </label>
            </div>

            <div class="border-t border-gray-100 pt-6">
                <h3 class="mb-3 font-semibold text-gray-900">Parent / Guardian</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Existing Guardian</label>
                        <select name="guardian_id" class="form-select">
                            <option value="">-- Or create new below --</option>
                            @foreach($guardians as $g)
                                <option value="{{ $g->id }}" @selected(old('guardian_id') == $g->id)>{{ $g->full_name }} ({{ $g->phone }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('guardian_id')" class="mt-1" />
                    </div>
                    <div>
                        <label class="form-label">Relationship</label>
                        <input type="text" name="guardian_relationship" value="{{ old('guardian_relationship') }}" class="form-input" placeholder="e.g. Father, Mother, Uncle">
                        <x-input-error :messages="$errors->get('guardian_relationship')" class="mt-1" />
                    </div>
                    <div>
                        <label class="form-label">New Guardian First Name</label>
                        <input type="text" name="new_guardian_first_name" value="{{ old('new_guardian_first_name') }}" class="form-input">
                        <x-input-error :messages="$errors->get('new_guardian_first_name')" class="mt-1" />
                    </div>
                    <div>
                        <label class="form-label">New Guardian Last Name</label>
                        <input type="text" name="new_guardian_last_name" value="{{ old('new_guardian_last_name') }}" class="form-input">
                        <x-input-error :messages="$errors->get('new_guardian_last_name')" class="mt-1" />
                    </div>
                    <div>
                        <label class="form-label">New Guardian Phone</label>
                        <input type="text" name="new_guardian_phone" value="{{ old('new_guardian_phone') }}" class="form-input">
                        <x-input-error :messages="$errors->get('new_guardian_phone')" class="mt-1" />
                    </div>
                    <div>
                        <label class="form-label">New Guardian Email</label>
                        <input type="email" name="new_guardian_email" value="{{ old('new_guardian_email') }}" class="form-input">
                        <x-input-error :messages="$errors->get('new_guardian_email')" class="mt-1" />
                    </div>
                    <div>
                        <label class="form-label">New Guardian Occupation</label>
                        <input type="text" name="new_guardian_occupation" value="{{ old('new_guardian_occupation') }}" class="form-input">
                        <x-input-error :messages="$errors->get('new_guardian_occupation')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('students.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Admit Student</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
