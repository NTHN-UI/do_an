<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'district',
        'province',
        'education_level'
    ];
    protected $casts = [
        'created_at' => 'datetime: d/m/Y H:i:s',
        'updated_at' => 'datetime: d/m/Y H:i:s',
    ];

    public function getEducationLevelNameAttribute()
    {
        return match($this->education_level) {
            'primary' => 'Tiểu học',
            'secondary' => 'THCS',
            'high' => 'THPT',
            default => 'Không xác định',
        };
    }

}
