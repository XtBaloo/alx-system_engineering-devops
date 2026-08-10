<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('admission_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female']);
            $table->date('date_of_birth')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('nationality')->default('Nigerian');
            $table->string('state_of_origin')->nullable();
            $table->string('lga')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('admission_date');
            $table->string('previous_school')->nullable();
            $table->foreignId('current_class_arm_id')->nullable()->constrained('class_arms')->nullOnDelete();
            $table->enum('status', ['active', 'graduated', 'withdrawn', 'suspended', 'transferred', 'archived'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
