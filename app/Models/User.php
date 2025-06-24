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

// User.php
    public function academicYears()
    {
        return $this->belongsToMany(AcademicYear::class, 'student_classes', 'user_id', 'academic_year_id')
            ->distinct()
            ->orderBy('start_date', 'desc');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_classes', 'class_id', 'user_id')
            ->withPivot('academic_year_id');
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class, 'student_id');
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

    public function studentGrades()
    {
        return $this->belongsToMany(AcademicYear::class, 'grade_users', 'academic_year_id','grade_id');
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

    public function documents()
    {
        return $this->hasMany(Document::class, 'teacher_id', 'id');
    }

    public function examAssignments()
    {
        return $this->hasMany(ExamAssignment::class, 'student_id');
    }

    public function submissions()
    {
        return $this->hasMany(ExamSubmission::class, 'student_id');
    }

    public function isCurrentTeacher()
    {
        return $this->teacherAssignments()->exists();
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

    public function getAveragesByYear($academicYearId)
    {
        return $this->gradesGiven()
            ->where('academic_year_id', $academicYearId)
            ->selectRaw('AVG(score) as average_score, subject_id')
            ->groupBy('subject_id')
            ->get();
    }

    public function getClassForYear($academicYearId)
    {
        return $this->studentClasses()
            ->wherePivot('academic_year_id', $academicYearId)
            ->first();
    }

    public function getFinalResult($academicYearId)
    {
        $finalGrades = Grade::where('student_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->where('test_type', 'final')
            ->get()
            ->groupBy('semester_id');

        $subjects = Subject::where('school_id', $this->school_id)->get();
        $result = [
            'semester1' => ['subjects' => [], 'average' => 0, 'classification' => ''],
            'semester2' => ['subjects' => [], 'average' => 0, 'classification' => ''],
            'yearly' => ['subjects' => [], 'average' => 0, 'classification' => '']
        ];

        if ($finalGrades->isEmpty()) {
            return $result;
        }

        // Tính điểm từng học kỳ
        foreach ([1, 2] as $semester) {
            $semesterGrades = $finalGrades->get($semester) ?? collect();
            $total = 0;
            $count = 0;

            foreach ($subjects as $subject) {
                $grade = $semesterGrades->where('subject_id', $subject->id)->first();
                $score = $grade ? $grade->score : 0;

                $result["semester$semester"]['subjects'][$subject->id] = $score;

                if ($score > 0) {
                    $total += $score;
                    $count++;
                }
            }

            if ($count > 0) {
                $result["semester$semester"]['average'] = round($total / $count, 1);
                $result["semester$semester"]['classification'] = $this->classifyStudent(
                    $result["semester$semester"]['average'],
                    $result["semester$semester"]['subjects']
                );
            }
        }

        // Tính điểm cả năm
        if ($result['semester1']['average'] > 0 && $result['semester2']['average'] > 0) {
            $yearlyAvg = round(
                ($result['semester1']['average'] + $result['semester2']['average'] * 2) / 3,
                1
            );

            $result['yearly']['average'] = $yearlyAvg;
            $result['yearly']['classification'] = $this->classifyStudent(
                $yearlyAvg,
                array_map(
                    function ($s1, $s2) {
                        return ($s1 + $s2 * 2) / 3; // Điểm môn cả năm
                    },
                    $result['semester1']['subjects'],
                    $result['semester2']['subjects']
                )
            );
        }

        return $result;
    }
    public function getAverageOverallGradeForAcademicYear($academicYearId)
    {
        $average = Grade::where('student_id', $this->id)
            ->where('academic_year_id', $academicYearId)
            ->where('test_type', 'final')
            ->avg('score');

        // Trả về null nếu không có điểm
        return $average !== null ? (float)$average : null;
    }
}
