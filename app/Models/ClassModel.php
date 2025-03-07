<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;
    protected $table = 'classes';
    protected $fillable = ['name', 'grade_level_id'];
    public function gradeLevel() {
        return $this->belongsTo(GradeLevel::class);
    }
}
