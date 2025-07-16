<?php


namespace App\Http\Controllers;

use App\Models\TeacherAssignment;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherViewController extends Controller
{
    public function showForStudent(Request $request)
    {
        // Kiểm tra role học sinh
        if (auth()->user()->role !== User::ROLE_STUDENT) {
            abort(403, 'Chỉ học sinh mới được xem trang này');
        }

        $selectedYearId = $request->input('year');

        // Lấy tất cả năm học
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        // Nếu không chọn năm học cụ thể, lấy năm hiện tại
        $selectedYear = $selectedYearId
            ? AcademicYear::find($selectedYearId)
            : AcademicYear::getCurrentYear(auth()->user()->school_id);

        if (!$selectedYear) {
            return back()->with('error', 'Không tìm thấy năm học');
        }

        // Lấy lớp của học sinh trong năm học ĐÃ CHỌN
        $studentClass = StudentClass::where('user_id', auth()->id())
            ->where('academic_year_id', $selectedYear->id)
            ->with('class')
            ->first();

        if (!$studentClass) {
            return view('view_teachers.students', [
                'noClass' => true,
                'selectedYear' => $selectedYear,
                'academicYears' => $academicYears,
                'selectedYearId' => $selectedYearId,
                'class' => null, // Thêm dòng này
                'homeroomTeacher' => null, // Đảm bảo tất cả biến đều được khởi tạo
                'subjectTeachers' => collect()
            ]);
        }

        // Lấy giáo viên cho năm học ĐÃ CHỌN
        $homeroomTeacher = TeacherAssignment::with(['teacher', 'subject'])
            ->where('class_id', $studentClass->class_id)
            ->where('academic_year_id', $selectedYear->id)
            ->where('is_homeroom', true)
            ->first();

        $subjectTeachers = TeacherAssignment::with(['teacher', 'subject'])
            ->where('class_id', $studentClass->class_id)
            ->where('academic_year_id', $selectedYear->id)
            ->where('is_homeroom', false)
            ->orderBy('subject_id')
            ->get();

        return view('view_teachers.students', [
            'homeroomTeacher' => $homeroomTeacher,
            'subjectTeachers' => $subjectTeachers,
            'class' => $studentClass->class,
            'selectedYear' => $selectedYear,
            'academicYears' => $academicYears,
            'selectedYearId' => $selectedYearId ?? $selectedYear->id
        ]);
    }

    public function showForHomeroomTeacher(Request $request)
    {
        // Kiểm tra role giáo viên
        if (auth()->user()->role !== User::ROLE_TEACHER) {
            abort(403, 'Chỉ giáo viên mới được xem trang này');
        }

        $selectedYearId = $request->input('year');

        // Lấy năm học
        $currentYear = $selectedYearId
            ? AcademicYear::where('school_id', auth()->user()->school_id)
                ->findOrFail($selectedYearId)
            : AcademicYear::getCurrentYear(auth()->user()->school_id);

        if (!$currentYear) {
            return back()->with('error', 'Không tìm thấy năm học');
        }

        // Lấy lớp chủ nhiệm
        $homeroomClass = TeacherAssignment::with(['class' => function($query) {
            $query->with('gradeLevel');
        }])
            ->where('teacher_id', auth()->id())
            ->where('academic_year_id', $currentYear->id)
            ->where('is_homeroom', true)
            ->first();

        if (!$homeroomClass) {
            return back()->with('error', 'Bạn không chủ nhiệm lớp nào trong năm học này');
        }

        // Lấy giáo viên bộ môn
        $subjectTeachers = TeacherAssignment::with([
            'teacher' => function($query) {
                $query->select('id', 'full_name', 'email', 'phone');
            },
            'subject' => function($query) {
                $query->select('id', 'name');
            }
        ])
            ->where('class_id', $homeroomClass->class_id)
            ->where('academic_year_id', $currentYear->id)
            ->where('is_homeroom', false)
            ->orderBy('subject_id')
            ->get();

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('view_teachers.teachers', [
            'class' => $homeroomClass->class,
            'subjectTeachers' => $subjectTeachers,
            'currentYear' => $currentYear,
            'academicYears' => $academicYears,
            'selectedYearId' => $selectedYearId
        ]);
    }
}
