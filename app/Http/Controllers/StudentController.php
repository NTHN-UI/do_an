<?php

namespace App\Http\Controllers;

use App\Exports\StudentsExport;
use App\Exports\StudentsTemplateExport;
use App\Imports\StudentsImport;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\School;
use App\Models\User;
use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        $search = $request->input('search');
        $classId = $request->input('class_id');
        $academicYearId = $request->input('academic_year_id');
        $gradeLevelId = $request->input('grade_level_id');

        // Tạo base query
        $query = User::where('role', User::ROLE_STUDENT)
            ->where('users.school_id', auth()->user()->school_id);

        // Xử lý AJAX request
        if ($request->ajax()) {
            $query->when($request->grade_id, function ($q) use ($request) {
                $q->join('grade_users', 'users.id', '=', 'grade_users.user_id')
                    ->where('grade_id', $request->grade_id);
            })
                ->when($request->academic_year_id, function ($q) use ($request) {
                    $q->where('academic_year_id', $request->academic_year_id);
                });

            $students = $query->select('users.*')
                ->with(['school:id,name'])
                ->orderBy('school_auto_id')
                ->paginate(10)
                ->appends($request->except('page')); // Thêm dòng này để giữ bộ lọc

            return response()->json([
                'success' => true,
                'data' => view('students.partials.results', ['students' => $students])->render(),
                'pagination' => view('students.partials.pagination', [
                    'students' => $students,
                    'filters' => $request->only(['grade_id', 'academic_year_id'])
                ])->render()
            ]);
        }

        // Xử lý request thông thường
        $query->when($search, function ($q) use ($search) {
            $q->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        })
            ->when($classId, function ($q, $classId) {
                $q->whereHas('studentClasses', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                });
            })
            ->when($academicYearId, function ($q, $academicYearId) {
                $q->whereHas('studentAcademicYears', function ($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                });
            })
            ->when($gradeLevelId, function ($q, $gradeLevelId) {
                $q->whereHas('studentClasses', function ($q) use ($gradeLevelId) {
                    $q->whereHas('gradeLevel', function ($q) use ($gradeLevelId) {
                        $q->where('id', $gradeLevelId);
                    });
                });
            });

        $students = $query->with(['school', 'studentClasses.gradeLevel'])
            ->orderBy('school_auto_id')
            ->paginate(10)
            ->appends($request->except('page'));

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->with('gradeLevel')
            ->get();

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'asc')
            ->get();
        $gradeLevels = GradeLevel::where('school_id', auth()->user()->school_id) // Thêm dòng này
        ->orderBy('grade_number', 'asc')
            ->get();

        return view('students.index', compact(
            'students',
            'search',
            'classes',
            'classId',
            'academicYears',
            'academicYearId',
            'gradeLevels',
            'gradeLevelId'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $academicYearId = request()->input('academic_year_id');
        $gradeLevelId = request()->input('grade_level_id');

        $currentAcademicYear = $academicYearId
            ? AcademicYear::find($academicYearId)
            : AcademicYear::where('school_id', auth()->user()->school_id)
                ->latest()
                ->first();

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->when($currentAcademicYear, function ($query) use ($currentAcademicYear) {
                $query->where('academic_year_id', $currentAcademicYear->id ?? null);
            })
            ->with('gradeLevel')
            ->get();

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'asc')
            ->get();

        $gradeLevels = GradeLevel::where('school_id', auth()->user()->school_id)
            ->orderBy('grade_number', 'asc')
            ->get();

        return view('students.create', [
            'classes' => $classes,
            'currentAcademicYear' => $currentAcademicYear,
            'selectedAcademicYearId' => $academicYearId,
            'selectedGradeLevelId' => $gradeLevelId,
            'academicYears' => $academicYears,
            'gradeLevels' => $gradeLevels,
            'showEntryScoreField' => $gradeLevelId == 10 // Sử dụng biến đã lấy ở trên
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'phone' => [
                    'nullable',
                    'string',
                    'regex:/^0\d{9}$/', // đúng 10 số bắt đầu bằng 0
                    function ($attribute, $value, $fail) {
                        if ($value && User::where('phone', $value)
                                ->where('school_id', auth()->user()->school_id)
                                ->exists()) {
                            $fail('Số điện thoại đã tồn tại trong trường.');
                        }
                    },

                ],

                'gender' => 'nullable|in:Nam,Nữ,Khác',
                'date_of_birth' => 'nullable|date',
                'academic_year_id' => 'required|exists:academic_years,id,school_id,' . auth()->user()->school_id,
                'grade_level_id' => 'required|exists:grade_levels,id,school_id,' . auth()->user()->school_id,
                'guardian_name' => 'nullable|string|max:255',
                'guardian_email' => 'nullable|email|unique:users,guardian_email',
                'guardian_phone' => 'nullable|string|max:20',

            ]);
            // Tạo mảng rules mới chỉ cho entry_score nếu là khối 10
            $entryScoreRules = [];
            // Tìm grade => "Khối 10"


            if ($request->grade_level_id == 1) {
                $entryScoreRules = ['entry_score' => 'required|numeric|min:0|max:50'];

                // Validate riêng cho entry_score
                $request->validate($entryScoreRules);

                // Thêm vào dữ liệu đã validate
                $validated['entry_score'] = $request->entry_score;
            }

            $validated['role'] = User::ROLE_STUDENT;
            $validated['school_id'] = auth()->user()->school_id;
            $validated['is_active'] = $request->has('is_active');

            Log::info("Du lieu:", [$validated]);

            // Lấy thông tin trường học
            $school = School::find(auth()->user()->school_id);

            // Xử lý tên trường để tạo domain
            $schoolName = $school->name;
            // Loại bỏ dấu và chuyển thành chữ thường không dấu
            $slug = Str::slug(mb_strtolower($schoolName));
            $slugParts = explode('-', $slug);
            $slugParts = array_slice($slugParts, 1, (count($slugParts) - 1));
            $schoolDomain = join('', $slugParts) . '.edu.vn';

            // Tạo email tự động theo định dạng: tên.họ+tên đệm@domain
            $fullName = $validated['full_name'];
            $nameParts = explode(' ', $fullName);

            // Lấy tên (phần cuối)
            $lastName = array_pop($nameParts);
            $lastName = mb_strtolower(Str::ascii($lastName)); // Chuyển thành không dấu

            // Lấy chữ cái đầu của họ và tên đệm
            $firstLetters = '';
            foreach ($nameParts as $part) {
                $firstLetters .= mb_substr($part, 0, 1);
            }
            $firstLetters = mb_strtolower(Str::ascii($firstLetters)); // Chuyển thành không dấu

            $username = $lastName . '.' . $firstLetters;

            // Kiểm tra nếu email đã tồn tại thì thêm số vào cuối
            $email = $username . '@' . $schoolDomain;
            $originalEmail = $email;
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = $username . $counter . '@' . $schoolDomain;
                $counter++;
            }

            $validated['email'] = $email;

            // Đặt mật khẩu mặc định là 12345678
            $validated['password'] = Hash::make('12345678');
            $student = User::create($validated);
            $student->studentGrades()->attach($request->academic_year_id, [
                'grade_id' => $request->grade_level_id,
                'school_id' => $school->id,
            ]);

            return redirect()->route('students.index')->with('success', 'Học sinh đã được thêm thành công.');
        } catch (\Exception $e) {
            Log::error('Error creating student: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi thêm học sinh: ' . $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(User $student)
    {
        if ($student->school_id !== auth()->user()->school_id || !$student->isStudent()) {
            abort(403, 'Không được phép truy cập học sinh từ trường khác');
        }

        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        if ($student->school_id !== auth()->user()->school_id || !$student->isStudent()) {
            abort(403, 'Không được phép sửa học sinh từ trường khác');
        }

        $currentAcademicYear = AcademicYear::where('school_id', auth()->user()->school_id)
            ->latest()
            ->first();

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->where('academic_year_id', $currentAcademicYear->id ?? null)
            ->with('gradeLevel')
            ->get();

        $studentClassIds = $student->studentClasses->pluck('id')->toArray();

        return view('students.edit', compact(
            'student',
            'classes',
            'currentAcademicYear',
            'studentClassIds'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student)
    {
        try {
            if ($student->school_id !== auth()->user()->school_id || !$student->isStudent()) {
                abort(403, 'Không được phép cập nhật học sinh từ trường khác');
            }

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'phone' => [
                    'nullable',
                    'string',
                    'regex:/^0\d{9}$/', // đúng 10 số bắt đầu bằng 0
                    function ($attribute, $value, $fail) use ($student) {
                        if ($value && User::where('phone', $value)
                                ->where('school_id', auth()->user()->school_id)
                                ->where('id', '!=', $student->id) // Loại trừ bản ghi hiện tại
                                ->exists()) {
                            $fail('Số điện thoại đã tồn tại trong trường.');
                        }
                    },
                ],

                'gender' => 'nullable|in:Nam,Nữ,Khác',
                'date_of_birth' => 'nullable|date',
                'guardian_name' => 'nullable|string|max:255',
                'guardian_email' => 'nullable|email|unique:users,guardian_email,' . $student->id,
                'guardian_phone' => 'nullable|string|max:20',
            ]);

            if ($student->studentGrades->first()?->grade_id == 1) {
                $entryScoreRules = ['entry_score' => 'required|numeric|min:0|max:50'];

                // Validate riêng cho entry_score
                $request->validate($entryScoreRules);

                // Thêm vào dữ liệu đã validate
                $validated['entry_score'] = $request->entry_score;
            }

            $validated['is_active'] = $request->has('is_active');
            $validated['email'] = $student->email; // Giữ nguyên email cũ
            // Không xử lý password, mật khẩu sẽ giữ nguyên

            $student->update($validated);


            return redirect()->route('students.index')->with('success', 'Thông tin học sinh đã được cập nhật.');

        } catch (\Exception $e) {
            Log::error('Error updating student: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi cập nhật học sinh: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
    }

    private function reorderStudentNumbers($school_id)
    {
        $students = User::where('school_id', $school_id)
            ->where('role', User::ROLE_STUDENT)
            ->orderBy('school_auto_id')
            ->get();

        foreach ($students as $index => $student) {
            $student->school_auto_id = $index + 1;
            $student->save();
        }
    }

    /**
     * Xử lý import học sinh từ file Excel
     */
    public function importDirect(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'academic_year_id' => 'required|exists:academic_years,id,school_id,' . auth()->user()->school_id,
            'grade_level_id' => 'required|exists:grade_levels,id,school_id,' . auth()->user()->school_id,
        ]);
        try {

            // Kiểm tra xem có phải khối 10 không
            $gradeLevel = GradeLevel::find($request->grade_level_id);
            $isGrade10 = $gradeLevel && $gradeLevel->grade_number == 10;
            if ($isGrade10) {
                // Kiểm tra file có cột điểm đầu vào không
                $fileData = Excel::toArray(new StudentsImport(
                    auth()->user()->school_id,
                    $request->academic_year_id,
                    $request->grade_level_id
                ), $request->file('file'))[0];

                $hasEntryScoreColumn = isset($fileData[0]['diem_dau_vao']) ||
                    isset($fileData[0]['diemdauvao']) ||
                    isset($fileData[0]['diem dau vao']);

                if (!$hasEntryScoreColumn) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File import thiếu cột điểm đầu vào (bắt buộc cho khối 10)'
                    ], 422);
                }
            }

            $import = new StudentsImport(
                auth()->user()->school_id,
                $request->academic_year_id,
                $request->grade_level_id
            );

            Excel::import($import, $request->file('file'));

            $message = 'Import thành công ' . $import->getImportedCount() . ' học sinh';
            if ($import->getErrors()) {
                $message .= '<br>Có lỗi với một số dòng: <br>' . implode('<br>', $import->getErrors());
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error("Error in StudentController@importDirect: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export danh sách học sinh
     */
    public function export(Request $request): BinaryFileResponse
    {
        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id,school_id,' . auth()->user()->school_id,
            'grade_level_id' => 'nullable|exists:grade_levels,id,school_id,' . auth()->user()->school_id,
        ]);

        $schoolId = auth()->user()->school_id;
        $academicYearId = $request->academic_year_id;
        $gradeLevelId = $request->grade_level_id;

        return Excel::download(
            new StudentsExport($schoolId, $academicYearId, $gradeLevelId),
            'danh-sach-hoc-sinh-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export file mẫu để nhập học sinh
     */
    public function exportTemplate(): BinaryFileResponse
    {
        $academicYearId = request()->input('academic_year_id');
        $gradeLevelId = request()->input('grade_level_id');

        return Excel::download(
            new StudentsTemplateExport($academicYearId, $gradeLevelId),
            'mau-nhap-hoc-sinh.xlsx'
        );
    }

    public function getStudentsByGrade(Request $request)
    {
        try {
            if ($request->ajax()) {
                $grade_id = $request->input('grade_id');
                $academic_year_id = $request->input('academic_year_id');

                $students = User::join('grade_users', 'users.id', '=', 'grade_users.user_id')
                    ->where('grade_id', '=', $grade_id)
                    ->where('academic_year_id', '=', $academic_year_id)
                    ->select('users.id', 'users.full_name', 'users.email',
                        'users.phone', 'users.is_active', 'users.school_id')
                    ->with(['school' => function ($query) {
                        $query->select('id', 'name');
                    }])
                    ->paginate(10);

                return response()->json([
                    'success' => true,
                    'data' => view('students.partials.results', ['students' => $students])->render(),
                    'pagination' => view('students.partials.pagination', ['students' => $students])->render()
                ]);
            }
        } catch (\Exception $ex) {
            Log::error('Error in StudentController@getStudentsByGrade: ' . $ex->getMessage());
            return response()->json([
                'error' => 'Có lỗi xảy ra',
            ]);
        }
    }
}
