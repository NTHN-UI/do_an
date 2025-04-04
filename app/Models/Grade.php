<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $table = 'grades';
    protected $fillable = ['teacher_id', 'student_id', 'subject_id', 'class_id',
        'academic_year_id', 'semester_id', 'test_type', 'score'];
    const TEST_TYPES = [
        'fifteen_minutes' => '15 phút',
        'one_period' => '1 tiết',
        'semester' => 'Cuối kỳ',
        'final' => 'Cuối năm'
    ];
    public function student(){
        return $this->belongsTo(User::class, 'student_id');
    }
    public function teacher(){
        return $this->belongsTo(User::class, 'teacher_id');
    }
    public function subject(){
        return $this->belongsTo(Subject::class);
    }
    public function class(){
        return $this->belongsTo(ClassModel::class);
    }
    public function academicYear(){
        return $this->belongsTo(AcademicYear::class);
    }
    public function semester(){
        return $this->belongsTo(Semester::class);
    }
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }
    public function getLetterGradeAttribute()
    {
        if ($this->score >= 8.5) return 'A';
        if ($this->score >= 7.0) return 'B';
        if ($this->score >= 5.5) return 'C';
        if ($this->score >= 4.0) return 'D';
        return 'F';
    }
}
