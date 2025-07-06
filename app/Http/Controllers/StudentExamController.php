<?php

namespace App\Http\Controllers;

use App\Models\ExamAssignment;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StudentExamController extends Controller
{
    public function index()
    {
        $studentId = auth()->id();
        $assignments = ExamAssignment::whereHas('class.students', function($query)  use ($studentId) {
            $query->where('user_id', $studentId);
        })
            ->with(['exam', 'class'])
            ->orderBy('start_time', 'desc')
            ->get();
        $assignments->each(function($assignment) use ($studentId) {
            $assignment->has_result = $assignment->results()->where('student_id', auth()->id())->exists();
            $assignment->is_active = now()->between($assignment->start_time, $assignment->end_time);
            $assignment->is_upcoming = now()->lt($assignment->start_time);
            $assignment->is_expired = now()->gt($assignment->end_time);
        });


        return view('student_exams.index', compact('assignments'));
    }

    public function show(ExamAssignment $assignment)
    {
        if (!$assignment->class->students()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        if (now() < $assignment->start_time) {
            return redirect()->back()->with('error', 'Đề thi chưa mở để làm!');
        }

        if (now() > $assignment->end_time) {
            return redirect()->back()->with('error', 'Đề thi đã hết thời gian làm!');
        }

        if ($assignment->results()->where('student_id', auth()->id())->exists()) {
            return redirect()->route('student_exams.result', $assignment->id);
        }

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
        if (!$assignment->class->students()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        if (now() < $assignment->start_time || now() > $assignment->end_time) {
            return redirect()->back()->with('error', 'Không thể nộp bài ngoài thời gian quy định!');
        }

        if ($assignment->results()->where('student_id', auth()->id())->exists()) {
            return redirect()->route('student_exams.result', $assignment->id);
        }

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

        $finalScore = ($score / $assignment->exam->questions->sum('marks')) * $totalMarks;

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
        $studentId = auth()->id();

        $assignments = ExamAssignment::whereHas('class.students', function($query) use( $studentId) {
            $query->where('user_id', $studentId);
        })
            ->with(['exam.subject', 'exam.gradeLevel', 'class'])
            ->orderBy('start_time', 'desc')
            ->get();
        $assignments->each(function($assignment) use ($studentId) {
            $now = Carbon::now();
            $assignment->has_result = $assignment->results()->where('student_id', $studentId)->exists();
            $assignment->is_active = $now->between($assignment->start_time, $assignment->end_time);
            $assignment->is_upcoming = $now->lt($assignment->start_time);
            $assignment->is_expired = $now->gt($assignment->end_time);
        });

        return view('student_exams.assigned_exams', compact('assignments'));
    }
}
