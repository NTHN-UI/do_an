<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankOption extends Model
{

    protected $fillable = ['question_bank_id', 'content', 'is_correct', 'order'];

    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }
}
