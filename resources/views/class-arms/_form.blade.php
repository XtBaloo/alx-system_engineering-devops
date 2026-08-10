@php($classArm = $classArm ?? null)
<div>
    <label class="form-label">Class</label>
    <select name="school_class_id" class="form-select" required>
        <option value="">-- Select --</option>
        @foreach($classes as $c)
            <option value="{{ $c->id }}" @selected(old('school_class_id', $classArm->school_class_id ?? '') == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="form-label">Arm (e.g. A, B, C)</label>
    <input type="text" name="name" value="{{ old('name', $classArm->name ?? '') }}" class="form-input" required>
</div>
<div>
    <label class="form-label">Class Teacher</label>
    <select name="class_teacher_id" class="form-select">
        <option value="">-- None --</option>
        @foreach($teachers as $t)
            <option value="{{ $t->id }}" @selected(old('class_teacher_id', $classArm->class_teacher_id ?? '') == $t->id)>{{ $t->full_name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="form-label">Capacity (optional)</label>
    <input type="number" name="capacity" value="{{ old('capacity', $classArm->capacity ?? '') }}" class="form-input">
</div>
