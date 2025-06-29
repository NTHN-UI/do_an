<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $table = 'grades';
    protected $fillable = ['teacher_id',
        'student_id',
        'subject_id',
        'class_id',
        'academic_year_id',
        'semester_id',
        'test_type',
        'score',
        'school_id',
        'text_value'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }


    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    // Thêm vào Grade model
    public static function validateGradeRequirements($subjectId, $studentId, $semesterId)
    {
        $fifteenMinutesCount = self::where('subject_id', $subjectId)
            ->where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->where('test_type', 'fifteen_minutes')
            ->count();

        $onePeriodCount = self::where('subject_id', $subjectId)
            ->where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->where('test_type', 'one_period')
            ->count();

        $finalCount = self::where('subject_id', $subjectId)
            ->where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->where('test_type', 'semester')
            ->count();

        // Yêu cầu tối thiểu: 3 điểm 15p, 1 điểm 1 tiết, 1 điểm cuối kỳ
        return $fifteenMinutesCount >= 3 && $onePeriodCount >= 1 && $finalCount >= 1;
    }
}
