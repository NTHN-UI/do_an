<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\ExamImport;
use App\Models\Subject;
use App\Models\GradeLevel;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    private function getValidationRules(bool $isUpdate = false, int $examId = null): array
    {
        $teacherId = auth()->id();
        $schoolId = auth()->user()->school_id;

        $rules = [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('exams')->where(function ($query) use ($teacherId) {
                    return $query->where('teacher_id', $teacherId);
                })->ignore($examId)
            ],
            'subject_id' => [
                'required',
            ],
            'grade_level_id' => [
                'required',
            ],
            'academic_year_id' => [
                'required',
            ],
            'semester_id' => [
                'required',

            ],
            'test_type' => [
                'required',
                'in:fifteen_minutes,one_period'
            ],
            'total_marks' => [
                'required',
                'numeric',
                'min:1'
            ],
            'duration_override' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'questions' => [
                'required',
                'array',
                'min:1'
            ],
            'questions.*.content' => [
                'required',
                'string'
            ],
            'questions.*.marks' => [
                'required',
                'numeric',
            ],
            'questions.*.options' => [
                'required',
                'array',
            ],
            'questions.*.options.*.content' => [
                'required',
                'string'
            ],
            'questions.*.correct_option' => [
                'required',
            ],
            'import_file' => [
                'nullable',
                'file',
                'mimes:docx',
                'max:10240'
            ]
        ];

        return $rules;
    }
    private function getValidationMessages(): array
    {
        return [
            'title.required' => 'Tên bài kiểm tra không được để trống',
            'title.max' => 'Tên bài kiểm tra không được vượt quá 255 ký tự',
            'subject_id.required' => 'Môn học không được để trống',
            'grade_level_id.required' => 'Khối lớp không được để trống',
            'academic_year_id.required' => 'Năm học không được để trống',
            'semester_id.required' => 'Học kỳ không được để trống',
            'test_type.required' => 'Loại đề thi không được để trống',
            'total_marks.required' => 'Tổng điểm không được để trống',
            'total_marks.numeric' => 'Tổng điểm phải là số',
            'questions.required' => 'Vui lòng thêm ít nhất 1 câu hỏi',
            'questions.*.content.required' => 'Nội dung câu hỏi không được để trống',
            'questions.*.marks.required' => 'Điểm câu hỏi không được để trống',
            'questions.*.marks.numeric' => 'Điểm câu hỏi phải là số',
            'questions.*.options.required' => 'Vui lòng thêm đáp án cho câu hỏi',
            'questions.*.options.*.content.required' => 'Nội dung đáp án không được để trống',
            'questions.*.correct_option.required' => 'Vui lòng chọn đáp án đúng',
            'import_file.mimes' => 'Chỉ chấp nhận file Word (.docx)',
            'import_file.max' => 'Kích thước file tối đa là 10MB'
        ];
    }
    private function validateExamData(Request $request, bool $isUpdate = false, Exam $exam = null)
    {
        $validator = Validator::make(
            $request->all(),
            $this->getValidationRules($isUpdate, $exam ? $exam->id : null),
            $this->getValidationMessages()
        );

        $validator->after(function ($validator) use ($request) {
            $totalMarks = $request->input('total_marks');
            $questions = $request->input('questions', []);

            $sumQuestionMarks = array_reduce($questions, function ($carry, $question) {
                return $carry + ($question['marks'] ?? 0);
            }, 0);

            if (abs($sumQuestionMarks - $totalMarks) > 0.01) {
                $validator->errors()->add(
                    'total_marks',
                    'Tổng điểm các câu hỏi không khớp với tổng điểm bài kiểm tra'
                );
            }

            foreach ($questions as $index => $question) {
                $options = $question['options'] ?? [];
                $correctOption = $question['correct_option'] ?? null;

                if ($correctOption === null || !isset($options[$correctOption])) {
                    $validator->errors()->add(
                        "questions.$index.correct_option",
                        "Câu hỏi #" . ($index + 1) . " phải có ít nhất 1 đáp án đúng"
                    );
                }
            }
        });

        return $validator;
    }

    public function index()
    {
        $exams = Exam::with(['subject', 'gradeLevel', 'academicYear', 'semester'])
            ->where('teacher_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('exams.index', compact('exams'));
    }

    public function create()
    {
        $assignedSubjects = TeacherAssignment::where('teacher_id', auth()->id())
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique()
            ->filter();

        $assignedGradeLevels = TeacherAssignment::where('teacher_id', auth()->id())
            ->with('class.gradeLevel')
            ->get()
            ->pluck('class.gradeLevel')
            ->unique()
            ->filter();

        $assignedAcademicYears = TeacherAssignment::where('teacher_id', auth()->id())
            ->with('academicYear')
            ->get()
            ->pluck('academicYear')
            ->unique()
            ->filter();

        $semesters = Semester::whereIn('academic_year_id', $assignedAcademicYears->pluck('id'))
            ->where('school_id', auth()->user()->school_id)
            ->get();

        $questionBanks = QuestionBank::where(function($query) {
            $query->where('teacher_id', auth()->id())
                ->orWhere('school_id', auth()->user()->school_id);
        })->with('options')->get();


        return view('exams.create', [
            'subjects' => $assignedSubjects,
            'gradeLevels' => $assignedGradeLevels,
            'academicYears' => $assignedAcademicYears,
            'semesters' => $semesters,
            'questionBanks' => $questionBanks
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('questions') && is_string($request->questions)) {
            $request->merge([
                'questions' => json_decode($request->questions, true)
            ]);
        }
        $validated = $this->validateRequest($request);

        $exam = Exam::create($this->prepareExamData($validated));

        if ($request->hasFile('import_file')) {
            $this->importFromWord($exam, $request->file('import_file'));
        }

        if (!empty($validated['question_bank_ids'])) {
            $this->addQuestionsFromBank($exam, $validated['question_bank_ids']);
        }

        if (!empty($validated['questions'])) {
            $this->addManualQuestions($exam, $validated['questions']);
        }

        return redirect()->route('exams.show', $exam->id)
            ->with('success', 'Đề thi đã được tạo thành công!');
    }

    public function show(Exam $exam)
    {

        $exam->load(['questions.options', 'subject', 'gradeLevel',
            'academicYear', 'semester', 'teacher']);

        return view('exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $subjects = TeacherAssignment::where('teacher_id', auth()->id())
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique()
            ->filter();

        $gradeLevels = TeacherAssignment::where('teacher_id', auth()->id())
            ->with('class.gradeLevel')
            ->get()
            ->pluck('class.gradeLevel')
            ->unique()
            ->filter();

        $academicYears = TeacherAssignment::where('teacher_id', auth()->id())
            ->with('academicYear')
            ->get()
            ->pluck('academicYear')
            ->unique()
            ->filter();

        $semesters = Semester::whereIn('academic_year_id', $academicYears->pluck('id'))
            ->where('school_id', auth()->user()->school_id)
            ->get();

        $questionBanks = QuestionBank::where(function($query) {
            $query->where('teacher_id', auth()->id())
                ->orWhere('school_id', auth()->user()->school_id);
        })->with('options')->get();

        $exam->load(['questions.options']);

        return view('exams.edit', compact(
            'exam', 'subjects', 'gradeLevels', 'academicYears',
            'semesters', 'questionBanks'
        ));
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $this->validateRequest($request, $exam);

        $exam->update($this->prepareExamData($validated));

        if ($request->has('replace_questions') || !$exam->questions()->exists()) {
            $exam->questions()->delete();

            if (!empty($validated['questions'])) {
                $this->addManualQuestions($exam, $validated['questions']);
            }
        } else {
            $this->updateExistingQuestions($exam, $validated['questions'] ?? []);
        }

        return redirect()->route('exams.show', $exam->id)
            ->with('success', 'Đề thi đã được cập nhật!');
    }
    private function updateExistingQuestions(Exam $exam, array $questionsData)
    {
        $existingQuestionIds = $exam->questions->pluck('id')->toArray();
        $updatedQuestionIds = [];

        foreach ($questionsData as $questionData) {
            if (isset($questionData['id'])) {
                $question = $exam->questions()->find($questionData['id']);
                if ($question) {
                    $question->update([
                        'content' => $questionData['content'],
                        'marks' => $questionData['marks'] ?? 1,
                    ]);

                    $this->updateQuestionOptions($question, $questionData['options'], $questionData['correct_option']);
                    $updatedQuestionIds[] = $question->id;
                }
            } else {
                $this->addManualQuestions($exam, [$questionData]);
            }
        }

        $toDelete = array_diff($existingQuestionIds, $updatedQuestionIds);
        if (!empty($toDelete)) {
            $exam->questions()->whereIn('id', $toDelete)->delete();
        }
    }

    private function updateQuestionOptions($question, $optionsData, $correctOptionIndex)
    {
        $existingOptionIds = $question->options->pluck('id')->toArray();
        $updatedOptionIds = [];

        foreach ($optionsData as $index => $optionData) {
            if (isset($optionData['id'])) {
                $option = $question->options()->find($optionData['id']);
                if ($option) {
                    $option->update([
                        'content' => $optionData['content'],
                        'is_correct' => $index == $correctOptionIndex,
                    ]);
                    $updatedOptionIds[] = $option->id;
                }
            } else {
                $question->options()->create([
                    'content' => $optionData['content'],
                    'is_correct' => $index == $correctOptionIndex,
                    'order' => $index,
                ]);
            }
        }

        $toDelete = array_diff($existingOptionIds, $updatedOptionIds);
        if (!empty($toDelete)) {
            $question->options()->whereIn('id', $toDelete)->delete();
        }
    }
    public function getQuestions(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:question_banks,id'
        ]);

        $questions = QuestionBank::whereIn('id', $request->ids)
            ->with('options')
            ->get()
            ->map(function($question) {
                return [
                    'id' => $question->id,
                    'content' => $question->content,
                    'marks' => $question->default_marks ?? 1,
                    'options' => $question->options->map(function($option) {
                        return [
                            'id' => $option->id,
                            'content' => $option->content,
                            'is_correct' => $option->is_correct
                        ];
                    })->toArray()
                ];
            });

        return response()->json($questions);
    }
    public function destroy(Exam $exam)
    {

        $exam->delete();

        return redirect()->route('exams.index')
            ->with('success', 'Đề thi đã được xóa!');
    }

    public function publish(Exam $exam)
    {

        $exam->update(['is_published' => true]);

        return back()->with('success', 'Đề thi đã được xuất bản!');
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'test_type' => 'required|in:fifteen_minutes,one_period',
            'total_marks' => 'required|integer|min:1',
            'duration_override' => 'nullable|integer|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.content' => 'required|string',
            'questions.*.marks' => 'required|numeric|min:0.1',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.content' => 'required|string',
            'questions.*.correct_option' => 'required|integer|min:0',
        ]);

        $subject = Subject::find($validated['subject_id']);
        $gradeLevel = GradeLevel::find($validated['grade_level_id']);
        $academicYear = AcademicYear::find($validated['academic_year_id']);
        $semester = Semester::find($validated['semester_id']);

        $questions = collect($validated['questions'])->map(function($question, $index) {
            $question['number'] = $index + 1;
            $question['options'] = collect($question['options'])->map(function($option, $optIndex) use ($question) {
                return [
                    'letter' => chr(65 + $optIndex),
                    'content' => $option['content'],
                    'is_correct' => $optIndex == $question['correct_option']
                ];
            });
            return $question;
        });
        $exam = null;
        if ($request->has('exam_id')) {
            $exam = Exam::find($request->exam_id);
        }

        return view('exams.preview', [
            'examData' => $validated,
            'subject' => $subject,
            'gradeLevel' => $gradeLevel,
            'academicYear' => $academicYear,
            'semester' => $semester,
            'questions' => $questions,
            'isPreview' => true,
            'exam' => $exam,
            'duration_override' => $exam->duration_override ?? null


        ]);
    }



    private function validateRequest(Request $request, $exam = null)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::exists('teacher_assignments', 'subject_id')->where(function($query) {
                    $query->where('teacher_id', auth()->id());
                })
            ],
            'grade_level_id' => [
                'required',
                'exists:grade_levels,id',
                Rule::exists('grade_levels', 'id')->where(function($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
            ],
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
                Rule::exists('academic_years', 'id')->where(function($query) {
                    $query->where('school_id', auth()->user()->school_id);
                })
            ],
            'semester_id' => [
                'required',
                'exists:semesters,id',
                Rule::exists('semesters', 'id')->where(function($query) use ($request) {
                    $query->where('academic_year_id', $request->academic_year_id)
                        ->where('school_id', auth()->user()->school_id);
                })
            ],
            'total_marks' => 'required|integer|min:1',
            'duration_override' => 'nullable|integer|min:1',
            'is_template' => 'nullable|boolean',

            'questions' => 'required_without_all:import_file,question_bank_ids|array|min:1',
            'questions.*.content' => 'required_without_all:import_file,question_bank_ids|string',
            'questions.*.marks' => 'required_without_all:import_file,question_bank_ids|integer|min:1',
            'questions.*.options' => 'required_without_all:import_file,question_bank_ids|array|min:2',
            'questions.*.options.*.content' => 'required_without_all:import_file,question_bank_ids|string',
            'questions.*.correct_option' => 'required_without_all:import_file,question_bank_ids|integer|min:0',

            'import_file' => 'nullable|file|mimes:docx|max:10240',

            'question_bank_ids' => 'nullable|array',
            'question_bank_ids.*' => [
                'exists:question_banks,id',
                Rule::exists('question_banks', 'id')->where(function($query) use ($request) {
                    $query->where('subject_id', $request->subject_id)
                        ->where('grade_level_id', $request->grade_level_id)
                        ->where(function($q) {
                            $q->where('teacher_id', auth()->id())
                                ->orWhere('school_id', auth()->user()->school_id);
                        });
                })
            ],

            'replace_questions' => 'nullable|boolean'
        ]);
    }

    private function prepareExamData($validated)
    {
        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'subject_id' => $validated['subject_id'],
            'grade_level_id' => $validated['grade_level_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id' => $validated['semester_id'],
            'total_marks' => $validated['total_marks'],
            'duration_override' => $validated['duration_override'] ?? null,
            'is_template' => $validated['is_template'] ?? false,
            'teacher_id' => auth()->id(),
        ];
    }

        private function importFromWord(Exam $exam, $file)
        {

            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $path = $file->store('exam_imports');

            $import = $exam->imports()->create([
                'file_path' => $path,
                'original_name' => $originalName,
                'file_size' => $fileSize,
            ]);

            try {
                $phpWord = IOFactory::load(storage_path('app/' . $path));
                $sections = $phpWord->getSections();

                $questions = [];
                $currentQuestion = null;

                foreach ($sections as $section) {
                    $elements = $section->getElements();

                    foreach ($elements as $element) {
                        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                            $text = $this->getElementText($element);

                            if (preg_match('/^(Câu\s*\d+|\d+\.)\s*(.+)/', $text, $matches)) {
                                if ($currentQuestion) {
                                    $questions[] = $currentQuestion;
                                }

                                $currentQuestion = [
                                    'content' => $matches[2],
                                    'marks' => 1,
                                    'options' => [],
                                    'correct_option' => 0
                                ];
                            }
                            elseif (preg_match('/^([A-D])\.\s*(.+)/', $text, $matches) && $currentQuestion) {
                                $optionIndex = ord($matches[1]) - ord('A');
                                $currentQuestion['options'][$optionIndex] = [
                                    'content' => $matches[2],
                                    'is_correct' => false
                                ];

                                if (strpos($matches[2], '(*)') !== false) {
                                    $currentQuestion['correct_option'] = $optionIndex;
                                    $currentQuestion['options'][$optionIndex]['content'] = str_replace('(*)', '', $matches[2]);
                                }
                            }
                        }
                    }
                }

                if ($currentQuestion) {
                    $questions[] = $currentQuestion;
                }

                $this->addManualQuestions($exam, $questions);

                $import->update([
                    'import_status' => 'completed',
                    'import_log' => 'Import thành công ' . count($questions) . ' câu hỏi'
                ]);
            } catch (\Exception $e) {
                $import->update([
                    'import_status' => 'failed',
                    'import_log' => 'Lỗi: ' . $e->getMessage()
                ]);

                throw $e;
            }
        }

    public function previewWord(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:docx|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $phpWord = IOFactory::load($file->getRealPath());
            $sections = $phpWord->getSections();

            $questions = [];
            $currentQuestion = null;

            foreach ($sections as $section) {
                $elements = $section->getElements();

                foreach ($elements as $element) {
                    if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        $text = $this->getElementText($element);

                        if (preg_match('/^(Câu\s*\d+|\d+\.)\s*(.+)/', $text, $matches)) {
                            if ($currentQuestion) {
                                $questions[] = $currentQuestion;
                            }

                            $currentQuestion = [
                                'content' => trim($matches[2]),
                                'marks' => 1,
                                'options' => [],
                                'correct_option' => null
                            ];
                        }
                        elseif (preg_match('/^([A-D])\.\s*(.+)/i', $text, $matches) && $currentQuestion) {
                            $optionIndex = ord(strtoupper($matches[1])) - ord('A');
                            $optionContent = trim($matches[2]);
                            $isCorrect = strpos($optionContent, '(*)') !== false;

                            if ($isCorrect) {
                                $currentQuestion['correct_option'] = $optionIndex;
                                $optionContent = str_replace('(*)', '', $optionContent);
                            }

                            $currentQuestion['options'][$optionIndex] = [
                                'content' => $optionContent,
                                'is_correct' => $isCorrect
                            ];
                        }
                    }
                }
            }

            if ($currentQuestion) {
                $questions[] = $currentQuestion;
            }

            return response()->json([
                'success' => true,
                'questions' => $questions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đọc file Word: ' . $e->getMessage()
            ], 500);
        }
    }


    private function getElementText($element)
    {
        $text = '';
        foreach ($element->getElements() as $child) {
            if ($child instanceof \PhpOffice\PhpWord\Element\Text) {
                $text .= $child->getText();
            }
        }
        return trim($text);
    }

    private function addQuestionsFromBank(Exam $exam, array $questionBankIds)
    {
        $questions = QuestionBank::whereIn('id', $questionBankIds)
            ->with('options')
            ->get();

        foreach ($questions as $bankQuestion) {
            $question = $exam->questions()->create([
                'question_bank_id' => $bankQuestion->id,
                'content' => $bankQuestion->content,
                'marks' => 1,
            ]);

            foreach ($bankQuestion->options as $option) {
                $question->options()->create([
                    'content' => $option->content,
                    'is_correct' => $option->is_correct,
                    'order' => $option->order,
                ]);
            }
        }
    }

    private function addManualQuestions(Exam $exam, array $questionsData)
    {
        foreach ($questionsData as $questionData) {
            $question = $exam->questions()->create([
                'content' => $questionData['content'],
                'marks' => $questionData['marks'] ?? 1,
            ]);

            foreach ($questionData['options'] as $index => $optionData) {
                $question->options()->create([
                    'content' => $optionData['content'],
                    'is_correct' => $index == $questionData['correct_option'],
                    'order' => $index,
                ]);
            }
        }
    }
    public function getSemestersByYear(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $semesters = Semester::where('academic_year_id', $request->academic_year_id)
            ->where('school_id', auth()->user()->school_id)
            ->get(['id', 'name']);

        return response()->json($semesters);
    }
}
