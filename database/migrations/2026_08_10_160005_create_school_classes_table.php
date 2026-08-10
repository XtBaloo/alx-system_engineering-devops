<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. JSS 1, Primary 3, Nursery 2
            $table->enum('level', ['nursery', 'primary', 'junior_secondary', 'senior_secondary'])->default('primary');
            $table->unsignedInteger('order')->default(0); // sort order across the whole school
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
