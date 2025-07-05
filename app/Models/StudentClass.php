<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;

    protected $table = 'student_classes';
    protected $fillable = ['user_id', 'class_id', 'academic_year_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
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

