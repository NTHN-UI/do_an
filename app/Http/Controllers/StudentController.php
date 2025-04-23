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


        $students = User::where('role', User::ROLE_STUDENT)
            ->where('school_id', auth()->user()->school_id)
            ->when($search, function ($query) use ($search) {
                return $query->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            })
            ->when($classId, function ($query, $classId) {
                return $query->whereHas('studentClasses', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                });
            })
            ->when($academicYearId, function ($query, $academicYearId) {
                return $query->whereHas('studentAcademicYears', function ($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                });
            })
            ->when($gradeLevelId, function ($query, $gradeLevelId) {
                return $query->whereHas('studentClasses', function ($q) use ($gradeLevelId) {
                    $q->whereHas('gradeLevel', function ($q) use ($gradeLevelId) {
                        $q->where('id', $gradeLevelId);
                    });
                });
            })
            ->with(['school', 'studentClasses.gradeLevel'])
            ->orderBy('school_auto_id')
            ->paginate(10);

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
        $currentAcademicYear = AcademicYear::where('school_id', auth()->user()->school_id)
            ->latest()
            ->first();

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->where('academic_year_id', $currentAcademicYear->id ?? null)
            ->with('gradeLevel')
            ->get();

        return view('students.create', compact('classes', 'currentAcademicYear'));
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
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:Nam,Nữ,Khác',
                'date_of_birth' => 'nullable|date',
            ]);

            $validated['role'] = User::ROLE_STUDENT;
            $validated['school_id'] = auth()->user()->school_id;
            $validated['is_active'] = $request->has('is_active');

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
            $student->studentAcademicYears()->attach($request->academic_year_id, [
                'grade_level_id' => $request->grade_level_id
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
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:Nam,Nữ,Khác',
                'date_of_birth' => 'nullable|date',

            ]);

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
        try {
            if ($student->school_id !== auth()->user()->school_id || !$student->isStudent()) {
                abort(403, 'Không được phép xóa học sinh từ trường khác');
            }


            // Kiểm tra xem học sinh có đang trong lớp nào không
            if ($student->studentClasses()->count() > 0) {
                return back()->with('error', 'Không thể xóa học sinh vì đang có lớp học. Vui lòng xóa học sinh khỏi lớp trước.');
            }

            $school_id = $student->school_id;
            $student->delete();

            // Cập nhật lại STT cho các học sinh còn lại
            $this->reorderStudentNumbers($school_id);

            return redirect()->route('students.index')
                ->with('success', 'Học sinh đã được xóa thành công.');

        } catch (\Exception $e) {
            Log::error('Error deleting student: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa học sinh: ' . $e->getMessage());
        }
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
}
