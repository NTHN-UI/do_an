<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'academic_year_id',
        'is_homeroom',
        'school_id'
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function gradeLevel()
    {
        return $this->hasOneThrough(
            GradeLevel::class,
            ClassModel::class,
            'id',
            'id',
            'class_id',
            'grade_level_id'
        );
    }
    public static function getHomeroomTeacher($classId, $academicYearId)
    {
        return self::with(['teacher' => function($query) {
            $query->select('id', 'full_name', 'email', 'phone');
        }])
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_homeroom', true)
            ->first();
    }

    /**
     * Lấy danh sách giáo viên bộ môn của lớp
     */
    public static function getSubjectTeachers($classId, $academicYearId)
    {
        return self::with([
            'teacher' => function($query) {
                $query->select('id', 'full_name', 'email', 'phone');
            },
            'subject' => function($query) {
                $query->select('id', 'name');
            }
        ])
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_homeroom', false)
            ->orderBy('subject_id')
            ->get();
    }

    /**
     * Lấy lớp chủ nhiệm của giáo viên
     */
    public static function getHomeroomClass($teacherId, $academicYearId)
    {
        return self::with(['class' => function($query) {
            $query->with('gradeLevel');
        }])
            ->where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_homeroom', true)
            ->first();
    }

}
