@php($classArm = $classArm ?? null)
<div>
    <label class="form-label">Class</label>
    <select name="school_class_id" class="form-select" required>
        <option value="">-- Select --</option>
        @foreach($classes as $c)
            <option value="{{ $c->id }}" @selected(old('school_class_id', $classArm->school_class_id ?? '') == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('school_class_id')" class="mt-1" />
</div>
<div>
    <label class="form-label">Arm (e.g. A, B, C)</label>
    <input type="text" name="name" value="{{ old('name', $classArm->name ?? '') }}" class="form-input" required>
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>
<div>
    <label class="form-label">Class Teacher</label>
    <select name="class_teacher_id" class="form-select">
        <option value="">-- None --</option>
        @foreach($teachers as $t)
            <option value="{{ $t->id }}" @selected(old('class_teacher_id', $classArm->class_teacher_id ?? '') == $t->id)>{{ $t->full_name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('class_teacher_id')" class="mt-1" />
</div>
<div>
    <label class="form-label">Capacity (optional)</label>
    <input type="number" name="capacity" value="{{ old('capacity', $classArm->capacity ?? '') }}" class="form-input">
    <x-input-error :messages="$errors->get('capacity')" class="mt-1" />
</div>
