<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\QuestionBank;
use App\Models\Subject;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{


    public function getQuestions(Request $request)
    {
        $questionIds = $request->input('ids');

        $questions = QuestionBank::with('options')
            ->whereIn('id', $questionIds)
            ->get();

        return response()->json($questions);
    }
    public function filter(Request $request)
    {
        $questions = QuestionBank::with(['subject', 'gradeLevel', 'options'])
            ->when($request->subject_id, function($query) use ($request) {
                return $query->where('subject_id', $request->subject_id);
            })
            ->when($request->grade_level_id, function($query) use ($request) {
                return $query->where('grade_level_id', $request->grade_level_id);
            })
            ->when($request->search, function($query) use ($request) {
                return $query->where('content', 'like', '%'.$request->search.'%');
            })
            ->paginate(10);

        return response()->json([
            'html' => view('question_bank.partials.question_rows', compact('questions'))->render()
        ]);
    }
}
