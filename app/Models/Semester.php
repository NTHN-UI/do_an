<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'academic_year_id'];

    /**
     * Mối quan hệ: Học kỳ thuộc về một năm học.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
