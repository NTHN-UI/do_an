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

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('guardian_name')->nullable();
            $table->string('guardian_email')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('role', ['admin', 'teacher', 'student']);
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('grade_number');
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('grade_level_id')->constrained('grade_levels')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('student_classes', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->primary(['user_id', 'class_id', 'academic_year_id']);
        });

        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->timestamps();
        });

        foreach (['fifteen_minute_tests', 'one_period_tests', 'semester_tests', 'final_grades'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
                $table->float('score');
                $table->timestamps();
            });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('fifteen_minute_tests');
        Schema::dropIfExists('one_period_tests');
        Schema::dropIfExists('semester_tests');
        Schema::dropIfExists('final_grades');
        Schema::dropIfExists('teacher_classes');
        Schema::dropIfExists('student_classes');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('grade_levels');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('semesters');
    }
};
