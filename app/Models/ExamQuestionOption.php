<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestionOption extends Model
{
    protected $fillable = ['exam_question_id', 'content', 'is_correct', 'order'];

    public function examQuestion()
    {
        return $this->belongsTo(ExamQuestion::class);
    }
}
