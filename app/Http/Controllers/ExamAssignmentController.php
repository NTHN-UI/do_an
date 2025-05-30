<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\ClassModel;
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
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('exam_assignments.index', compact('assignments'));
    }
    public function create(Exam $exam)
    {
        // Lấy các lớp mà giáo viên được phân công và phù hợp với khối lớp của đề thi
        $classes = ClassModel::where('grade_level_id', $exam->grade_level_id)
            ->whereHas('teacherAssignments', function($query) {
                $query->where('teacher_id', auth()->id());
            })
            ->get();

        return view('exam_assignments.create', compact('exam', 'classes'));
    }

    public function store(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
        ]);

        $assignment = ExamAssignment::create([
            'exam_id' => $exam->id,
            'class_id' => $validated['class_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'shuffle_exam-assignmentsquestions' => $validated['shuffle_questions'] ?? false,
            'shuffle_options' => $validated['shuffle_options'] ?? false,
        ]);

        // Gửi thông báo đến học sinh (có thể triển khai sau)
        // Notification::send($assignment->class->students, new NewExamAssignment($assignment));

        return redirect()->route('exams.show', $exam->id)
            ->with('success', 'Đề thi đã được giao thành công!');
    }
}
