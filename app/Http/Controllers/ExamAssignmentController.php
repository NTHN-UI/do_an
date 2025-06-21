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
            'shuffle_questions' => 'sometimes|boolean',
            'shuffle_options' => 'sometimes|boolean',
        ]);

        $assignment = ExamAssignment::create([
            'exam_id' => $exam->id,
            'class_id' => $validated['class_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'shuffle_questions' => $request->has('shuffle_questions'),
            'shuffle_options' => $request->has('shuffle_options'),
        ]);

        // Gửi thông báo đến học sinh (có thể triển khai sau)
        // Notification::send($assignment->class->students, new NewExamAssignment($assignment));

        return redirect()->route('exams.show', $exam->id)
            ->with('success', 'Đề thi đã được giao thành công!');
    }
    public function show(Exam $exam)
    {
        $assignment = ExamAssignment::where('exam_id', $exam->id)
            ->where('class_id', auth()->user()->class_id)
            ->firstOrFail();

        $questions = $exam->questions()->with('options')->get();

        // Xáo trộn câu hỏi nếu được cấu hình
        if ($assignment->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        // Xáo trộn đáp án trong từng câu hỏi nếu được cấu hình
        if ($assignment->shuffle_options) {
            $questions->each(function ($question) {
                $question->options = $question->options->shuffle();
            });
        }

        return view('exams.take', compact('exam', 'questions', 'assignment'));
    }
    // Thêm vào ExamAssignmentController
    public function classResults(ExamAssignment $assignment)
    {
        // Kiểm tra quyền
        if ($assignment->exam->teacher_id !== auth()->id()) {
            abort(403);
        }

        // Lấy danh sách học sinh với kết quả
        $students = $assignment->class->students()
            ->with(['examResults' => function($query) use ($assignment) {
                $query->where('exam_assignment_id', $assignment->id);
            }])
            ->get();

        // Thêm dòng này để tạo biến $results
        $results = ExamResult::where('exam_assignment_id', $assignment->id)->get();

        return view('exam_assignments.class_results', [
            'assignment' => $assignment,
            'students' => $students,
            'results' => $results // Thêm biến này
        ]);
    }
}
