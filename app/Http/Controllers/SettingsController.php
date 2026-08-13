<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\SchoolSetting;
use App\Models\Term;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = SchoolSetting::current();
        $sessions = AcademicSession::orderByDesc('name')->get();
        $terms = Term::orderByDesc('id')->get();

        return view('settings.edit', compact('settings', 'sessions', 'terms'));
    }

    public function update(Request $request)
    {
        $settings = SchoolSetting::current();

        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'motto' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'principal_name' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'examination_max_score' => ['required', 'integer', 'min:1', 'max:100'],
            'ranking_method' => ['required', 'in:standard_competition,dense'],
            'report_card_footer_note' => ['nullable', 'string', 'max:2000'],
            'current_academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
            'current_term_id' => ['nullable', 'exists:terms,id'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'school_signature' => ['nullable', 'image', 'max:2048'],
            'principal_signature' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('settings', 'public');
        }
        if ($request->hasFile('school_signature')) {
            $data['school_signature_path'] = $request->file('school_signature')->store('settings', 'public');
        }
        if ($request->hasFile('principal_signature')) {
            $data['principal_signature_path'] = $request->file('principal_signature')->store('settings', 'public');
        }

        $settings->update($data);

        return back()->with('success', 'School settings updated successfully.');
    }
}
