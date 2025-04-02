<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teacher = User::where('role', 'teacher')->first();
        $currentSemester = Semester::where('is_current', true)->first();

        if (!$currentSemester) {
            return redirect()->back()->with('error', 'Hiện không có học kỳ nào đang hoạt động');
        }

        $assignments = $teacher->teacherAssignments()
            ->with(['class' => function($query) {
                $query->withCount('students');
            }, 'subject'])
            ->where('academic_year_id', $currentSemester->academic_year_id)
            ->get()
            ->groupBy('subject_id');

        return view('grades.index', compact('assignments', 'currentSemester'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(ClassModel $class, Subject $subject, Semester $semester)
    {
        $students = $class->students()->orderBy('full_name')->get();

        // Lấy các loại điểm đã nhập
        $grades = Grade::where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->where('semester_id', $semester->id)
            ->get()
            ->groupBy(['student_id', 'test_type']);

        $testTypes = Grade::TEST_TYPES;

        return view('grades.create', compact(
            'class', 'subject', 'semester',
            'students', 'grades', 'testTypes'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ClassModel $class, Subject $subject, Semester $semester)
    {
        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.*.score' => 'nullable|numeric|min:0|max:10|decimal:0,2',
            'teacher_id' => 'required|exists:users,id' // Thêm validation cho teacher_id
        ]);

        $teacher = User::where('role', 'teacher')->first();

        foreach ($request->grades as $studentId => $studentGrades) {
            foreach ($studentGrades as $testType => $gradeData) {
                if (isset($gradeData['score'])) {
                    Grade::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'semester_id' => $semester->id,
                            'test_type' => $testType,
                        ],
                        [
                            'teacher_id' => $teacher, // Sử dụng teacherId từ request
                            'academic_year_id' => $semester->academic_year_id,
                            'score' => $gradeData['score'],
                        ]
                    );
                }
            }
        }

        return redirect()->route('grades.index')
            ->with('success', 'Nhập điểm thành công!');
    }
    /**
     * Display the specified resource.
     */
    public function show(ClassModel $class, Subject $subject, Semester $semester)
    {
        $students = $class->students()->orderBy('full_name')->get();

        $grades = Grade::where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->where('semester_id', $semester->id)
            ->get()
            ->groupBy(['student_id', 'test_type']);

        // Tính điểm trung bình
        $averages = [];
        foreach ($students as $student) {
            $studentGrades = $grades[$student->id] ?? [];

            $fifteenMin = $studentGrades['fifteen_minutes'][0]->score ?? 0;
            $onePeriod = $studentGrades['one_period'][0]->score ?? 0;
            $semesterTest = $studentGrades['semester'][0]->score ?? 0;

            // Công thức tính điểm theo quy định
            $average = ($fifteenMin + $onePeriod * 2 + $semesterTest * 3) / 6;
            $averages[$student->id] = round($average, 2);
        }

        return view('grades.show', compact(
            'class', 'subject', 'semester',
            'students', 'grades', 'averages'
        ));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
