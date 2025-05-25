<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'academic_year_id',
        'is_homeroom',
        'school_id'
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    // Trong TeacherAssignment.php
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function gradeLevel()
    {
        return $this->hasOneThrough(
            GradeLevel::class,
            ClassModel::class,
            'id', // Foreign key on classes table
            'id', // Foreign key on grade_levels table
            'class_id', // Local key on teacher_assignments table
            'grade_level_id' // Local key on classes table
        );
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Tự động gán school_id từ teacher
            if (empty($model->school_id) && $model->teacher) {
                $model->school_id = $model->teacher->school_id;
            }

            // Kiểm tra tất cả các quan hệ phải cùng trường
            $schoolId = $model->school_id ?? auth()->user()->school_id;

            if ($model->teacher && $model->teacher->school_id !== $schoolId) {
                throw new \Exception('Giáo viên không thuộc trường hiện tại');
            }

            if ($model->class && $model->class->school_id !== $schoolId) {
                throw new \Exception('Lớp học không thuộc trường hiện tại');
            }

            if ($model->subject && $model->subject->school_id !== $schoolId) {
                throw new \Exception('Môn học không thuộc trường hiện tại');
            }

            if ($model->academicYear && $model->academicYear->school_id !== $schoolId) {
                throw new \Exception('Năm học không thuộc trường hiện tại');
            }

            // Kiểm tra trùng môn học trong lớp
            if (self::where('class_id', $model->class_id)
                ->where('subject_id', $model->subject_id)
                ->where('academic_year_id', $model->academic_year_id)
                ->where('school_id', $schoolId)
                ->exists()) {
                throw new \Exception('Môn học này đã có giáo viên dạy trong lớp');
            }

            // Kiểm tra GVCN
            if ($model->is_homeroom) {
                if (self::where('class_id', $model->class_id)
                    ->where('academic_year_id', $model->academic_year_id)
                    ->where('is_homeroom', true)
                    ->where('school_id', $schoolId)
                    ->exists()) {
                    throw new \Exception('Lớp này đã có giáo viên chủ nhiệm');
                }

                if (self::where('teacher_id', $model->teacher_id)
                    ->where('academic_year_id', $model->academic_year_id)
                    ->where('is_homeroom', true)
                    ->where('school_id', $schoolId)
                    ->exists()) {
                    throw new \Exception('Giáo viên này đã là chủ nhiệm lớp khác');
                }
            }
        });
        static::updating(function ($model) {
            $schoolId = $model->school_id;

            // Kiểm tra tất cả các quan hệ phải cùng trường khi cập nhật
            if ($model->teacher && $model->teacher->school_id !== $schoolId) {
                throw new \Exception('Giáo viên không thuộc trường hiện tại');
            }

            if ($model->class && $model->class->school_id !== $schoolId) {
                throw new \Exception('Lớp học không thuộc trường hiện tại');
            }

            if ($model->subject && $model->subject->school_id !== $schoolId) {
                throw new \Exception('Môn học không thuộc trường hiện tại');
            }

            if ($model->academicYear && $model->academicYear->school_id !== $schoolId) {
                throw new \Exception('Năm học không thuộc trường hiện tại');
            }
        });
    }
}
