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
    public function index(Request $request)
    {
        $student = Auth::user();

        $academicYears = AcademicYear::where('school_id', $student->school_id)
            ->orderBy('start_date', 'desc')
            ->get();

        $selectedYearId = $request->input('academic_year_id');
        $subjects = Subject::where('school_id', $student->school_id)
            ->orderBy('name')
            ->get();

        $grades = collect();
        $yearlyResults = null;
        $hasData = false;

        if ($selectedYearId) {
            $semesters = Semester::where('academic_year_id', $selectedYearId)
                ->where('school_id', $student->school_id)
                ->orderBy('start_date')
                ->get();

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
                        $semester1Avg = round($semester1Grades->avg('score'), 1);
                        $semester2Avg = round($semester2Grades->avg('score'), 1);
                        $yearlyAverage = round(($semester1Avg + $semester2Avg * 2) / 3, 1);

                        $subjectGrades = [];
                        $subjectScores = []; // Thêm mảng để lưu thông tin các môn học

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
                                    $subjectGrades[$subject->id] = [
                                        'value' => $grade2->text_value ?? ($grade2->score >= 5 ? 'Đạt' : 'Chưa đạt'),
                                        'is_special' => true
                                    ];

                                    // Thêm thông tin môn học vào mảng subjectScores
                                    $subjectScores[$subject->id] = [
                                        'name' => $subject->name,
                                        'yearly_result' => $grade2->text_value ?? ($grade2->score >= 5 ? 'Đạt' : 'Chưa đạt'),
                                        'semester2_text' => $grade2->text_value ?? ($grade2->score >= 5 ? 'Đạt' : 'Chưa đạt'),
                                        'is_special' => true
                                    ];
                                } else {
                                    $yearlyScore = round(($grade1->score + $grade2->score * 2) / 3, 1);
                                    $subjectGrades[$subject->id] = [
                                        'value' => $yearlyScore,
                                        'is_special' => false
                                    ];

                                    // Thêm thông tin môn học vào mảng subjectScores
                                    $subjectScores[$subject->id] = [
                                        'name' => $subject->name,
                                        'average' => $yearlyScore,
                                        'is_special' => false
                                    ];
                                }
                            }
                        }

                        $yearlyResults = [
                            'semester1_avg' => round($semester1Avg, 1),
                            'semester2_avg' => round($semester2Avg, 1),
                            'yearly_avg' => $yearlyAverage,
                            'classification' => $this->classifyStudent($yearlyAverage, $subjectScores, 'yearly'),
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

    public function classifyStudent($averageScore, $subjectScores, $semesterType = 'semester')
    {
        if (!is_numeric($averageScore)) {
            return 'Chưa đạt';
        }

        $specialSubjects = [
            'Giáo dục quốc phòng và an ninh',
            'Giáo dục thể chất',
            'Nghệ thuật'
        ];

        $stats = [
            'special_not_passed' => 0,
            'regular_above8' => 0,
            'regular_above6_5' => 0,
            'regular_above5' => 0,
            'has_below3_5' => false,
            'total_regular' => 0,
            'counted_regular' => 0
        ];

        foreach ($subjectScores as $subjectId => $subjectData) {
            if (!is_array($subjectData)) {
                continue;
            }

            $isSpecial = in_array($subjectData['name'] ?? '', $specialSubjects);

            if ($isSpecial) {
                // Xử lý môn đặc biệt
                $result = ($semesterType === 'yearly')
                    ? ($subjectData['yearly_result'] ?? $subjectData['semester2_text'] ?? 'Chưa đạt')
                    : ($subjectData['display_average'] ?? 'Chưa đạt');

                // Xử lý trường hợp không nhập (để "-")
                if ($result === '-' || $result === '') {
                    $result = 'Chưa đạt';
                }

                $normalizedResult = is_string($result) ? mb_strtolower(trim($result)) : $result;
                $isPassed = in_array($normalizedResult, ['đạt', 'đ', 'd']);

                if (!$isPassed) {
                    $stats['special_not_passed']++;
                }
            } else {
                // Xử lý môn thường
                $score = ($semesterType === 'yearly')
                    ? ($subjectData['average'] ?? 0)
                    : ($subjectData['average'] ?? 0);

                // Xử lý trường hợp không nhập (để "-")
                if ($score === '-' || $score === '') {
                    $score = 0;
                }

                // Ép kiểu về số nếu cần
                $score = is_numeric($score) ? (float)$score : 0;

                if ($score > 0) {
                    $stats['total_regular']++;
                    $stats['counted_regular']++;

                    if ($score >= 8) $stats['regular_above8']++;
                    if ($score >= 6.5) $stats['regular_above6_5']++;
                    if ($score >= 5) $stats['regular_above5']++;
                    if ($score < 3.5) {
                        $stats['has_below3_5'] = true;
                    }
                } elseif ($score == 0) {
                    // Xử lý điểm 0 (bao gồm cả trường hợp không nhập)
                    $stats['has_below3_5'] = true;
                }
            }
        }

        // Phần xếp loại giữ nguyên như trước
        if ($semesterType === 'yearly') {
            // 1. Loại TỐT
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 6.5 &&
                $stats['regular_above8'] >= 6 &&
                $stats['regular_above6_5'] === $stats['counted_regular'] &&
                !$stats['has_below3_5']) {
                return 'Tốt';
            }

            // 2. Loại KHÁ
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 5.0 &&
                $stats['regular_above6_5'] >= 6 &&
                $stats['regular_above5'] === $stats['counted_regular'] &&
                !$stats['has_below3_5']) {
                return 'Khá';
            }

            // 3. Loại ĐẠT
            if ($stats['special_not_passed'] <= 1 &&
                $stats['regular_above5'] >= 6 &&
                !$stats['has_below3_5']) {
                return 'Đạt';
            }
        }
        // XẾP LOẠI HỌC KỲ
        else {
            // 1. Loại TỐT
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 6.5 &&
                $stats['regular_above8'] >= 6 &&
                $stats['regular_above6_5'] == $stats['total_regular'] &&
                !$stats['has_below3_5']) {
                return 'Tốt';
            }

            // 2. Loại KHÁ
            if ($stats['special_not_passed'] === 0 &&
                $averageScore >= 5.0 &&
                $stats['regular_above6_5'] >= 6 &&
                $stats['regular_above5'] == $stats['total_regular'] &&
                !$stats['has_below3_5']) {
                return 'Khá';
            }

            // 3. Loại ĐẠT
            if ($stats['special_not_passed'] <= 1 &&
                $stats['regular_above5'] >= 6 &&
                !$stats['has_below3_5']) {
                return 'Đạt';
            }
        }

        return 'Chưa đạt';
    }

    public function detail($academicYearId, $semesterId)
    {
        $student = Auth::user();

        $academicYear = AcademicYear::where('id', $academicYearId)
            ->where('school_id', $student->school_id)
            ->firstOrFail();

        $semester = Semester::where('id', $semesterId)
            ->where('academic_year_id', $academicYearId)
            ->firstOrFail();

        $grades = Grade::where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->where('academic_year_id', $academicYearId)
            ->with('subject')
            ->get()
            ->groupBy(['subject_id', 'test_type']);

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
        if ($subjectGrades['is_special'] ?? false) {
            $semesterValue = $subjectGrades['semester']->text_value ?? null;
            return $semesterValue === 'Đạt' ? 'Đạt' : 'Chưa đạt';
        }
        $getScore = function ($item) {
            if (is_object($item) && isset($item->score)) {
                return $item->score;
            }
            return is_numeric($item) ? $item : null;
        };

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

        $total = 0;
        $weights = 0;

        $count = 0;
        foreach ($fifteenMinutes as $item) {
            if ($count >= 3) break;
            $score = is_object($item) ? $item->score : $item;
            $total += $score * 1;
            $weights += 1;
            $count++;
        }

        if ($onePeriod->isNotEmpty()) {
            $score = is_object($onePeriod->first()) ? $onePeriod->first()->score : $onePeriod->first();
            $total += $score * 2;
            $weights += 2;
        }

        if ($semester->isNotEmpty()) {
            $score = is_object($semester->first()) ? $semester->first()->score : $semester->first();
            $total += $score * 3;
            $weights += 3;
        }

        return $weights > 0 ? round($total / $weights, 1) : 0;
    }
}
