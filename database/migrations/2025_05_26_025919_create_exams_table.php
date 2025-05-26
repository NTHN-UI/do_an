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
            $table->string('title'); // tên đề thi
            $table->foreignId('academic_year_id')->constrained('academic_years'); // năm học
            $table->foreignId('semester_id')->constrained('semesters'); // học kỳ
            $table->foreignId('teacher_id')->constrained('users'); // người ra đề
            $table->foreignId('subject_id')->constrained('subjects'); // môn học
            $table->foreignId('grade_level_id')->constrained('grade_levels'); // khối lớp
            $table->foreignId('exam_type_id')->constrained('exam_types'); // loại đề thi
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
