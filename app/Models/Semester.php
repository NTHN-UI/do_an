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
        'is_current'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean'
    ];
    // Tự động xử lý học kỳ hiện tại
    protected static function booted()
    {
        static::saving(function ($semester) {
            if ($semester->is_current) {
                self::where('id', '!=', $semester->id)
                    ->update(['is_current' => false]);
            }
        });
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
