<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->foreignId('grade_level_id')->constrained('grade_levels');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('users');
            $table->enum('test_type', ['fifteen_minutes', 'one_period']);
            $table->integer('total_marks');
            $table->integer('duration_override')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_template')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
