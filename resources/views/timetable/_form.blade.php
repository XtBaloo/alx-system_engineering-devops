@php($timetable = $timetable ?? null)
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <div>
        <label class="form-label">Class</label>
        <select name="class_arm_id" class="form-select" required>
            <option value="">Select class</option>
            @foreach($classArms as $arm)
                <option value="{{ $arm->id }}" @selected(old('class_arm_id', $timetable?->class_arm_id) == $arm->id)>{{ $arm->full_name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Subject</label>
        <select name="subject_id" class="form-select" required>
            <option value="">Select subject</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(old('subject_id', $timetable?->subject_id) == $subject->id)>{{ $subject->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Teacher</label>
        <select name="teacher_id" class="form-select" required>
            <option value="">Select teacher</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $timetable?->teacher_id) == $teacher->id)>{{ $teacher->full_name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Day of Week</label>
        <select name="day_of_week" class="form-select" required>
            <option value="">Select day</option>
            @foreach($days as $value => $label)
                <option value="{{ $value }}" @selected(old('day_of_week', $timetable?->day_of_week) == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Start Time</label>
        <input type="time" name="start_time" value="{{ old('start_time', $timetable?->start_time?->format('H:i')) }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label">End Time</label>
        <input type="time" name="end_time" value="{{ old('end_time', $timetable?->end_time?->format('H:i')) }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label">Room / Venue</label>
        <input type="text" name="room" value="{{ old('room', $timetable?->room ?? '') }}" class="form-input" required placeholder="e.g. Room 12">
    </div>
</div>
