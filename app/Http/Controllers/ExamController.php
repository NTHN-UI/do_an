<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // import Log

class ExamController extends Controller
{
    public function index()
    {
        Log::info('API exams accessed - index method');
        return response()->json("Gọi được API exams!");
    }

    public function store(Request $request)
    {
        Log::info('Attempt to create exam', ['request' => $request->all()]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'teacher_id' => 'required|integer|exists:users,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'grade_level_id' => 'required|integer|exists:grade_levels,id',
            'exam_type_id' => 'required|integer|exists:exam_types,id',
            'questions' => 'required|array|min:1',
            'questions.*.content' => 'required|string',
            'questions.*.options' => 'required|array|size:4',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            $exam = Exam::create($validated);
            Log::info('Exam created', ['exam_id' => $exam->id]);

            foreach ($validated['questions'] as $q) {
                $question = $exam->questions()->create([
                    'content' => $q['content'],
                ]);
                Log::info('Question created', ['question_id' => $question->id]);

                foreach ($q['options'] as $option) {
                    $question->options()->create($option);
                }
            }

            DB::commit();
            Log::info('Transaction committed for exam creation', ['exam_id' => $exam->id]);
            return response()->json(['message' => 'Exam created successfully', 'exam_id' => $exam->id], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create exam', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to create exam', 'details' => $e->getMessage()], 500);
        }
    }
}
