@php($feeStructure = $feeStructure ?? null)
<div>
    <label class="form-label">Fee Category</label>
    <select name="fee_category_id" class="form-select" required>
        <option value="">-- Select --</option>
        @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('fee_category_id', $feeStructure->fee_category_id ?? '') == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('fee_category_id')" class="mt-1" />
</div>
<div>
    <label class="form-label">Class (leave blank for all classes)</label>
    <select name="school_class_id" class="form-select">
        <option value="">-- All Classes --</option>
        @foreach($classes as $c)
            <option value="{{ $c->id }}" @selected(old('school_class_id', $feeStructure->school_class_id ?? '') == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('school_class_id')" class="mt-1" />
</div>
<div>
    <label class="form-label">Academic Session</label>
    <select name="academic_session_id" class="form-select" required>
        <option value="">-- Select --</option>
        @foreach($sessions as $s)
            <option value="{{ $s->id }}" @selected(old('academic_session_id', $feeStructure->academic_session_id ?? ($currentSessionId ?? '')) == $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('academic_session_id')" class="mt-1" />
</div>
<div>
    <label class="form-label">Term</label>
    <select name="term_id" class="form-select" required>
        <option value="">-- Select --</option>
        @foreach($terms as $t)
            <option value="{{ $t->id }}" @selected(old('term_id', $feeStructure->term_id ?? ($currentTermId ?? '')) == $t->id)>{{ $t->academicSession->name }} — {{ $t->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('term_id')" class="mt-1" />
</div>
<div>
    <label class="form-label">Amount (₦)</label>
    <input type="number" step="0.01" name="amount" value="{{ old('amount', $feeStructure->amount ?? '') }}" class="form-input" required>
    <x-input-error :messages="$errors->get('amount')" class="mt-1" />
</div>
<div>
    <label class="flex items-center gap-2 text-sm mt-6">
        <input type="checkbox" name="is_compulsory" value="1" @checked(old('is_compulsory', $feeStructure->is_compulsory ?? true)) class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">
        Compulsory fee
    </label>
</div>
