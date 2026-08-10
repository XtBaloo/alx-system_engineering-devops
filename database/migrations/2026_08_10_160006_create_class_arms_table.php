<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_arms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('name'); // e.g. A, B, C -> JSS 1A
            $table->foreignId('class_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->unsignedInteger('capacity')->nullable();
            $table->timestamps();

            $table->unique(['school_class_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_arms');
    }
};
