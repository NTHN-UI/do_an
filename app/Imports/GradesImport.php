<?php

namespace App\Imports;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
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

    public function __construct($classId, $semesterId, $academicYearId, $teacherId, $schoolId)
    {
        $this->classId = $classId;
        $this->semesterId = $semesterId;
        $this->academicYearId = $academicYearId;
        $this->teacherId = $teacherId;
        $this->schoolId = $schoolId;
    }

    public function collection(Collection $rows)
    {
        // Lấy tên môn học từ sheet name
        $subjectName = $this->getSubjectNameFromSheet();
        $this->currentSubject = Subject::where('name', $subjectName)
            ->where('school_id', $this->schoolId)
            ->firstOrFail();

        $isTextSubject = $this->currentSubject->education_level === 'primary';

        foreach ($rows as $row) {
            // Bỏ qua các dòng không có mã học sinh
            if (empty($row['ma_hs'])) {
                continue;
            }

            $studentCode = $row['ma_hs'];
            $student = User::where('school_auto_id', $studentCode)
                ->where('school_id', $this->schoolId)
                ->where('role', 'student')
                ->first();

            if (!$student) {
                continue;
            }

            // Xử lý điểm số hoặc đánh giá
            if ($isTextSubject) {
                // Môn đạt/chưa đạt
                $this->processTextGrades($row, $student->id);
            } else {
                // Môn nhập điểm số
                $this->processNumericGrades($row, $student->id);
            }
        }
    }

    protected function processTextGrades($row, $studentId)
    {
        $grades = [
            ['test_type' => 'fifteen_minutes', 'value' => $row['danh_gia_1'] ?? null],
            ['test_type' => 'fifteen_minutes', 'value' => $row['danh_gia_2'] ?? null],
            ['test_type' => 'one_period', 'value' => $row['danh_gia_3'] ?? null],
            ['test_type' => 'one_period', 'value' => $row['danh_gia_4'] ?? null],
            ['test_type' => 'semester', 'value' => $row['danh_gia_hk'] ?? null],
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

    protected function processNumericGrades($row, $studentId)
    {
        $grades = [
            ['test_type' => 'fifteen_minutes', 'value' => $row['diem_15p_lan_1'] ?? null],
            ['test_type' => 'fifteen_minutes', 'value' => $row['diem_15p_lan_2'] ?? null],
            ['test_type' => 'one_period', 'value' => $row['diem_1_tiet_lan_1'] ?? null],
            ['test_type' => 'one_period', 'value' => $row['diem_1_tiet_lan_2'] ?? null],
            ['test_type' => 'semester', 'value' => $row['diem_hoc_ky'] ?? null],
        ];

        foreach ($grades as $grade) {
            if (!is_null($grade['value']) && $grade['value'] !== '') {
                $score = (float) $grade['value'];

                // Validate điểm số
                if ($score < 0 || $score > 10) {
                    continue;
                }

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

    protected function getSubjectNameFromSheet()
    {
        // Trong thực tế, cần lấy tên môn học từ sheet đang xử lý
        // Ở đây giả định sheet name chính là tên môn học
        return $this->currentSubject->name;
    }
}
