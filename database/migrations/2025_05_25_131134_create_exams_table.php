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
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ví dụ: 15 phút, 1 tiết, học kỳ
            $table->integer('duration'); // thời lượng (phút)
            $table->integer('question_count'); // số câu hỏi mặc định
            $table->timestamps();
        });


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

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams');
            $table->text('content'); // nội dung câu hỏi
            $table->timestamps();
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions');
            $table->string('content'); // nội dung đáp án
            $table->boolean('is_correct')->default(false); // đúng/sai
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
