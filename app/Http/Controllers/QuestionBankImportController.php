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
                if ($index === 0) continue; // Skip header

                // Kiểm tra số lượng cột tối thiểu
                if (count($row) < 10) {
                    throw new \Exception("Dòng {$index} thiếu dữ liệu. Vui lòng kiểm tra lại file mẫu");
                }

                // Validate môn học
                $subjectName = trim($row[2] ?? '');
                $subject = Subject::where('name', $subjectName)->first();

                if (!$subject) {
                    throw new \Exception("Môn học '{$subjectName}' không tồn tại (Dòng {$index})");
                }

                // Kiểm tra phân công giảng dạy
                if (!in_array($subject->id, $teacherAssignments)) {
                    throw new \Exception("Bạn không được phân công dạy môn '{$subjectName}' (Dòng {$index})");
                }

                // Tạo câu hỏi
                $question = QuestionBank::create([
                    'content' => $row[1] ?? '',
                    'subject_id' => $subject->id,
                    'grade_level_id' => $this->getGradeLevelId($row[3] ?? 10), // Mặc định khối 10 nếu không có
                    'teacher_id' => $user->id,
                    'school_id' => $user->school_id
                ]);

                // Thêm đáp án với kiểm tra null
                $this->createQuestionOptions(
                    $question->id,
                    $row[5] ?? '', // Đáp án A
                    $row[6] ?? '', // Đáp án B
                    $row[7] ?? null, // Đáp án C
                    $row[8] ?? null, // Đáp án D
                    strtoupper($row[9] ?? 'A') // Mặc định đáp án A nếu không có
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
        // Validate đáp án đúng
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
        // Đảm bảo thư mục tồn tại
        $directory = storage_path('app/templates');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory.'/multi_subject_import_template.xlsx';

        // Tạo file mới nếu chưa tồn tại
        if (!file_exists($filePath)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Thiết lập tiêu đề
            $headers = [
                'STT', 'Nội dung câu hỏi*', 'Môn học*', 'Khối lớp*',
                 'Điểm*', 'Đáp án A*', 'Đáp án B*',
                'Đáp án C', 'Đáp án D', 'Đáp án đúng*'
            ];
            $sheet->fromArray([$headers], null, 'A1');

            // Lưu file
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
        }

        // Tải file về
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
