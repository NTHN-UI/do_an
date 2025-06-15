<?php

namespace App\Imports;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
// Bỏ dòng này đi vì chúng ta sẽ không dùng WithHeadingRow nữa
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class GradesImport implements ToCollection, WithCalculatedFormulas // Đã bỏ WithHeadingRow
{
    protected $classId;
    protected $semesterId;
    protected $academicYearId;
    protected $teacherId;
    protected $schoolId;
    protected $currentSubject;

    protected $importedStudentIds = [];
    protected $headerRowIndex = 0;
    protected $isForceUpdate;

    public function __construct($classId, $semesterId, $academicYearId, $teacherId, $schoolId, $currentSubject = null, $isForceUpdate = false)
    {
        $this->classId = $classId;
        $this->semesterId = $semesterId;
        $this->academicYearId = $academicYearId;
        $this->teacherId = $teacherId;
        $this->schoolId = $schoolId;
        $this->currentSubject = $currentSubject;
        $this->isForceUpdate = (bool)$isForceUpdate;
    }

    public function sheets(): array
    {
        return [
            1 => $this,
        ];
    }

    public function collection(Collection $rows)
    {
        $firstRow = $rows[0][0] ?? '';

        if (strpos($firstRow, 'HƯỚNG DẪN NHẬP ĐIỂM') !== false) {
            throw new \Exception("VUI LÒNG MỞ FILE EXCEL VÀ CHỌN SHEET MÔN HỌC ĐỂ IMPORT");
        }

        if (strpos($firstRow, 'THÔNG TIN LỚP') === false) {
            throw new \Exception("FILE KHÔNG ĐÚNG ĐỊNH DẠNG. VUI LÒNG DÙNG FILE MẪU CỦA HỆ THỐNG");
        }
        // Dựa trên GradeTemplateSubjectSheet::headings()
        $metadataStringClass = $rows[0][0] ?? ''; // Dòng 1: THÔNG TIN LỚP: ... (Mã: ID)
        $metadataStringAcademicYear = $rows[1][0] ?? ''; // Dòng 2: NĂM HỌC: ID
        $metadataStringSemester = $rows[2][0] ?? ''; // Dòng 3: HỌC KỲ: ID
        $metadataStringSubject = $rows[3][0] ?? ''; // Dòng 4: MÔN HỌC: ... (Mã: ID)

        // Trích xuất ID cho từng thông tin
        $extractedClassId = $this->extractIdFromMetadataRow($metadataStringClass, 'THÔNG TIN LỚP');
        $extractedAcademicYearId = $this->extractIdFromMetadataRow($metadataStringAcademicYear, 'NĂM HỌC');
        $extractedSemesterId = $this->extractIdFromMetadataRow($metadataStringSemester, 'HỌC KỲ');
        $extractedSubjectId = $this->extractIdFromMetadataRow($metadataStringSubject, 'MÔN HỌC');

        // Cập nhật currentSubject dựa trên ID trích xuất từ Excel
        $this->currentSubject = Subject::find($extractedSubjectId);
        if (!$this->currentSubject) {
            throw new \Exception("Không tìm thấy môn học với ID: {$extractedSubjectId} trong hệ thống.");
        }

        // So sánh các ID trích xuất với các tham số import từ frontend
        if ($this->classId != $extractedClassId ||
            $this->academicYearId != $extractedAcademicYearId ||
            $this->semesterId != $extractedSemesterId ||
            $this->currentSubject->id != $extractedSubjectId) {
            throw new \Exception("Nội dung file Excel không khớp với thông tin import đã chọn trên hệ thống. Vui lòng kiểm tra lại lớp, năm học, học kỳ và môn học.");
        }
        if (!$this->isForceUpdate) {
            $existingGrades = Grade::where([
                'teacher_id' => $this->teacherId,
                'subject_id' => $this->currentSubject->id,
                'class_id' => $this->classId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
                'school_id' => $this->schoolId,
            ])->exists();

            if ($existingGrades) {
                throw new \Exception("Điểm cho môn học này đã được nhập. Bạn có chắc muốn cập nhật lại?");
            }
        }
        // Nếu ở chế độ ghi đè, xóa toàn bộ điểm cũ trước khi import
        else {
            Grade::where([
                'teacher_id' => $this->teacherId,
                'subject_id' => $this->currentSubject->id,
                'class_id' => $this->classId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
                'school_id' => $this->schoolId,
            ])->delete();
        }

        // Dòng tiêu đề dữ liệu học sinh trong Excel là dòng thứ 6 (index 5 trong collection)
        $this->headerRowIndex = 5;

        $headerRow = $rows[$this->headerRowIndex] ?? null;
        if (!$headerRow) {
            throw new \Exception("File Excel không có dòng tiêu đề dữ liệu học sinh ở dòng " . ($this->headerRowIndex + 1) . ".");
        }

        $studentIdColumnIndex = -1;
        foreach ($headerRow as $colIndex => $colValue) {
            if (mb_strtolower(trim($colValue)) === 'mã hs' || mb_strtolower(trim($colValue)) === 'mã học sinh') {
                $studentIdColumnIndex = $colIndex;
                break;
            }
        }

        if ($studentIdColumnIndex === -1) {
            throw new \Exception("Không tìm thấy cột 'Mã HS' trong file Excel ở dòng " . ($this->headerRowIndex + 1) . ". Vui lòng kiểm tra lại định dạng file.");
        }

        $dataRows = $rows->slice($this->headerRowIndex + 1);
        $isTextSubject = $this->currentSubject->is_text_based;

        foreach ($dataRows as $index => $row) {
            $studentId = $row->get($studentIdColumnIndex);
            if (empty($studentId)) {
                continue;
            }

            $student = User::where('id', $studentId)
                ->where('school_id', $this->schoolId)
                ->where('role', 'student')
                ->first();

            if (!$student) {
                Log::warning("Học sinh với mã ID: {$studentId} không tồn tại trong hệ thống (dòng Excel: " . ($this->headerRowIndex + 1 + $index + 1) . "). Bỏ qua hàng.");
                continue;
            }

            if ($isTextSubject) {
                $this->processTextGrades($row, $student->id);
            } else {
                $this->processNumericGrades($row, $student->id);
            }

            $this->importedStudentIds[] = $student->id;
        }
    }

    /**
     * Sửa đổi hàm này để xử lý cả hai dạng: "PREFIX: VALUE (Mã: ID)" và "PREFIX: VALUE"
     */
    protected function extractIdFromMetadataRow($rowContent, $expectedPrefix)
    {
        Log::debug("Extracting ID from: [{$rowContent}] with prefix: {$expectedPrefix}");

        // If we get the guide sheet content by mistake, skip it
        if (str_contains($rowContent, 'HƯỚNG DẪN NHẬP ĐIỂM')) {
            throw new \Exception("Đang đọc nhầm sheet Hướng dẫn. Vui lòng đảm bảo import từ sheet môn học.");
        }

        // Pattern 1: "PREFIX: ... (Mã: ID)"
        $patternWithId = '/' . preg_quote($expectedPrefix, '/') . ':\s*(.*?)\s*\(Mã:\s*(\d+)\)/i';

        // Pattern 2: "PREFIX: ID" (simple number)
        $patternSimpleId = '/' . preg_quote($expectedPrefix, '/') . ':\s*(\d+)/i';

        if (preg_match($patternWithId, $rowContent, $matches)) {
            Log::debug("Matched pattern with ID. ID: " . ($matches[2] ?? 'N/A'));
            return (int)($matches[2] ?? 0);
        } elseif (preg_match($patternSimpleId, $rowContent, $matches)) {
            Log::debug("Matched simple ID pattern. ID: " . ($matches[1] ?? 'N/A'));
            return (int)($matches[1] ?? 0);
        }

        throw new \Exception("Không tìm thấy thông tin '{$expectedPrefix}' hoặc định dạng không đúng.");
    }
    public function getImportedStudentIds()
    {
        return array_unique($this->importedStudentIds);
    }

    protected function createErrorRow($row, $error)
    {
        return [
            'student_code' => $row[0] ?? '', // Sửa lại thành chỉ số cột
            'student_name' => $row[1] ?? '', // Sửa lại thành chỉ số cột
            'subject' => $this->currentSubject->name,
            'test_type' => 'N/A',
            'current_grade' => null,
            'new_grade' => 'N/A',
            'status' => 'error',
            'status_text' => 'Lỗi',
            'error' => $error
        ];
    }


//    protected function processTextGrades($row, $studentId)
//    {
//        // Điều chỉnh lại chỉ số cột cho các điểm số: Cột C là 2, D là 3, E là 4, F là 5, G là 6
//        $grades = [
//            ['test_type' => 'fifteen_minutes', 'test_number' => 1, 'value' => $this->normalizeTextGrade($row->get(2) ?? null)],
//            ['test_type' => 'fifteen_minutes', 'test_number' => 2, 'value' => $this->normalizeTextGrade($row->get(3) ?? null)],
//            ['test_type' => 'fifteen_minutes', 'test_number' => 3, 'value' => $this->normalizeTextGrade($row->get(4) ?? null)],
//            ['test_type' => 'one_period', 'test_number' => 1, 'value' => $this->normalizeTextGrade($row->get(5) ?? null)],
//            ['test_type' => 'semester', 'test_number' => 1, 'value' => $this->normalizeTextGrade($row->get(6) ?? null)],
//        ];
//
//        $fifteenMinPassed = 0;
//        $onePeriodPassed = false;
//        $semesterGrade = null;
//
//        foreach ($grades as $grade) {
//            if ($grade['test_type'] === 'semester') {
//                $semesterGrade = $grade['value'];
//                continue;
//            }
//            if ($grade['value'] === 'Đạt') {
//                if ($grade['test_type'] === 'fifteen_minutes') {
//                    $fifteenMinPassed++;
//                } elseif ($grade['test_type'] === 'one_period') {
//                    $onePeriodPassed = true;
//                }
//            }
//            if ($grade['value'] !== null) {
//                Grade::updateOrCreate(
//                    [
//                        'teacher_id' => $this->teacherId,
//                        'student_id' => $studentId,
//                        'subject_id' => $this->currentSubject->id,
//                        'class_id' => $this->classId,
//                        'academic_year_id' => $this->academicYearId,
//                        'semester_id' => $this->semesterId,
//                        'test_type' => $grade['test_type'],
//                        'test_number' => $grade['test_number'],
//                        'school_id' => $this->schoolId,
//                    ],
//                    [
//                        'text_value' => $grade['value'],
//                        'score' => null
//                    ]
//                );
//            }
//        }
//        $isEligible = ($fifteenMinPassed >= 2) && $onePeriodPassed;
//        $finalSemesterGrade = $isEligible ? $semesterGrade : 'Chưa đạt';
//
//        if (!$isEligible) {
//            $finalSemesterGrade = 'Chưa đạt';
//        } elseif ($semesterGrade === null) {
//            $finalSemesterGrade = null;
//        }
//
//        if ($finalSemesterGrade !== null) {
//            Grade::updateOrCreate(
//                [
//                    'teacher_id' => $this->teacherId,
//                    'student_id' => $studentId,
//                    'subject_id' => $this->currentSubject->id,
//                    'class_id' => $this->classId,
//                    'academic_year_id' => $this->academicYearId,
//                    'semester_id' => $this->semesterId,
//                    'test_type' => 'semester',
//                    'test_number' => 1,
//                    'school_id' => $this->schoolId,
//                ],
//                [
//                    'text_value' => $finalSemesterGrade,
//                    'score' => null
//                ]
//            );
//        }
//
//        $finalGrade = $this->calculateFinalTextGrade($grades, $isEligible);
//        Grade::updateOrCreate(
//            [
//                'teacher_id' => $this->teacherId,
//                'student_id' => $studentId,
//                'subject_id' => $this->currentSubject->id,
//                'class_id' => $this->classId,
//                'academic_year_id' => $this->academicYearId,
//                'semester_id' => $this->semesterId,
//                'test_type' => 'final',
//                'school_id' => $this->schoolId,
//            ],
//            [
//                'text_value' => $finalGrade,
//                'score' => null
//            ]
//        );
//    }
    protected function processTextGrades($row, $studentId)
    {
        // Điều chỉnh lại chỉ số cột cho các điểm số
        $grades = [
            ['test_type' => 'fifteen_minutes', 'test_number' => 1, 'value' => $this->normalizeTextGrade($row->get(2) ?? null)],
            ['test_type' => 'fifteen_minutes', 'test_number' => 2, 'value' => $this->normalizeTextGrade($row->get(3) ?? null)],
            ['test_type' => 'fifteen_minutes', 'test_number' => 3, 'value' => $this->normalizeTextGrade($row->get(4) ?? null)],
            ['test_type' => 'one_period', 'test_number' => 1, 'value' => $this->normalizeTextGrade($row->get(5) ?? null)],
            ['test_type' => 'semester', 'test_number' => 1, 'value' => $this->normalizeTextGrade($row->get(6) ?? null)],
        ];

        $fifteenMinPassed = 0;
        $onePeriodPassed = false;
        $semesterGrade = null;

        foreach ($grades as $grade) {
            if ($grade['value'] !== null) {
                // Thêm điều kiện test_number vào mảng điều kiện
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

            if ($grade['test_type'] === 'semester') {
                $semesterGrade = $grade['value'];
            } elseif ($grade['value'] === 'Đạt') {
                if ($grade['test_type'] === 'fifteen_minutes') {
                    $fifteenMinPassed++;
                } elseif ($grade['test_type'] === 'one_period') {
                    $onePeriodPassed = true;
                }
            }
        }

        // Xử lý điểm cuối kỳ
        $isEligible = ($fifteenMinPassed >= 2) && $onePeriodPassed;
        $finalSemesterGrade = $isEligible ? $semesterGrade : 'Chưa đạt';

        if ($finalSemesterGrade !== null) {
            $semesterConditions = [
                'teacher_id' => $this->teacherId,
                'student_id' => $studentId,
                'subject_id' => $this->currentSubject->id,
                'class_id' => $this->classId,
                'academic_year_id' => $this->academicYearId,
                'semester_id' => $this->semesterId,
                'test_type' => 'semester',
                'test_number' => 1,
                'school_id' => $this->schoolId,
            ];

            if ($this->isForceUpdate) {
                Grade::where($semesterConditions)->delete();
            }

            Grade::updateOrCreate(
                $semesterConditions,
                [
                    'text_value' => $finalSemesterGrade,
                    'score' => null
                ]
            );
        }

        // Xử lý điểm tổng kết
        $finalGrade = $this->calculateFinalTextGrade($grades, $isEligible);
        $finalConditions = [
            'teacher_id' => $this->teacherId,
            'student_id' => $studentId,
            'subject_id' => $this->currentSubject->id,
            'class_id' => $this->classId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'test_type' => 'final',
            'school_id' => $this->schoolId,
        ];

        if ($this->isForceUpdate) {
            Grade::where($finalConditions)->delete();
        }

        Grade::updateOrCreate(
            $finalConditions,
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
        if (!$isEligible) {
            return 'Chưa đạt';
        }
        foreach ($grades as $grade) {
            if ($grade['value'] === 'Chưa đạt') {
                return 'Chưa đạt';
            }
        }
        return 'Đạt';
    }

    public function getProcessedGrades()
    {
        // Có vẻ biến $this->processedGrades không được định nghĩa hay sử dụng
        // Nếu bạn muốn trả về các điểm đã xử lý, bạn cần lưu chúng vào biến này
        return []; // Trả về mảng rỗng nếu không có dữ liệu
    }

//    protected function processNumericGrades($row, $studentId)
//    {
//        // Điều chỉnh lại chỉ số cột cho các điểm số
//        $grades = collect([
//            ['test_type' => 'fifteen_minutes', 'test_number' => 1, 'value' => (float)($row->get(2) ?? 0)],
//            ['test_type' => 'fifteen_minutes', 'test_number' => 2, 'value' => (float)($row->get(3) ?? 0)],
//            ['test_type' => 'fifteen_minutes', 'test_number' => 3, 'value' => (float)($row->get(4) ?? 0)],
//            ['test_type' => 'one_period', 'test_number' => 1, 'value' => (float)($row->get(5) ?? 0)],
//            ['test_type' => 'semester', 'test_number' => 1, 'value' => (float)($row->get(6) ?? 0)],
//        ]);
//
//        $subjectGrades = [
//            'fifteen_minutes' => [],
//            'one_period' => null,
//            'semester' => null
//        ];
//
//        foreach ($grades as $grade) {
//            if (is_numeric($grade['value']) && $grade['value'] >= 0 && $grade['value'] <= 10) {
//                Grade::updateOrCreate(
//                    [
//                        'teacher_id' => $this->teacherId,
//                        'student_id' => $studentId,
//                        'subject_id' => $this->currentSubject->id,
//                        'class_id' => $this->classId,
//                        'academic_year_id' => $this->academicYearId,
//                        'semester_id' => $this->semesterId,
//                        'test_type' => $grade['test_type'],
//                        'test_number' => $grade['test_number'],
//                        'school_id' => $this->schoolId,
//                    ],
//                    ['score' => $grade['value'],
//                        'text_value' => null
//                    ]
//                );
//
//                if ($grade['test_type'] == 'fifteen_minutes') {
//                    $subjectGrades['fifteen_minutes'][] = $grade['value'];
//                } else {
//                    $subjectGrades[$grade['test_type']] = $grade['value'];
//                }
//            }
//        }
//
//        $semesterAvg = $this->calculateSemesterAverage($subjectGrades);
//        Grade::updateOrCreate(
//            [
//                'teacher_id' => $this->teacherId,
//                'student_id' => $studentId,
//                'subject_id' => $this->currentSubject->id,
//                'class_id' => $this->classId,
//                'academic_year_id' => $this->academicYearId,
//                'semester_id' => $this->semesterId,
//                'test_type' => 'final',
//                'school_id' => $this->schoolId,
//            ],
//            ['score' => $semesterAvg,
//                'text_value' => null
//            ]
//        );
//    }
    protected function processNumericGrades($row, $studentId)
    {
        $grades = collect([
            ['test_type' => 'fifteen_minutes', 'test_number' => 1, 'value' => (float)($row->get(2) ?? 0)],
            ['test_type' => 'fifteen_minutes', 'test_number' => 2, 'value' => (float)($row->get(3) ?? 0)],
            ['test_type' => 'fifteen_minutes', 'test_number' => 3, 'value' => (float)($row->get(4) ?? 0)],
            ['test_type' => 'one_period', 'test_number' => 1, 'value' => (float)($row->get(5) ?? 0)],
            ['test_type' => 'semester', 'test_number' => 1, 'value' => (float)($row->get(6) ?? 0)],
        ]);

        $subjectGrades = [
            'fifteen_minutes' => [],
            'one_period' => null,
            'semester' => null
        ];

        foreach ($grades as $grade) {
            if (is_numeric($grade['value']) && $grade['value'] >= 0 && $grade['value'] <= 10) {
                Grade::updateOrCreate([
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
                        'score' => $grade['value'],
                        'text_value' => null
                    ]
                );


                if ($grade['test_type'] == 'fifteen_minutes') {
                    $subjectGrades['fifteen_minutes'][] = $grade['value'];
                } else {
                    $subjectGrades[$grade['test_type']] = $grade['value'];
                }
            }
        }

        // Xử lý điểm tổng kết
        $semesterAvg = $this->calculateSemesterAverage($subjectGrades);

        $finalConditions = [
            'teacher_id' => $this->teacherId,
            'student_id' => $studentId,
            'subject_id' => $this->currentSubject->id,
            'class_id' => $this->classId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'test_type' => 'final',
            'school_id' => $this->schoolId,
        ];

        // Xóa điểm tổng kết cũ nếu ở chế độ ghi đè
        if ($this->isForceUpdate) {
            Grade::where($finalConditions)->delete();
        }

        Grade::updateOrCreate(
            $finalConditions,
            [
                'score' => $semesterAvg,
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

        $count = 0;
        foreach ($fifteenMinutes as $score) {
            if ($count >= 3) break;
            $total += $score * 1;
            $weights += 1;
            $count++;
        }

        if (!empty($onePeriod)) {
            $total += $onePeriod[0] * 2;
            $weights += 2;
        }

        if (!empty($semester)) {
            $total += $semester[0] * 3;
            $weights += 3;
        }

        return $weights > 0 ? round($total / $weights, 1) : 0;
    }
}
