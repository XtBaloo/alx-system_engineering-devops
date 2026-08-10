<?php

namespace Database\Seeders;

use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    public function run(): void
    {
        SchoolSetting::updateOrCreate(['id' => 1], [
            'school_name' => 'Prime Foundation Academy',
            'motto' => 'Knowledge, Character, Excellence',
            'address' => '15 Freedom Way, Lekki Phase 1, Lagos, Nigeria',
            'phone' => '+234 801 234 5678',
            'email' => 'info@primefoundationacademy.example',
            'website' => 'www.primefoundationacademy.example',
            'principal_name' => 'Mrs. Adaeze Okonkwo',
            'registration_number' => 'PFA/LAG/2010/001',
            'currency_symbol' => '₦',
            'examination_max_score' => 60,
            'ranking_method' => 'standard_competition',
            'report_card_footer_note' => 'This report card is only valid with the school stamp and signature.',
        ]);
    }
}
