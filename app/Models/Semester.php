<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_year_id',
        'start_date',
        'end_date',
        'school_id',
        'school_auto_id',
        'is_current'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean'
    ];

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }
    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }
    protected static function booted()
    {
        static::creating(function ($model) {
            // Chỉ tự động tăng nếu không được gán giá trị
            if (empty($model->school_auto_id)) {
                $maxAutoId = static::where('school_id', $model->school_id)
                    ->max('school_auto_id') ?? 0;

                $model->school_auto_id = $maxAutoId + 1;
            }
        });

        static::deleted(function ($model) {
            // Chỉ sắp xếp lại nếu đây là học kỳ cuối cùng
            $maxAutoId = static::where('school_id', $model->school_id)
                ->max('school_auto_id') ?? 0;

            if ($model->school_auto_id < $maxAutoId) {
                return; // Không cần sắp xếp lại nếu không phải học kỳ cuối cùng
            }

            // Sắp xếp lại các học kỳ còn lại
            $semesters = static::where('school_id', $model->school_id)
                ->orderBy('school_auto_id')
                ->get();

            foreach ($semesters as $index => $semester) {
                if ($semester->school_auto_id != $index + 1) {
                    $semester->school_auto_id = $index + 1;
                    $semester->save();
                }
            }
        });
    }

}
