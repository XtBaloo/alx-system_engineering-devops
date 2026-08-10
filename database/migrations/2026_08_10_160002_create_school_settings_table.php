<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('Prime Foundation Academy');
            $table->string('motto')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('school_signature_path')->nullable();
            $table->string('principal_signature_path')->nullable();
            $table->string('currency_symbol')->default('₦');
            $table->unsignedTinyInteger('examination_max_score')->default(60);
            $table->enum('ranking_method', ['standard_competition', 'dense'])->default('standard_competition');
            $table->text('report_card_footer_note')->nullable();
            $table->foreignId('current_academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('current_term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
