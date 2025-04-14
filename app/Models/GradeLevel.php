<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use HasFactory;
    protected $fillable = [
        'grade_number',
        'school_id'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }
    protected static function booted()
    {
        static::creating(function ($model) {
            $maxAutoId = static::where('school_id', $model->school_id)
                ->max('school_auto_id') ?? 0;
            $model->school_auto_id = $maxAutoId + 1;
        });

        static::deleted(function ($model) {
            // Lấy danh sách khối học cùng school_id, sắp xếp theo id
            $gradeLevels = static::where('school_id', $model->school_id)
                ->orderBy('id')
                ->get();

            // Đánh lại STT từ 1
            foreach ($gradeLevels as $index => $gradeLevel) {
                $gradeLevel->school_auto_id = $index + 1;
                $gradeLevel->save();
            }
        });
    }
}
