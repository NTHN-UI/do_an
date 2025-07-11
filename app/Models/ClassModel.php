<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'school_id',
        'grade_level_id',
        'academic_year_id'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }


    public function students()
    {
        return $this->belongsToMany(User::class, 'student_classes', 'class_id', 'user_id')
            ->withPivot('academic_year_id');
    }
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_assignments', 'class_id', 'teacher_id')
            ->withPivot(['subject_id', 'academic_year_id', 'is_homeroom'])
            ->withTimestamps();
    }
    public function homeroomTeacher()
    {
        return $this->hasOne(TeacherAssignment::class, 'class_id')
            ->where('is_homeroom', true);
    }
    public function homeroomAssignment()
    {
        return $this->hasOne(TeacherAssignment::class, 'class_id')
            ->where('is_homeroom', true)
            ->with('teacher');
    }
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'class_id');
    }
    public function assignments()
    {
        return $this->morphMany(ExamAssignment::class, 'assignable');
    }
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
