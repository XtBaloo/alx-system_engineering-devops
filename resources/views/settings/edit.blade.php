<x-layouts.dashboard title="School Settings">
    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <x-card title="School Identity">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">School Name</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $settings->school_name) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Motto</label>
                    <input type="text" name="motto" value="{{ old('motto', $settings->motto) }}" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-textarea">{{ old('address', $settings->address) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $settings->email) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Website</label>
                    <input type="text" name="website" value="{{ old('website', $settings->website) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Principal / Head Teacher</label>
                    <input type="text" name="principal_name" value="{{ old('principal_name', $settings->principal_name) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" value="{{ old('registration_number', $settings->registration_number) }}" class="form-input">
                </div>
            </div>
        </x-card>

        <x-card title="Branding & Signatures">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label class="form-label">School Logo</label>
                    @if($settings->logo_path)<img src="{{ Storage::disk('public')->url($settings->logo_path) }}" class="mb-2 h-16 w-16 rounded-full object-cover">@endif
                    <input type="file" name="logo" class="text-sm">
                </div>
                <div>
                    <label class="form-label">School Signature</label>
                    @if($settings->school_signature_path)<img src="{{ Storage::disk('public')->url($settings->school_signature_path) }}" class="mb-2 h-12">@endif
                    <input type="file" name="school_signature" class="text-sm">
                </div>
                <div>
                    <label class="form-label">Principal Signature</label>
                    @if($settings->principal_signature_path)<img src="{{ Storage::disk('public')->url($settings->principal_signature_path) }}" class="mb-2 h-12">@endif
                    <input type="file" name="principal_signature" class="text-sm">
                </div>
            </div>
        </x-card>

        <x-card title="Academic Context">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Current Academic Session</label>
                    <select name="current_academic_session_id" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" @selected(old('current_academic_session_id', $settings->current_academic_session_id) == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Current Term</label>
                    <select name="current_term_id" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" @selected(old('current_term_id', $settings->current_term_id) == $t->id)>{{ $t->academicSession->name }} — {{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-card>

        <x-card title="Grading & Report Card Settings">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="form-label">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Examination Max Score</label>
                    <input type="number" name="examination_max_score" value="{{ old('examination_max_score', $settings->examination_max_score) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Class Ranking Method</label>
                    <select name="ranking_method" class="form-select">
                        <option value="standard_competition" @selected($settings->ranking_method === 'standard_competition')>Standard competition (1,2,2,4)</option>
                        <option value="dense" @selected($settings->ranking_method === 'dense')>Dense (1,2,2,3)</option>
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label class="form-label">Report Card Footer Note</label>
                    <textarea name="report_card_footer_note" rows="2" class="form-textarea">{{ old('report_card_footer_note', $settings->report_card_footer_note) }}</textarea>
                </div>
            </div>
        </x-card>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">Save Settings</button>
        </div>
    </form>
</x-layouts.dashboard>
