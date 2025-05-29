<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 'academic_year_id', 'semester_id',
        'grade_level_id', 'subject_id', 'exam_type_id', 'teacher_id',
        'total_marks', 'duration_override', 'is_published', 'is_template'
    ];

    public function getTestTypeNameAttribute()
    {
        return [
            'fifteen_minutes' => '15 phút',
            'one_period' => '1 tiết',
        ][$this->test_type];
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }


    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function imports()
    {
        return $this->hasMany(ExamImport::class);
    }

    public function getDurationAttribute()
    {
        return $this->duration_override ?? $this->examType->duration;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }
}
