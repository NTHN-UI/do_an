<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterTest extends Model
{
    use HasFactory;
    protected $fillable = ['student_id', 'subject_id', 'class_id', 'academic_year_id', 'score'];
}
