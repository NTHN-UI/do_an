<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\ClassModel;
use App\Models\ExamResult;
use Illuminate\Http\Request;

class ExamAssignmentController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();

        $assignments = ExamAssignment::with(['exam.subject', 'class'])
            ->whereHas('exam', function($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('exam_assignments.index', compact('assignments'));
    }
    public function create(Exam $exam)
    {
        $academicYearId = $exam->academic_year_id;

        $classes = ClassModel::where('grade_level_id', $exam->grade_level_id)
            ->where('academic_year_id', $exam->academic_year_id)
            ->whereHas('teacherAssignments', function($query) {
                $query->where('teacher_id', auth()->id());
            })
            ->with('academicYear')
            ->get();


        return view('exam_assignments.create', compact('exam', 'classes'));
    }

    public function store(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'shuffle_questions' => 'sometimes|boolean',
            'shuffle_options' => 'sometimes|boolean',
        ]);

        $assignment = ExamAssignment::create([
            'exam_id' => $exam->id,
            'class_id' => $validated['class_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'shuffle_questions' => $request->has('shuffle_questions'),
            'shuffle_options' => $request->has('shuffle_optstoreions'),
        ]);



        return redirect()->route('exams.show', $exam->id)
            ->with('success', 'Đề thi đã được giao thành công!');
    }
    public function show(Exam $exam)
    {
        $assignment = ExamAssignment::where('exam_id', $exam->id)
            ->whereHas('class.students', function($query) {
                $query->where('student_id', auth()->id());
            })
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->firstOrFail();
        $questions = $exam->questions()->with('options')->get();

        if ($assignment->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        if ($assignment->shuffle_options) {
            $questions->each(function ($question) {
                $question->options = $question->options->shuffle();
            });
        }

        return view('exams.take', compact('exam', 'questions', 'assignment'));
    }
    public function classResults(ExamAssignment $assignment)
    {
        if ($assignment->exam->teacher_id !== auth()->id()) {
            abort(403);
        }
        $students = $assignment->class->students()
            ->with(['examResults' => function($query) use ($assignment) {
                $query->where('exam_assignment_id', $assignment->id);
            }])
            ->get();

        $results = ExamResult::where('exam_assignment_id', $assignment->id)->get();

        return view('exam_assignments.class_results', [
            'assignment' => $assignment,
            'students' => $students,
            'results' => $results
        ]);
    }
}
