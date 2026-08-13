<x-layouts.dashboard title="Edit Student">
    <x-card>
        <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" class="form-input" required>
                    <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}" class="form-input">
                    <x-input-error :messages="$errors->get('middle_name')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" class="form-input" required>
                    <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="male" @selected(old('gender', $student->gender) === 'male')>Male</option>
                        <option value="female" @selected(old('gender', $student->gender) === 'female')>Female</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" class="form-input">
                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Photograph</label>
                    <input type="file" name="photo" class="text-sm">
                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $student->nationality) }}" class="form-input">
                    <x-input-error :messages="$errors->get('nationality')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">State of Origin</label>
                    <input type="text" name="state_of_origin" value="{{ old('state_of_origin', $student->state_of_origin) }}" class="form-input">
                    <x-input-error :messages="$errors->get('state_of_origin')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">LGA</label>
                    <input type="text" name="lga" value="{{ old('lga', $student->lga) }}" class="form-input">
                    <x-input-error :messages="$errors->get('lga')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-textarea">{{ old('address', $student->address) }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" class="form-input">
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $student->email) }}" class="form-input">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Admission Date</label>
                    <input type="date" name="admission_date" value="{{ old('admission_date', $student->admission_date?->format('Y-m-d')) }}" class="form-input" required>
                    <x-input-error :messages="$errors->get('admission_date')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Previous School</label>
                    <input type="text" name="previous_school" value="{{ old('previous_school', $student->previous_school) }}" class="form-input">
                    <x-input-error :messages="$errors->get('previous_school')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach(['active','graduated','withdrawn','suspended','transferred','archived'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $student->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('students.show', $student) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Update Student</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
