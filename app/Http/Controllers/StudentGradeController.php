<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentGradeController extends Controller
{
    // StudentGradeController.php

    public function index(Request $request)
    {
        $student = Auth::user();

        // Lấy TẤT CẢ năm học của trường
        $academicYears = AcademicYear::where('school_id', $student->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        // Năm học được chọn
        $selectedYearId = $request->input('academic_year_id');
        $subjects = Subject::where('school_id', $student->school_id)
            ->orderBy('name')
            ->get();

        // Khởi tạo dữ liệu
        $grades = collect();
        $yearlyResults = null;
        $hasData = false;

        if ($selectedYearId) {
            // Lấy tất cả học kỳ của năm học được chọn
            $semesters = Semester::where('academic_year_id', $selectedYearId)
                ->where('school_id', $student->school_id)
                ->orderBy('start_date')
                ->get();

            // Xác định học kỳ 1 và học kỳ 2 (2 học kỳ đầu tiên khi sắp xếp theo ngày bắt đầu)
            $semester1 = $semesters->first();
            $semester2 = $semesters->slice(1)->first();

            if ($semester1 && $semester2) {
                $grades = Grade::where('student_id', $student->id)
                    ->where('academic_year_id', $selectedYearId)
                    ->whereIn('semester_id', [$semester1->id, $semester2->id])
                    ->where('test_type', 'final')
                    ->with(['subject', 'semester'])
                    ->get()
                    ->groupBy(['semester_id', 'subject_id']);

                if ($grades->isNotEmpty()) {
                    $hasData = true;

                    $semester1Grades = Grade::where('student_id', $student->id)
                        ->where('academic_year_id', $selectedYearId)
                        ->where('semester_id', $semester1->id)
                        ->where('test_type', 'final')
                        ->get();

                    $semester2Grades = Grade::where('student_id', $student->id)
                        ->where('academic_year_id', $selectedYearId)
                        ->where('semester_id', $semester2->id)
                        ->where('test_type', 'final')
                        ->get();

                    if ($semester1Grades->isNotEmpty() && $semester2Grades->isNotEmpty()) {
                        $semester1Avg = $semester1Grades->avg('score');
                        $semester2Avg = $semester2Grades->avg('score');
                        $yearlyAverage = round(($semester1Avg + $semester2Avg * 2) / 3, 1);

                        // Tính điểm cả năm cho từng môn
                        $subjectGrades = [];
                        foreach ($subjects as $subject) {
                            $grade1 = $grades[$semester1->id][$subject->id][0] ?? null;
                            $grade2 = $grades[$semester2->id][$subject->id][0] ?? null;

                            $isSpecialSubject = in_array($subject->name, [
                                'Giáo dục quốc phòng và an ninh',
                                'Giáo dục thể chất',
                                'Nghệ thuật'
                            ]);

                            if ($grade1 && $grade2) {
                                if ($isSpecialSubject) {
                                    // Môn đặc biệt: lấy kết quả HK2
                                    $subjectGrades[$subject->id] = [
                                        'value' => $grade2->text_value ?? ($grade2->score >= 5 ? 'Đạt' : 'Chưa đạt'),
                                        'is_special' => true
                                    ];
                                } else {
                                    // Môn thường: tính theo công thức
                                    $subjectGrades[$subject->id] = [
                                        'value' => round(($grade1->score + $grade2->score * 2) / 3, 1),
                                        'is_special' => false
                                    ];
                                }
                            }
                        }

                        $yearlyResults = [
                            'semester1_avg' => round($semester1Avg, 1),
                            'semester2_avg' => round($semester2Avg, 1),
                            'yearly_avg' => $yearlyAverage,
                            'classification' => $this->classifyStudent($yearlyAverage),
                            'subject_grades' => $subjectGrades,
                            'semester1_id' => $semester1->id,
                            'semester2_id' => $semester2->id
                        ];
                    }
                }
            }
        }

        return view('student_grades.index', compact(
            'academicYears',
            'selectedYearId',
            'grades',
            'yearlyResults',
            'hasData',
            'subjects'
        ));
    }
    private function classifyStudent($averageScore)
    {
        if ($averageScore >= 8.0) return 'Giỏi';
        if ($averageScore >= 6.5) return 'Khá';
        if ($averageScore >= 5.0) return 'TB';
        if ($averageScore >= 3.5) return 'Yếu';
        return 'Kém';
    }
    // Hiển thị chi tiết điểm theo học kỳ
    public function detail($academicYearId, $semesterId)
    {
        $student = Auth::user();

        // Kiểm tra năm học hợp lệ
        $academicYear = AcademicYear::where('id', $academicYearId)
            ->where('school_id', $student->school_id)
            ->firstOrFail();

        // Kiểm tra học kỳ hợp lệ và thuộc năm học này
        $semester = Semester::where('id', $semesterId)
            ->where('academic_year_id', $academicYearId)
            ->firstOrFail();

        // Lấy điểm của học sinh
        $grades = Grade::where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->where('academic_year_id', $academicYearId)
            ->with('subject')
            ->get()
            ->groupBy(['subject_id', 'test_type']);

        // Tính điểm trung bình từng môn
        $subjectAverages = [];
        foreach ($grades as $subjectId => $subjectGrades) {
            $subjectAverages[$subjectId] = $this->calculateSubjectAverage($subjectGrades);
        }

        return view('student_grades.detail', compact(
            'academicYear',
            'semester',
            'grades',
            'subjectAverages'
        ));
    }
    private function calculateSubjectAverage($subjectGrades)
    {
        // Kiểm tra nếu là môn đặc biệt và điểm cuối kỳ là "Chưa đạt"
        // Nếu là môn đặc biệt
        if ($subjectGrades['is_special'] ?? false) {
            $semesterValue = $subjectGrades['semester']->text_value ?? null;
            // Trả về text value nếu là môn đặc biệt
            return $semesterValue === 'Đạt' ? 'Đạt' : 'Chưa đạt';
        }
        // Hàm helper để lấy giá trị điểm
        $getScore = function ($item) {
            if (is_object($item) && isset($item->score)) {
                return $item->score;
            }
            return is_numeric($item) ? $item : null;
        };

        // Thay thế toàn bộ đoạn code tính điểm bằng:
        $fifteenMinutes = ($subjectGrades['fifteen_minutes'] ?? collect())->filter(function ($item) use ($getScore) {
            $score = $getScore($item);
            return !is_null($score) && $score >= 0;
        })->values();

        $onePeriod = ($subjectGrades['one_period'] ?? collect())->filter(function ($item) use ($getScore) {
            $score = $getScore($item);
            return !is_null($score) && $score >= 0;
        })->values();

        $semester = ($subjectGrades['semester'] ?? collect())->filter(function ($item) use ($getScore) {
            $score = $getScore($item);
            return !is_null($score) && $score >= 0;
        })->values();

// Tính toán tổng điểm có trọng số
        $total = 0;
        $weights = 0;

// Điểm 15 phút (tối đa 3 điểm, hệ số 1)
        $count = 0;
        foreach ($fifteenMinutes as $item) {
            if ($count >= 3) break;
            $score = is_object($item) ? $item->score : $item;
            $total += $score * 1;
            $weights += 1;
            $count++;
        }

// Điểm 1 tiết (hệ số 2)
        if ($onePeriod->isNotEmpty()) {
            $score = is_object($onePeriod->first()) ? $onePeriod->first()->score : $onePeriod->first();
            $total += $score * 2;
            $weights += 2;
        }

// Điểm cuối kỳ (hệ số 3)
        if ($semester->isNotEmpty()) {
            $score = is_object($semester->first()) ? $semester->first()->score : $semester->first();
            $total += $score * 3;
            $weights += 3;
        }

        return $weights > 0 ? round($total / $weights, 1) : 0;
    }
}
