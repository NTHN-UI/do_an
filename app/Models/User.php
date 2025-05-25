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
        'school_id',
        'subject_id',
        'entry_score',
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
    public function scopeWithGuardianInfo($query)
    {
        return $query->whereNotNull('guardian_email')
            ->where('guardian_email', '!=', '');
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
            ->with('gradeLevel');
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

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function isHomeroomTeacherOfClass($classId)
    {
        return $this->teacherAssignments()
            ->where('class_id', $classId)
            ->where('is_homeroom', true)
            ->exists();
    }
    public function isHomeroomTeacher($academicYearId = null)
    {
        $query = $this->teacherAssignments()
            ->where('is_homeroom', true);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        return $query->exists();
    }
    public function taughtClasses()
    {
        return $this->belongsToMany(ClassModel::class, 'teacher_assignments')
            ->withPivot(['subject_id', 'is_homeroom']);
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

    // Accessors

}
