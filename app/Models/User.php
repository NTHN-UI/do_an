<?php

namespace App\Models;

use App\Helpers\DateHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    public function school()
    {
        return $this->belongsTo(School::class);
    }

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
    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }
    public function classesForYear($academicYearId)
    {
        return $this->belongsToMany(ClassModel::class, 'student_classes', 'user_id', 'class_id')
            ->wherePivot('academic_year_id', $academicYearId);
    }

    public function getCurrentClass($year){
        return $this->studentClasses()
            ->whereHas('academicYear', function ($query) use ($year) {
                $query->where('year', $year);
            })
            ->first();
    }
    public function classes()
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

    public function studentGrades()
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_users', 'user_id', 'grade_id')
            ->withPivot(['academic_year_id', 'school_id']);
    }
    public function gradeLevels()
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_users', 'user_id', 'grade_id')
            ->withPivot('academic_year_id', 'school_id');
    }
    public function currentAcademicYear()
    {
        return $this->studentGrades()->first()?->pivot?->academic_year_id;
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
    public function isHomeroomTeacherOf(string $id)
    {
        $student = $this->with('studentClasses')->findOrFail($id);
        if ($student->role !== 'student') return false;

        return TeacherAssignment::where('teacher_id', $this->id)
            ->where('class_id', $student->getCurrentClass(DateHelper::getCurrentAcademicYear())->class_id)
            ->where('is_homeroom', 1)
            ->exists();
    }
    public function isHomeroomTeacher($academicYearId = null)
    {
        $query = $this->teacherAssignments()
            ->where('is_homeroom', true);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        } elseif (session()->has('academic_year_id')) {
            $query->where('academic_year_id', session('academic_year_id'));
        }

        return $query->exists();
    }
    public function homeroomTeacher()
    {
        return $this->hasOne(TeacherAssignment::class)
            ->where('is_homeroom', true)
            ->with('teacher');
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
    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id');
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

    public function getAverageOverallGradeForAcademicYear($academicYearId)
    {
        $grades = Grade::whereHas('semester', function($query) use ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        })
            ->where('student_id', $this->id)
            ->where('test_type', 'final')
            ->get();

        if ($grades->isEmpty()) {
            return null;
        }

        $specialSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

        $total = 0;
        $count = 0;

        foreach ($grades as $grade) {
            $subject = $grade->subject;
            if ($subject && !in_array($subject->name, $specialSubjects) && is_numeric($grade->score)) {
                $total += $grade->score;
                $count++;
            }
        }

        return $count > 0 ? round($total / $count, 1) : null;
    }

}
