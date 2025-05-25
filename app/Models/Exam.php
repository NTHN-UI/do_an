<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'title', 'academic_year_id', 'semester_id', 'teacher_id',
        'subject_id', 'grade_level_id', 'exam_type_id'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
