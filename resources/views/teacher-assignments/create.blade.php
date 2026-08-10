<x-layouts.dashboard title="New Teacher Assignment">
    <x-card>
        <form method="POST" action="{{ route('teacher-assignments.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>{{ $t->full_name }} ({{ $t->teacher_id }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Academic Session</label>
                <select name="academic_session_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}" @selected(old('academic_session_id', $currentSessionId) == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Class Arm</label>
                <select name="class_arm_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($classArms as $arm)
                        <option value="{{ $arm->id }}" @selected(old('class_arm_id') == $arm->id)>{{ $arm->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" @selected(old('subject_id') == $subj->id)>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('teacher-assignments.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </x-card>
</x-layouts.dashboard>
