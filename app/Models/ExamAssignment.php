<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAssignment extends Model
{

    use HasFactory;

    protected $fillable = [
        'exam_id',
        'class_id',
        'start_time',
        'end_time',
        'shuffle_questions',
        'shuffle_options'
    ];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }


}
