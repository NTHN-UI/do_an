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
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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
}
