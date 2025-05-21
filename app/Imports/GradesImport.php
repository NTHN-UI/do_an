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

    protected function processGrades($row, $studentId)
    {
        $isTextSubject = $this->currentSubject->is_text_based;

        if ($isTextSubject) {
            $this->processTextGrades($row, $studentId);
        } else {
            $this->processNumericGrades($row, $studentId);
        }
    }

    protected function processTextGrades($row, $studentId)
    {
        $grades = [
            ['test_type' => 'fifteen_minutes', 'test_number' => 1, 'value' => $row->get('2') ?? null],
            ['test_type' => 'fifteen_minutes', 'test_number' => 2, 'value' => $row->get('3') ?? null],
            ['test_type' => 'fifteen_minutes', 'test_number' => 3, 'value' => $row->get('4') ?? null],
            ['test_type' => 'one_period', 'test_number' => 1, 'value' => $row->get('5') ?? null],
            ['test_type' => 'semester', 'test_number' => 1, 'value' => $row->get('6') ?? null],
        ];

        $fifteenMinPassed = 0;
        $onePeriodPassed = false;
        $semesterGrade = null;

        // Xử lý và lưu điểm 15 phút và 1 tiết
        foreach ($grades as $grade) {
            if ($grade['test_type'] === 'semester') {
                $semesterGrade = $grade['value']; // Lưu tạm giá trị điểm cuối kỳ
                continue;
            }

            // Kiểm tra và đếm bài đạt
            if ($grade['value'] === 'Đạt') {
                if ($grade['test_type'] === 'fifteen_minutes') {
                    $fifteenMinPassed++;
                } elseif ($grade['test_type'] === 'one_period') {
                    $onePeriodPassed = true;
                }
            }

            // Lưu điểm vào database nếu có giá trị
            if ($grade['value'] !== null) {
                Grade::updateOrCreate(
                    [
                        'teacher_id' => $this->teacherId,
                        'student_id' => $studentId,
                        'subject_id' => $this->currentSubject->id,
                        'class_id' => $this->classId,
                        'academic_year_id' => $this->academicYearId,
                        'semester_id' => $this->semesterId,
                        'test_type' => $grade['test_type'],
                        'test_number' => $grade['test_number'],
                        'school_id' => $this->schoolId,
                    ],
                    [
                        'text_value' => $grade['value'],
                        'score' => null
                    ]
                );
            }
        }

        // Kiểm tra điều kiện thi cuối kỳ
        $isEligible = ($fifteenMinPassed >= 2) && $onePeriodPassed;

        // Xử lý điểm cuối kỳ
        $finalSemesterGrade = $isEligible ? $semesterGrade : 'Chưa đạt';

        // Nếu không đủ điều kiện, ghi đè thành "Chưa đạt" bất kể Excel nhập gì
        if (!$isEligible) {
            $finalSemesterGrade = 'Chưa đạt';
        } elseif ($semesterGrade === null) {
            // Nếu đủ điều kiện nhưng không có điểm cuối kỳ
            $finalSemesterGrade = null;
        }
        // Lưu điểm cuối kỳ
        if ($finalSemesterGrade !== null) {
            Grade::updateOrCreate(
                [
                    'teacher_id' => $this->teacherId,
                    'student_id' => $studentId,
                    'subject_id' => $this->currentSubject->id,
                    'class_id' => $this->classId,
                    'academic_year_id' => $this->academicYearId,
                    'semester_id' => $this->semesterId,
                    'test_type' => 'semester',
                    'test_number' => 1,
                    'school_id' => $this->schoolId,
                ],
                [
                    'text_value' => $finalSemesterGrade,
                    'score' => null
                ]
            );
        }

        // Tính và lưu điểm tổng kết (final)
        $finalGrade = $this->calculateFinalTextGrade($grades, $isEligible);
        Grade::updateOrCreate(
            [
                'teacher_id' => $this->teacherId,
                'student_id' => $studentId,
                'subject_id' => $this->currentSubject->id,
                'class_id' => $this->classId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
                'test_type' => 'final',
                'school_id' => $this->schoolId,
            ],
            [
                'text_value' => $finalGrade,
                'score' => null
            ]
        );
    }

    private function normalizeTextGrade($value)
    {
        if ($value === null) {
            return null;
        }

        $value = mb_strtolower(trim($value));

        if (in_array($value, ['đạt', 'dat', 'd', 'đ'])) {
            return 'Đạt';
        }

        if (in_array($value, ['chưa đạt', 'chua dat', 'không đạt', 'khong dat', 'cđ', 'kđ'])) {
            return 'Chưa đạt';
        }

        return null;
    }

    private function calculateFinalTextGrade($grades, $isEligible)
    {
        // Nếu không đủ điều kiện thì tự động "Chưa đạt"
        if (!$isEligible) {
            return 'Chưa đạt';
        }

        // Kiểm tra tất cả các điểm thành phần
        foreach ($grades as $grade) {
            if ($grade['value'] === 'Chưa đạt') {
                return 'Chưa đạt';
            }
        }

        return 'Đạt';
    }

    public function getProcessedGrades()
    {
        return $this->processedGrades;
    }

    protected function processNumericGrades($row, $studentId)
    {
        $grades = collect([
            ['test_type' => 'fifteen_minutes', 'test_number' => 1, 'value' => (float)$row->get('2')],
            ['test_type' => 'fifteen_minutes', 'test_number' => 2, 'value' => (float)$row->get('3')],
            ['test_type' => 'fifteen_minutes', 'test_number' => 3, 'value' => (float)$row->get('4')],
            ['test_type' => 'one_period', 'test_number' => 1, 'value' => (float)$row->get('5')],
            ['test_type' => 'semester', 'test_number' => 1, 'value' => (float)$row->get('6')],
        ]);

        // Lưu các điểm thành phần
        $subjectGrades = [
            'fifteen_minutes' => [],
            'one_period' => null,
            'semester' => null
        ];

        foreach ($grades as $grade) {
            if (is_numeric($grade['value']) && $grade['value'] >= 0 && $grade['value'] <= 10) {
                Grade::updateOrCreate(
                    [
                        'teacher_id' => $this->teacherId,
                        'student_id' => $studentId,
                        'subject_id' => $this->currentSubject->id,
                        'class_id' => $this->classId,
                        'academic_year_id' => $this->academicYearId,
                        'semester_id' => $this->semesterId,
                        'test_type' => $grade['test_type'],
                        'test_number' => $grade['test_number'],
                        'school_id' => $this->schoolId,
                    ],
                    ['score' => $grade['value'],
                        'text_value' => null
                    ]
                );

                // Chuẩn bị dữ liệu để tính điểm TB
                if ($grade['test_type'] == 'fifteen_minutes') {
                    $subjectGrades['fifteen_minutes'][] = $grade['value'];
                } else {
                    $subjectGrades[$grade['test_type']] = $grade['value'];
                }
            }
        }

        // Tính và lưu điểm trung bình học kỳ (final)
        $semesterAvg = $this->calculateSemesterAverage($subjectGrades);
        Grade::updateOrCreate(
            [
                'teacher_id' => $this->teacherId,
                'student_id' => $studentId,
                'subject_id' => $this->currentSubject->id,
                'class_id' => $this->classId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
                'test_type' => 'final',
                'school_id' => $this->schoolId,
            ],
            ['score' => $semesterAvg,
                'text_value' => null
            ]
        );
    }

    private function calculateSemesterAverage($subjectGrades)
    {
        $fifteenMinutes = array_filter($subjectGrades['fifteen_minutes'] ?? [], function ($score) {
            return !is_null($score) && $score >= 0;
        });

        $onePeriod = !is_null($subjectGrades['one_period'] ?? null) ? [$subjectGrades['one_period']] : [];
        $semester = !is_null($subjectGrades['semester'] ?? null) ? [$subjectGrades['semester']] : [];

        $total = 0;
        $weights = 0;

        // Điểm 15 phút (hệ số 1)
        $count = 0;
        foreach ($fifteenMinutes as $score) {
            if ($count >= 3) break;
            $total += $score * 1;
            $weights += 1;
            $count++;
        }

        // Điểm 1 tiết (hệ số 2)
        if (!empty($onePeriod)) {
            $total += $onePeriod[0] * 2;
            $weights += 2;
        }

        // Điểm cuối kỳ (hệ số 3)
        if (!empty($semester)) {
            $total += $semester[0] * 3;
            $weights += 3;
        }

        return $weights > 0 ? round($total / $weights, 1) : 0;
    }
}
