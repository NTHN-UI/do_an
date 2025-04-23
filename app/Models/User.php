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
    protected static function booted()
    {
        static::creating(function ($model) {
            // Đảm bảo rằng school_id và role đã được thiết lập
            if ($model->school_id && $model->role) {
                // Lấy giá trị lớn nhất hiện có và tăng lên 1
                $maxAutoId = static::where('school_id', $model->school_id)
                    ->where('role', $model->role)
                    ->lockForUpdate() // Chống race condition
                    ->max('school_auto_id') ?? 0;

                $model->school_auto_id = $maxAutoId + 1;
            }
        });

        static::deleted(function ($model) {
            // Cập nhật lại school_auto_id sau khi xóa người dùng
            $users = static::where('school_id', $model->school_id)
                ->where('role', $model->role)
                ->orderBy('id')
                ->get();

            // Cập nhật lại theo STT mới
            foreach ($users as $index => $user) {
                $newAutoId = $index + 1;
                if ($user->school_auto_id !== $newAutoId) {
                    $user->school_auto_id = $newAutoId;
                    $user->saveQuietly(); // dùng saveQuietly để tránh kích hoạt sự kiện tạo mới nữa
                }
            }
        });
    }
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
            ->withPivot('academic_year_id');
    }
    public function assignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
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
    public function gradesGiven()
    {
        return $this->hasMany(Grade::class, 'teacher_id');
    }

    public function gradesReceived()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }
    public function studentAcademicYears()
    {
        return $this->belongsToMany(AcademicYear::class, 'student_classes', 'user_id', 'academic_year_id')
            ->withTimestamps();
    }
    public function studentGrades(){
        return $this->belongsToMany(AcademicYear::class, 'grade_users', 'user_id', 'academic_year_id');
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
