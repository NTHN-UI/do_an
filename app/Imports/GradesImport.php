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

            $isTextSubject = $this->currentSubject->education_level === 'secondary';

            foreach ($rows as $row) {
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
        protected $processedGrades = [];

        public function getProcessedGrades()
        {
            return $this->processedGrades;
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

                    $gradeRecord = Grade::updateOrCreate(
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

                            $this->processedGrades[] = [
                                'student_id' => $studentId,
                                'subject_id' => $this->currentSubject->id,
                                'test_type' => $grade['test_type'],
                                'score' => $score
                            ]
                    );

                }
            }
        }
    }
