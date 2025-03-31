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

    // Các role trong hệ thống
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_SCHOOL_ADMIN = 'school_admin';
    const ROLE_TEACHER = 'teacher';
    const ROLE_STUDENT = 'student';

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
        'date_of_birth' => 'date',
        'is_active' => 'boolean'

    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Kiểm tra vai trò
    public function isSuperAdmin()
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isSchoolAdmin()
    {
        return $this->role === self::ROLE_SCHOOL_ADMIN;
    }

    public function isTeacher()
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isStudent()
    {
        return $this->role === self::ROLE_STUDENT;
    }
    // Quan hệ với trường học
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function students()
    {
        return $this->belongsToMany(User::class, 'student_classes', 'class_id', 'user_id')
            ->withPivot('academic_year_id');
    }
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'student_classes', 'user_id', 'class_id')
            ->withPivot('academic_year_id');
    }
    public function studentClasses()
    {
        return $this->belongsToMany(ClassModel::class, 'student_classes', 'user_id', 'class_id')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }
    public function assignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }
    public function teachingAssignments()
    {
        return $this->hasMany(TeacherClass::class, 'user_id');
    }
    public function homeroomClasses()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id')
            ->where('is_homeroom', true);
    }

    public function subjectClasses()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id')
            ->where('is_homeroom', false);
    }
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'datetime:Y-m-d',
            'is_active' => 'boolean'
        ];
    }

}
