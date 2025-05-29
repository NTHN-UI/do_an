<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
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
}
