<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_assignment_id',
        'student_id',
        'score',
        'time_taken',
        'answers'
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function assignment()
    {
        return $this->belongsTo(ExamAssignment::class, 'exam_assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
