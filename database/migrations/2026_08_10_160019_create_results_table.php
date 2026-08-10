<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_arm_id')->constrained('class_arms')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->decimal('assessment_total', 5, 2)->default(0);
            $table->decimal('examination_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->string('grade', 5)->nullable();
            $table->string('remark')->nullable();
            $table->decimal('grade_point', 4, 2)->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->unsignedInteger('subject_class_size')->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved', 'published'])->default('draft');
            $table->text('teacher_comment')->nullable();
            $table->text('principal_comment')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'term_id'], 'result_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
