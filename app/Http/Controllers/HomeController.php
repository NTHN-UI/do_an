<?php

    namespace App\Http\Controllers;

    use App\Models\AcademicYear;
    use App\Models\ClassModel;
    use App\Models\GradeLevel;
    use App\Models\School;
    use App\Models\Semester;
    use App\Models\User;
    use Illuminate\Http\Request;
    class HomeController extends Controller
    {
        public function index(Request $request)
        {
            if (auth()->user()->isSuperAdmin()) {
                // Nếu là super admin và đã chọn trường
                if ($request->has('school_id')) {
                    return $this->schoolDashboard($request, $request->school_id);
                }
                // Nếu chưa chọn trường thì hiển thị danh sách trường
                return $this->superAdminDashboard($request);
            } else {
                // Nếu là school admin thì xem thống kê trường của mình
                return $this->schoolDashboard($request, auth()->user()->school_id);
            }
        }

        private function superAdminDashboard(Request $request)
        {
            $schools = School::all();
            return view('home.super_admin', compact('schools'));
        }

        private function schoolDashboard(Request $request, $schoolId)
        {
            $selectedAcademicYearId = $request->input('academic_year')
                ?: AcademicYear::where('school_id', $schoolId)
                    ->orderBy('start_date', 'desc')
                    ->first()->id;

            $currentAcademicYear = AcademicYear::findOrFail($selectedAcademicYearId);

            $academicYears = AcademicYear::where('school_id', $schoolId)
                ->orderBy('start_date', 'desc')
                ->get();

            $currentSemester = Semester::where('academic_year_id', $currentAcademicYear->id)
                ->where('is_current', true)
                ->first();

            $semesters = Semester::where('academic_year_id', $currentAcademicYear->id)
                ->orderBy('start_date')
                ->get();

            // Thống kê số lượng
            $teacherCount = User::where('school_id', $schoolId)
                ->where('role', 'teacher')
                ->count();

            $currentYearStudentCount = User::where('school_id', $schoolId)
                ->where('role', 'student')
                ->when($currentAcademicYear, function($query) use ($currentAcademicYear) {
                    $query->whereHas('studentClasses', function($q) use ($currentAcademicYear) {
                        $q->where('academic_year_id', $currentAcademicYear->id);
                    });
                })
                ->count();

            $studentCount = User::where('school_id', $schoolId)
                ->where('role', 'student')
                ->count();

            $classCount = ClassModel::where('school_id', $schoolId)
                ->where('academic_year_id', $currentAcademicYear->id)
                ->count();

            $staffCount = User::where('school_id', $schoolId)
                ->where('role', 'school_admin')
                ->count();

            // Phân bổ học sinh theo khối
            $gradeLevels = GradeLevel::where('school_id', $schoolId)
                ->orderBy('grade_number')
                ->get();

            $gradeDistribution = [];
            $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'];

            foreach ($gradeLevels as $index => $grade) {
                $studentCount = User::where('school_id', $schoolId)
                    ->where('role', 'student')
                    ->whereHas('studentClasses', function($q) use ($grade, $currentAcademicYear) {
                        $q->where('academic_year_id', $currentAcademicYear->id)
                            ->whereHas('class', function($q) use ($grade) {
                                $q->where('grade_level_id', $grade->id);
                            });
                    })
                    ->count();

                $gradeDistribution[] = [
                    'grade_number' => $grade->grade_number,
                    'student_count' => $studentCount,
                    'color' => $colors[$index % count($colors)],
                    'hover_color' => $this->adjustBrightness($colors[$index % count($colors)], -20),
                ];
            }

            // Thống kê học lực (giữ nguyên như cũ)
            $academicPerformanceByGrade = [];
            $totalPerformance = [
                'semester1' => ['excellent' => 0, 'good' => 0, 'average' => 0, 'weak' => 0, 'unrated' => 0],
                'semester2' => ['excellent' => 0, 'good' => 0, 'average' => 0, 'weak' => 0, 'unrated' => 0],
                'year' => ['excellent' => 0, 'good' => 0, 'average' => 0, 'weak' => 0, 'unrated' => 0],
            ];

            foreach ($gradeLevels as $grade) {
                $performance = $this->calculateAcademicPerformance($grade, $currentAcademicYear, $schoolId);
                $academicPerformanceByGrade[] = $performance;

                foreach (['semester1', 'semester2', 'year'] as $period) {
                    $totalPerformance[$period]['excellent'] += $performance[$period]['excellent'];
                    $totalPerformance[$period]['good'] += $performance[$period]['good'];
                    $totalPerformance[$period]['average'] += $performance[$period]['average'];
                    $totalPerformance[$period]['weak'] += $performance[$period]['weak'];
                    $totalPerformance[$period]['unrated'] += $performance[$period]['unrated'];
                }
            }

            // Tính phần trăm cho tổng
            foreach (['semester1', 'semester2', 'year'] as $period) {
                $total = array_sum($totalPerformance[$period]);
                if ($total > 0) {
                    $totalPerformance[$period]['excellent_percent'] = round(($totalPerformance[$period]['excellent'] / $total) * 100, 2);
                    $totalPerformance[$period]['good_percent'] = round(($totalPerformance[$period]['good'] / $total) * 100, 2);
                    $totalPerformance[$period]['average_percent'] = round(($totalPerformance[$period]['average'] / $total) * 100, 2);
                    $totalPerformance[$period]['weak_percent'] = round(($totalPerformance[$period]['weak'] / $total) * 100, 2);
                    $totalPerformance[$period]['unrated_percent'] = round(($totalPerformance[$period]['unrated'] / $total) * 100, 2);
                } else {
                    foreach (['excellent', 'good', 'average', 'weak', 'unrated'] as $type) {
                        $totalPerformance[$period][$type . '_percent'] = 0;
                    }
                }
            }

            // Nếu là super admin thì lấy thông tin trường đang xem
            $currentSchool = null;
            if (auth()->user()->isSuperAdmin()) {
                $currentSchool = School::find($schoolId);
            }

            return view('home.index', compact(
                'currentAcademicYear',
                'academicYears',
                'currentSemester',
                'semesters',
                'teacherCount',
                'studentCount',
                'classCount',
                'staffCount',
                'gradeDistribution',
                'academicPerformanceByGrade',
                'totalPerformance',
                'currentYearStudentCount',
                'currentSchool'
            ));
        }

        // Cập nhật method calculateAcademicPerformance để thêm school_id
        private function calculateAcademicPerformance($grade, $academicYear, $schoolId)
        {
            $students = User::where('school_id', $schoolId)
                ->where('role', 'student')
                ->whereHas('studentClasses', function($query) use ($grade, $academicYear) {
                    $query->where('academic_year_id', $academicYear->id)
                        ->whereHas('class', function($q) use ($grade) {
                            $q->where('grade_level_id', $grade->id);
                        });
                })
                ->with(['grades' => function($query) use ($academicYear) {
                    $query->where('academic_year_id', $academicYear->id);
                }])
                ->get();

            // Phần còn lại giữ nguyên
            $result = [
                'grade_number' => $grade->grade_number,
                'semester1' => ['excellent' => 0, 'good' => 0, 'average' => 0, 'weak' => 0, 'unrated' => 0],
                'semester2' => ['excellent' => 0, 'good' => 0, 'average' => 0, 'weak' => 0, 'unrated' => 0],
                'year' => ['excellent' => 0, 'good' => 0, 'average' => 0, 'weak' => 0, 'unrated' => 0],
            ];

            foreach ($students as $student) {
                $performance = $this->evaluateStudentPerformance($student, $academicYear);

                foreach (['semester1', 'semester2', 'year'] as $period) {
                    $result[$period][$performance[$period]]++;
                }
            }

            // Tính phần trăm cho từng học kỳ và cả năm
            foreach (['semester1', 'semester2', 'year'] as $period) {
                $total = array_sum($result[$period]);
                if ($total > 0) {
                    $result[$period]['excellent_percent'] = round(($result[$period]['excellent'] / $total) * 100, 2);
                    $result[$period]['good_percent'] = round(($result[$period]['good'] / $total) * 100, 2);
                    $result[$period]['average_percent'] = round(($result[$period]['average'] / $total) * 100, 2);
                    $result[$period]['weak_percent'] = round(($result[$period]['weak'] / $total) * 100, 2);
                    $result[$period]['unrated_percent'] = round(($result[$period]['unrated'] / $total) * 100, 2);
                } else {
                    foreach (['excellent', 'good', 'average', 'weak', 'unrated'] as $type) {
                        $result[$period][$type.'_percent'] = 0;
                    }
                }
            }

            return $result;
        }
        private function evaluateStudentPerformance($student, $academicYear)
        {


            $semester1Grades = $student->grades->where('semester_id', 1); // Giả sử semester_id 1 là HK1
            $semester2Grades = $student->grades->where('semester_id', 2); // Giả sử semester_id 2 là HK2

            $semester1Avg = $semester1Grades->avg('score');
            $semester2Avg = $semester2Grades->avg('score');
            $yearAvg = ($semester1Avg + $semester2Avg) / 2;

            return [
                'semester1' => $this->getPerformanceLevel($semester1Avg),
                'semester2' => $this->getPerformanceLevel($semester2Avg),
                'year' => $this->getPerformanceLevel($yearAvg),
            ];
        }

        private function getPerformanceLevel($avgScore)
        {
            if ($avgScore === null) return 'unrated';
            if ($avgScore >= 8.0) return 'excellent';
            if ($avgScore >= 6.5) return 'good';
            if ($avgScore >= 5.0) return 'average';
            return 'weak';
        }

        private function adjustBrightness($hex, $steps)
        {
            // Bước điều chỉnh độ sáng màu
            $steps = max(-255, min(255, $steps));

            // Chuyển đổi HEX thành RGB
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) == 3) {
                $hex = str_repeat(substr($hex, 0, 1), 2).str_repeat(substr($hex, 1, 1), 2).str_repeat(substr($hex, 2, 1), 2);
            }

            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));

            // Điều chỉnh độ sáng
            $r = max(0, min(255, $r + $steps));
            $g = max(0, min(255, $g + $steps));
            $b = max(0, min(255, $b + $steps));

            $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
            $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
            $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

            return '#'.$r_hex.$g_hex.$b_hex;
        }
    }
