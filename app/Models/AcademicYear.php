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

    public function scopeCurrent($query)
    {
        $today = now()->format('Y-m-d');
        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }


}
