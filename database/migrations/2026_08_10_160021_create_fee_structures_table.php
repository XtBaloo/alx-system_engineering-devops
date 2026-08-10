<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_category_id')->constrained('fee_categories')->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('classes')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->boolean('is_compulsory')->default(true);
            $table->timestamps();

            $table->unique(['fee_category_id', 'school_class_id', 'academic_session_id', 'term_id'], 'fee_structure_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
