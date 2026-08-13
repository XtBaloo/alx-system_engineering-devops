@php($announcement = $announcement ?? null)
<div>
    <label class="form-label">Title</label>
    <input type="text" name="title" value="{{ old('title', $announcement->title ?? '') }}" class="form-input" required>
    <x-input-error :messages="$errors->get('title')" class="mt-1" />
</div>
<div>
    <label class="form-label">Message</label>
    <textarea name="message" rows="5" class="form-textarea" required>{{ old('message', $announcement->message ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('message')" class="mt-1" />
</div>
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="form-label">Target Audience</label>
        <select name="target" x-model="target" class="form-select" required>
            <option value="everyone" @selected(old('target', $announcement->target ?? '') === 'everyone')>Everyone</option>
            <option value="teachers" @selected(old('target', $announcement->target ?? '') === 'teachers')>Teachers</option>
            <option value="students" @selected(old('target', $announcement->target ?? '') === 'students')>Students</option>
            <option value="parents" @selected(old('target', $announcement->target ?? '') === 'parents')>Parents</option>
            <option value="class" @selected(old('target', $announcement->target ?? '') === 'class')>Specific Class</option>
        </select>
        <x-input-error :messages="$errors->get('target')" class="mt-1" />
    </div>
    <div x-show="target === 'class'" x-cloak>
        <label class="form-label">Class</label>
        <select name="school_class_id" class="form-select">
            <option value="">-- Select --</option>
            @foreach($classes as $c)
                <option value="{{ $c->id }}" @selected(old('school_class_id', $announcement->school_class_id ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('school_class_id')" class="mt-1" />
    </div>
</div>
<div>
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
        <option value="draft" @selected(old('status', $announcement->status ?? 'draft') === 'draft')>Draft</option>
        <option value="published" @selected(old('status', $announcement->status ?? '') === 'published')>Published</option>
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-1" />
</div>
