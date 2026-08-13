<x-layouts.dashboard title="Student Promotions">
    <x-card title="Select Source Class" class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">Promote students from</label>
                <select name="source_class_arm_id" class="form-select w-64" onchange="this.form.submit()">
                    <option value="">-- Select class --</option>
                    @foreach($classArms as $arm)
                        <option value="{{ $arm->id }}" @selected($sourceArmId == $arm->id)>{{ $arm->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-card>

    @if($sourceArmId)
    <x-card title="Select Students to Promote">
        <form method="POST" action="{{ route('students.promote') }}">
            @csrf
            <div class="mb-4">
                <label class="form-label">Promote to Class</label>
                <select name="target_class_arm_id" class="form-select w-64" required>
                    <option value="">-- Select target class --</option>
                    @foreach($classArms as $arm)
                        <option value="{{ $arm->id }}" @selected(old('target_class_arm_id') == $arm->id)>{{ $arm->full_name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('target_class_arm_id')" class="mt-1" />
            </div>

            <x-input-error :messages="$errors->get('student_ids')" class="mb-2" />

            <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="document.querySelectorAll('.student-check').forEach(c => c.checked = this.checked)"></th>
                        <th>Admission No.</th><th>Name</th><th>Gender</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $s)
                        <tr>
                            <td><input type="checkbox" name="student_ids[]" value="{{ $s->id }}" class="student-check"></td>
                            <td>{{ $s->admission_number }}</td>
                            <td>{{ $s->full_name }}</td>
                            <td class="capitalize">{{ $s->gender }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state title="No active students in this class" /></td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            @if($students->isNotEmpty())
            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-primary" onclick="return confirm('Promote selected students?')">Promote Selected Students</button>
            </div>
            @endif
        </form>
    </x-card>
    @endif
</x-layouts.dashboard>
