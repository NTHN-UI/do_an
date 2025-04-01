<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'subject_id',
        'teacher_id'
    ];

    // Quan hệ với môn học
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Quan hệ với giáo viên
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Scope cho giáo viên
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // Scope cho môn học
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }
}
