<?php

namespace App\Http\Controllers;

use App\Models\TeacherAssignment;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Auth;

class HomeroomTeacherController extends Controller
{
    public function index()
    {
        $academicYear = DateHelper::getCurrentAcademicYear();

        $assignment = TeacherAssignment::with('class')
            ->where('teacher_id', Auth::id())
            ->whereHas('academicYear', function ($query) use ($academicYear) {
                $query->where('year', $academicYear);
            })
            ->where('is_homeroom', 1)->first();

        $class = $assignment->class;

        $students = $class->students()
            ->where('student_classes.academic_year_id', $class->academic_year_id)
            ->paginate(20);

        return view('homeroom_teacher.index', [
            'class' => $class,
            'students' => $students
        ]);
    }
}
