<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    use HasFactory;

        protected $fillable = [
            'content', 'subject_id', 'grade_level_id', 'teacher_id', 'school_id'
        ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionBankOption::class)->orderBy('order');
    }

    public function examQuestions()
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
