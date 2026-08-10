<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('class_arm_id')->constrained('class_arms')->cascadeOnDelete();
            $table->enum('status', ['active', 'promoted', 'repeated', 'withdrawn'])->default('active');
            $table->foreignId('promoted_from_enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->foreignId('promoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('promoted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
