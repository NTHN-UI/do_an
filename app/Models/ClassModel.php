<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = ['name', 'grade_level_id', 'academic_year_id'];

    public function gradeLevel() {
        return $this->belongsTo(GradeLevel::class);
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }
}
