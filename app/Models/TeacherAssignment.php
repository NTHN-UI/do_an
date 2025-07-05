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
            'id',
            'id',
            'class_id',
            'grade_level_id'
        );
    }

}
