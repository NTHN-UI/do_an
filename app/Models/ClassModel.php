<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'school_id',
        'school_auto_id',
        'grade_level_id',
        'academic_year_id'
    ];
    protected static function booted()
    {
        static::creating(function ($model) {
            $maxAutoId = static::where('school_id', $model->school_id)
                ->max('school_auto_id') ?? 0;
            $model->school_auto_id = $maxAutoId + 1;
        });

        static::deleted(function ($model) {
            // Lấy danh sách lớp học cùng school_id, sắp xếp theo id
            $classes = static::where('school_id', $model->school_id)
                ->orderBy('id')
                ->get();

            // Đánh lại STT từ 1
            foreach ($classes as $index => $class) {
                $class->school_auto_id = $index + 1;
                $class->save();
            }
        });
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_classes', 'class_id', 'user_id')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_classes')
            ->withPivot('subject_id', 'academic_year_id');
    }
    public function homeroomTeacher()
    {
        return $this->hasOne(TeacherAssignment::class, 'class_id')
            ->where('is_homeroom', true);
    }
}
