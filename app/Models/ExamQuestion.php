<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id', 'question_bank_id', 'content', 'order', 'marks'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class)->withDefault();
    }

    public function options()
    {
        return $this->hasMany(ExamQuestionOption::class);
    }
}
