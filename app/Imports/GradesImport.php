<?php

namespace App\Imports;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class GradesImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    protected $classId;
    protected $semesterId;
    protected $academicYearId;
    protected $teacherId;
    protected $schoolId;
    protected $currentSubject;


    protected $importedStudentIds = [];

    public function __construct($classId, $semesterId, $academicYearId, $teacherId, $schoolId, $currentSubject = null)
    {
        $this->classId = $classId;
        $this->semesterId = $semesterId;
        $this->academicYearId = $academicYearId;
        $this->teacherId = $teacherId;
        $this->schoolId = $schoolId;
        $this->currentSubject = $currentSubject;
    }

    public function collection(Collection $rows)
    {
        // Lấy tên môn học từ sheet name
        $subjectName = $this->currentSubject->name;

        $this->currentSubject = Subject::where('name', $subjectName)
            ->where('school_id', $this->schoolId)
            ->first();

        if (!$this->currentSubject) {
            throw new \Exception("Không tìm thấy môn học: $subjectName trong hệ thống. Vui lòng kiểm tra lại tên môn học trong file Excel.");
        }

        $isTextSubject = $this->currentSubject->is_text_based;


        foreach ($rows as $index => $row) {
            // Bước 1: Tìm cột chứa mã học sinh
            $studentCodeColumn = null;
            foreach ($row->toArray() as $columnName => $value) {
                if (str_contains($columnName, 'thong_tin_lop_') && str_contains($columnName, '_ma_')) {
                    $studentCodeColumn = $columnName;
                    break;
                }
            }

            // Nếu không tìm thấy cột mã học sinh thì bỏ qua
            if (!$studentCodeColumn || empty($row->get($studentCodeColumn))) {
                continue;
            }

            $studentId = $row->get($studentCodeColumn); // đây là cột chứa ID học sinh trong Excel

            $student = User::where('id', $studentId)
                ->where('school_id', $this->schoolId)
                ->where('role', 'student')
                ->first();


            if (!$student) {
                continue;
            }

            // Xử lý điểm số
            if ($isTextSubject) {
                $this->processTextGrades($row, $student->id);
            } else {
                $this->processNumericGrades($row, $student->id);
            }

            $this->importedStudentIds[] = $student->id;
        }
    }

    public function getImportedStudentIds()
    {
        return array_unique($this->importedStudentIds);
    }

    protected function createErrorRow($row, $error)
    {
        return [
            'student_code' => $row['ma_hs'] ?? '',
            'student_name' => $row['ho_va_ten'] ?? '',
            'subject' => $this->currentSubject->name,
            'test_type' => 'N/A',
            'current_grade' => null,
            'new_grade' => 'N/A',
            'status' => 'error',
            'status_text' => 'Lỗi',
            'error' => $error
        ];
    }


    protected function processTextGrades($row, $studentId)
    {
        $grades = [
            ['test_type' => 'fifteen_minutes', 'value' => $row->get('2') ?? null],
            ['test_type' => 'fifteen_minutes', 'value' => $row->get('3') ?? null],
            ['test_type' => 'one_period', 'value' => $row->get('4') ?? null],
            ['test_type' => 'one_period', 'value' => $row->get('5') ?? null],
            ['test_type' => 'semester', 'value' => $row->get('6') ?? null],
        ];

        foreach ($grades as $grade) {
            if (!empty($grade['value'])) {
                $score = $grade['value'] === 'Đạt' ? 10 : 0;
                Grade::updateOrCreate(
                    [
                        'teacher_id' => $this->teacherId,
                        'student_id' => $studentId,
                        'subject_id' => $this->currentSubject->id,
                        'class_id' => $this->classId,
                        'academic_year_id' => $this->academicYearId,
                        'semester_id' => $this->semesterId,
                        'test_type' => $grade['test_type'],
                        'school_id' => $this->schoolId,
                    ],
                    [
                        'score' => $score,
                    ]
                );
            }
        }
    }

    protected $processedGrades = [];

    public function getProcessedGrades()
    {
        return $this->processedGrades;
    }

    protected function processNumericGrades($row, $studentId)
    {
        $grades = collect([
            ['test_type' => 'fifteen_minutes', 'value' => (float) $row->get('2')],
            ['test_type' => 'fifteen_minutes', 'value' => (float) $row->get('3')],
            ['test_type' => 'one_period', 'value' => (float) $row->get('4')],
            ['test_type' => 'one_period', 'value' => (float) $row->get('5')],
            ['test_type' => 'semester', 'value' => (float) $row->get('6')],
        ]);

        // Nhóm theo loại điểm (test_type)
        $grouped = $grades
            ->filter(fn($g) => is_numeric($g['value']) && $g['value'] >= 0 && $g['value'] <= 10)
            ->groupBy('test_type');

        foreach ($grouped as $testType => $items) {
            $average = round($items->avg('value'), 2); // Làm tròn 2 chữ số thập phân

            Grade::updateOrCreate(
                [
                    'teacher_id' => $this->teacherId,
                    'student_id' => $studentId,
                    'subject_id' => $this->currentSubject->id,
                    'class_id' => $this->classId,
                    'academic_year_id' => $this->academicYearId,
                    'semester_id' => $this->semesterId,
                    'test_type' => $testType,
                    'school_id' => $this->schoolId,
                ],
                [
                    'score' => $average
                ]
            );
        }
    }

}
