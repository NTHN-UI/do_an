<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['year',
        'start_date',
        'end_date',
        'school_id',
        'school_auto_id',

    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }
    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $maxAutoId = static::where('school_id', $model->school_id)
                ->max('school_auto_id') ?? 0;
            $model->school_auto_id = $maxAutoId + 1;
        });

        static::deleted(function ($model) {
            // Lấy danh sách năm học cùng school_id, sắp xếp theo id
            $academicYears = static::where('school_id', $model->school_id)
                ->orderBy('id')
                ->get();

            // Đánh lại STT từ 1
            foreach ($academicYears as $index => $academicYear) {
                $academicYear->school_auto_id = $index + 1;
                $academicYear->save();
            }
        });
    }

}
