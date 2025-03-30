<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'academic_year_id',
        'is_homeroom'
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Kiểm tra trùng môn học trong lớp
            if (self::where('class_id', $model->class_id)
                ->where('subject_id', $model->subject_id)
                ->where('academic_year_id', $model->academic_year_id)
                ->exists()) {
                throw new \Exception('Môn học này đã có giáo viên dạy trong lớp');
            }

            // Kiểm tra GVCN
            if ($model->is_homeroom) {
                if (self::where('class_id', $model->class_id)
                    ->where('academic_year_id', $model->academic_year_id)
                    ->where('is_homeroom', true)
                    ->exists()) {
                    throw new \Exception('Lớp này đã có giáo viên chủ nhiệm');
                }

                if (self::where('teacher_id', $model->teacher_id)
                    ->where('academic_year_id', $model->academic_year_id)
                    ->where('is_homeroom', true)
                    ->exists()) {
                    throw new \Exception('Giáo viên này đã là chủ nhiệm lớp khác');
                }
            }
        });
    }
}
