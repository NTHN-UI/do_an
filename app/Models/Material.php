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
        'teacher_id',
        'school_auto_id'
    ];
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->school_id = $model->school_id ?? auth()->user()->school_id;
            $maxAutoId = static::where('school_id', $model->school_id)->max('school_auto_id') ?? 0;
            $model->school_auto_id = $maxAutoId + 1;
        });
    }

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
    public function belongsToSchool($schoolId)
    {
        return $this->teacher->school_id == $schoolId;
    }
}
