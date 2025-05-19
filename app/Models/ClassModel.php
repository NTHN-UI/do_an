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
        return $this->belongsTo(GradeLevel::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_classes', 'class_id', 'user_id')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_classes')
            ->withPivot('subject_id', 'academic_year_id');
    }
    public function homeroomTeacher()
    {
        return $this->hasOne(TeacherAssignment::class, 'class_id')
            ->where('is_homeroom', true);
    }
}
