<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name','is_text_based', 'school_id', 'is_core'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
    public function users(){
        return $this->hasMany(User::class);
    }
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }
    // app/Models/Subject.php
    public function isSpecialSubject()
    {
        return in_array($this->name, [
            'Giáo dục quốc phòng và an ninh',
            'Giáo dục thể chất',
            'Nghệ thuật'
        ]);
    }
}


