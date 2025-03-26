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
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('district');
            $table->string('province');
            $table->enum('education_level', ['primary', 'secondary', 'high']);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_email')->nullable()->unique();
            $table->string('guardian_phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('gender', ['Nam', 'Nữ', 'Khác'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('role', ['super_admin', 'school_admin', 'teacher', 'student']);
            $table->boolean('is_active')->default(true);
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year');
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade'); // Đúng
            $table->timestamps();
        });



        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('grade_number');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('name');
            $table->foreignId('grade_level_id')->constrained('grade_levels')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('education_level', ['primary', 'secondary', 'high']);
            $table->timestamps();
        });

        Schema::create('student_classes', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->primary(['user_id', 'class_id', 'academic_year_id']);
            $table->timestamps();
        });

        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('homeroom_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->unique(['class_id', 'academic_year_id']);
            $table->timestamps();
        });


        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->enum('test_type', ['fifteen_minutes', 'one_period', 'semester', 'final']);
            $table->decimal('score', 5, 2);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

        });
        // Bảng tài liệu tham khảo
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('file_path');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('users');
            $table->timestamps();
        });

// Bảng đề kiểm tra
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['quiz', 'midterm', 'final']);
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('class_id')->constrained('classes');
            $table->timestamps();
        });

// Bảng bài làm của học sinh
        Schema::create('student_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams');
            $table->foreignId('student_id')->constrained('users');
            $table->string('answer_file_path');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

    }
    /**
     * Reverse the migrations.
     */
        public function down(): void
    {
        Schema::dropIfExists('student_exams');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('homeroom_teachers');
        Schema::dropIfExists('teacher_classes');
        Schema::dropIfExists('student_classes');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('grade_levels');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('password_reset_tokens');
    }
};
