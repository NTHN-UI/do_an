<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index()
{
    return response()->json([
        'message' => 'Gọi được API exams!'
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'teacher_id' => 'required|integer|exists:users,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'grade_level_id' => 'required|integer|exists:grade_levels,id',
            'questions' => 'required|array|min:1',
            'questions.*.content' => 'required|string',
            'questions.*.options' => 'required|array|size:4',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);
        DB::beginTransaction();
        try {
            $exam = Exam::create($validated);

            foreach ($validated['questions'] as $q) {
                $question = $exam->questions()->create([
                    'content' => $q['content'],
                ]);

                foreach ($q['options'] as $option) {
                    $question->options()->create($option);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Exam created successfully', 'exam_id' => $exam->id], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to create exam', 'details' => $e->getMessage()], 500);
        }
    }
}
