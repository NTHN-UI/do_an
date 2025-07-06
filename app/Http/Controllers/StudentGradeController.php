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
                        $semester1Avg = $semester1Grades->avg('score');
                        $semester2Avg = $semester2Grades->avg('score');
                        $yearlyAverage = round(($semester1Avg + $semester2Avg * 2) / 3, 1);

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
                                    $subjectGrades[$subject->id] = [
                                        'value' => $grade2->text_value ?? ($grade2->score >= 5 ? 'Đạt' : 'Chưa đạt'),
                                        'is_special' => true
                                    ];
                                } else {
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
