<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'address',
        'phone',
        'gender',
        'date_of_birth',
        'role',
        'is_active',
        'school_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Scope để lọc học sinh
    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    // Scope tìm kiếm
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('full_name', 'like', '%'.$keyword.'%')
                ->orWhere('email', 'like', '%'.$keyword.'%')
                ->orWhere('phone', 'like', '%'.$keyword.'%');
        });
    }

    // Quan hệ với trường học
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Quan hệ với lớp học
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'student_classes', 'user_id', 'class_id')
            ->withPivot('academic_year_id');
    }



    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'datetime:Y-m-d',

        ];
    }
}
