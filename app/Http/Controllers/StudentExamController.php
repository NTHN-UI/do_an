<?php

namespace App\Http\Controllers;

use App\Models\ExamAssignment;
use App\Models\ExamResult;
use Illuminate\Http\Request;

class StudentExamController extends Controller
{
    public function index()
    {
        // Danh sách đề thi được giao cho học sinh
        $assignments = ExamAssignment::whereHas('class.students', function($query) {
            $query->where('user_id', auth()->id());
        })
            ->with(['exam', 'class'])
            ->where('end_time', '>', now())
            ->orderBy('start_time')
            ->get();

        return view('student_exams.index', compact('assignments'));
    }

    public function show(ExamAssignment $assignment)
    {
        // Kiểm tra học sinh có trong lớp được giao đề không
        if (!$assignment->class->students()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        // Kiểm tra thời gian làm bài
        if (now() < $assignment->start_time) {
            return redirect()->back()->with('error', 'Đề thi chưa mở để làm!');
        }

        if (now() > $assignment->end_time) {
            return redirect()->back()->with('error', 'Đề thi đã hết thời gian làm!');
        }

        // Kiểm tra đã làm bài chưa
        if ($assignment->results()->where('student_id', auth()->id())->exists()) {
            return redirect()->route('student_exams.result', $assignment->id);
        }

        // Lấy đề thi và xử lý xáo trộn nếu có
        $exam = $assignment->exam;
        $questions = $exam->questions()->with('options')->get();

        if ($assignment->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        if ($assignment->shuffle_options) {
            $questions->each(function($question) {
                $question->options = $question->options->shuffle();
            });
        }

        return view('student_exams.show', compact('assignment', 'exam', 'questions'));
    }

    public function submit(Request $request, ExamAssignment $assignment)
    {
        // Kiểm tra hợp lệ
        if (!$assignment->class->students()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        if (now() < $assignment->start_time || now() > $assignment->end_time) {
            return redirect()->back()->with('error', 'Không thể nộp bài ngoài thời gian quy định!');
        }

        if ($assignment->results()->where('student_id', auth()->id())->exists()) {
            return redirect()->route('student_exams.result', $assignment->id);
        }

        // Tính điểm
        $answers = $request->input('answers', []);
        $score = 0;
        $totalMarks = $assignment->exam->total_marks;

        foreach ($assignment->exam->questions as $question) {
            if (isset($answers[$question->id])) {
                $selectedOptionId = $answers[$question->id];
                $isCorrect = $question->options()->where('id', $selectedOptionId)->where('is_correct', true)->exists();

                if ($isCorrect) {
                    $score += $question->marks;
                }
            }
        }

        // Tính điểm theo thang điểm của đề
        $finalScore = ($score / $assignment->exam->questions->sum('marks')) * $totalMarks;

        // Lưu kết quả
        ExamResult::create([
            'exam_assignment_id' => $assignment->id,
            'student_id' => auth()->id(),
            'score' => $finalScore,
            'time_taken' => $request->input('time_taken', 0),
            'answers' => $answers,
        ]);

        return redirect()->route('student_exams.result', $assignment->id)
            ->with('success', 'Nộp bài thành công!');
    }

    public function result(ExamAssignment $assignment)
    {
        $result = $assignment->results()->where('student_id', auth()->id())->firstOrFail();
        $exam = $assignment->exam;
        $questions = $exam->questions()->with('options')->get();

        return view('student_exams.result', compact('assignment', 'exam', 'result', 'questions'));
    }
    public function assignedExams()
    {
        // Lấy tất cả các đề thi đã được giao cho học sinh hiện tại
        $assignments = ExamAssignment::whereHas('class.students', function($query) {
            $query->where('user_id', auth()->id());
        })
            ->with(['exam.subject', 'exam.gradeLevel', 'class'])
            ->where('end_time', '>', now()) // Chỉ hiển thị các đề chưa hết hạn
            ->orderBy('start_time', 'asc')
            ->get();

        return view('student_exams.assigned_exams', compact('assignments'));
    }
}
