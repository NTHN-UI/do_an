<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\QuestionBank;
use App\Models\QuestionBankImport;
use App\Models\QuestionBankOption;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


class QuestionBankImportController extends Controller
{
    public function showImportForm(Request $request)
    {
        if ($request->has('return_to')) {
            session(['import_return_to' => $request->return_to]);
        }

        return view('question_bank.import');
    }

    public function import(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:xlsx,xls']);

        $user = auth()->user();
        $teacherAssignments = TeacherAssignment::where('teacher_id', $user->id)
            ->pluck('subject_id')
            ->unique()
            ->toArray();

        $spreadsheet = IOFactory::load($request->file('import_file'));
        $rows = $spreadsheet->getActiveSheet()->toArray();

        DB::beginTransaction();
        try {
            $importedCount = 0;

            foreach ($rows as $index => $row) {
                if ($index === 0) continue;

                if (count($row) < 10) {
                    throw new \Exception("Dòng {$index} thiếu dữ liệu. Vui lòng kiểm tra lại file mẫu");
                }

                $subjectName = trim($row[2] ?? '');
                $subject = Subject::where('name', $subjectName)->first();

                if (!$subject) {
                    throw new \Exception("Môn học '{$subjectName}' không tồn tại (Dòng {$index})");
                }

                if (!in_array($subject->id, $teacherAssignments)) {
                    throw new \Exception("Bạn không được phân công dạy môn '{$subjectName}' (Dòng {$index})");
                }


                $question = QuestionBank::create([
                    'content' => $row[1] ?? '',
                    'subject_id' => $subject->id,
                    'grade_level_id' => $this->getGradeLevelId($row[3] ?? 10),
                    'teacher_id' => $user->id,
                    'school_id' => $user->school_id
                ]);

                $this->createQuestionOptions(
                    $question->id,
                    $row[5] ?? '',
                    $row[6] ?? '',
                    $row[7] ?? null,
                    $row[8] ?? null,
                    strtoupper($row[9] ?? 'A')
                );
                $importedCount++;

            }

            DB::commit();
            if (session('import_return_to') === 'exam') {
                session()->forget('import_return_to');
                return redirect()->route('exams.create')->with('success', "Import thành công {$importedCount} câu hỏi!")
                    ->with('open_question_bank', true);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
    private function getGradeLevelId($gradeNumber)
    {
        return GradeLevel::where('grade_number', $gradeNumber)->firstOrFail()->id;
    }

    private function createQuestionOptions($questionId, $a, $b, $c = null, $d = null, $correct)
    {
        $correct = strtoupper(trim($correct));
        if (!in_array($correct, ['A', 'B', 'C', 'D'])) {
            throw new \Exception("Đáp án đúng phải là A, B, C hoặc D");
        }

        $options = [
            ['content' => $a, 'is_correct' => ($correct === 'A')],
            ['content' => $b, 'is_correct' => ($correct === 'B')]
        ];

        if (!empty($c)) {
            $options[] = ['content' => $c, 'is_correct' => ($correct === 'C')];
        }

        if (!empty($d)) {
            $options[] = ['content' => $d, 'is_correct' => ($correct === 'D')];
        }

        foreach ($options as $order => $option) {
            QuestionBankOption::create([
                'question_bank_id' => $questionId,
                'content' => $option['content'],
                'is_correct' => $option['is_correct'],
                'order' => $order
            ]);
        }
    }
    public function downloadTemplate()
    {
        $directory = storage_path('app/templates');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory.'/multi_subject_import_template.xlsx';

        if (!file_exists($filePath)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                'STT', 'Nội dung câu hỏi*', 'Môn học*', 'Khối lớp*',
                 'Điểm*', 'Đáp án A*', 'Đáp án B*',
                'Đáp án C', 'Đáp án D', 'Đáp án đúng*'
            ];
            $sheet->fromArray([$headers], null, 'A1');


            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
