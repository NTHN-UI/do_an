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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private function getValidationRules(Request $request, bool $isUpdate = false, User $student = null): array
    {
        $rules = [
            'full_name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[\p{L}\s\-]+$/u'
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^(0[3|5|7|8|9])[0-9]{8,9}$/',
                function ($attribute, $value, $fail) use ($isUpdate, $student) {
                    if ($value) {
                        $cleanNumber = preg_replace('/[^0-9]/', '', $value);
                        if (!in_array(strlen($cleanNumber), [10, 11])) {
                            $fail('Số điện thoại phải có 10 hoặc 11 số');
                        }

                        $query = User::where('phone', $cleanNumber)
                            ->where('school_id', auth()->user()->school_id);

                        if ($isUpdate && $student) {
                            $query->where('id', '!=', $student->id);
                        }

                        if ($query->exists()) {
                            $fail('Số điện thoại đã được sử dụng');
                        }
                    }
                }
            ],
            'gender' => [
                'required',
                'in:Nam,Nữ,Khác'
            ],
            'date_of_birth' => [
                'required'
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[\p{L}0-9\s\-\/,]+$/u'
            ],
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', auth()->user()->school_id)
            ],
            'grade_level_id' => [
                'required',
                Rule::exists('grade_levels', 'id')->where('school_id', auth()->user()->school_id)
            ],
            'guardian_name' => [
                'nullable',
                'string',
                'max:50'
            ],
            'guardian_phone' => [
                'nullable',
                'string',
                'regex:/^(0[3|5|7|8|9])[0-9]{8,9}$/'
            ],
            'guardian_email' => [
                'required',
                'email',
                Rule::unique('users', 'guardian_email')->ignore($isUpdate ? $student->id : null)
            ],

        ];
        $gradeLevel = GradeLevel::find($request->grade_level_id);
        if ($gradeLevel && $gradeLevel->grade_number == 10) {
            $rules['entry_score'] = 'required|numeric|min:0|max:50';
        }

        return $rules;
    }

    private function getValidationMessages(): array
    {
        return [
            'full_name.required' => 'Họ và tên không được để trống',
            'full_name.max' => 'Họ và tên không được vượt quá 50 ký tự',
            'full_name.regex' => 'Họ và tên chỉ được chứa chữ cái, khoảng trắng và dấu gạch ngang',

            'phone.regex' => 'Số điện thoại phải bắt đầu bằng 03, 05, 07, 08 hoặc 09',

            'gender.required' => 'Vui lòng chọn giới tính',

            'date_of_birth.required' => 'Ngày sinh không được để trống',

            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự',
            'address.regex' => 'Địa chỉ không được chứa ký tự đặc biệt',

            'academic_year_id.required' => 'Vui lòng chọn năm học',

            'grade_level_id.required' => 'Vui lòng chọn khối lớp',

            'guardian_name.max' => 'Tên người giám hộ không được vượt quá 50 ký tự',

            'guardian_phone.regex' => 'Số điện thoại người giám hộ không hợp lệ',

            'guardian_email.required' => 'Email phụ huynh không để trống',
            'guardian_email.email' => 'Email phụ huynh không hợp lệ',
            'guardian_email.unique' => 'Email phụ huynh đã được sử dụng',

            'entry_score.required' => 'Vui lòng nhập điểm đầu vào cho khối 10',
            'entry_score.numeric' => 'Điểm đầu vào phải là số',
            'entry_score.min' => 'Điểm đầu vào không được nhỏ hơn 0',
            'entry_score.max' => 'Điểm đầu vào không được lớn hơn 50',

        ];
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $classId = $request->input('class_updateid');
        $academicYearId = $request->input('academic_year_id');
        $gradeLevelId = $request->input('grade_level_id');


        $query = User::where('role', User::ROLE_STUDENT)
            ->where('users.school_id', auth()->user()->school_id);


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
                ->latest('created_at')
                ->paginate(10)
                ->appends($request->all());

            return response()->json([
                'success' => true,
                'data' => view('students.partials.results', ['students' => $students])->render(),
                'pagination' => view('students.partials.pagination', [
                    'students' => $students,
                    'filters' => $request->only(['grade_id', 'academic_year_id'])
                ])->render()
            ]);
        }

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
            ->latest('created_at')
            ->paginate(10)
            ->appends($request->except('page'));

        $classes = ClassModel::where('school_id', auth()->user()->school_id)
            ->with('gradeLevel')
            ->get();

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'asc')
            ->get();
        $gradeLevels = GradeLevel::where('school_id', auth()->user()->school_id)
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

        $selectedGradeLevel = $gradeLevelId ? GradeLevel::find($gradeLevelId) : null;

        return view('students.create', [
            'classes' => $classes,
            'currentAcademicYear' => $currentAcademicYear,
            'selectedAcademicYearId' => $academicYearId,
            'selectedGradeLevelId' => $gradeLevelId,
            'selectedGradeLevel' => $selectedGradeLevel,
            'academicYears' => $academicYears,
            'gradeLevels' => $gradeLevels,
            'showEntryScoreField' => $selectedGradeLevel && $selectedGradeLevel->grade_number == 10
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make(
                $request->all(),
                $this->getValidationRules($request),
                $this->getValidationMessages()
            );

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $validated = $validator->validated();

            $gradeLevel = GradeLevel::find($request->grade_level_id);
            if ($gradeLevel && $gradeLevel->grade_number == 10) {
                $validated['entry_score'] = $request->entry_score;
            }

            $validated['role'] = User::ROLE_STUDENT;
            $validated['school_id'] = auth()->user()->school_id;
            $validated['is_active'] = $request->has('is_active');

            $school = School::find(auth()->user()->school_id);
            $schoolName = $school->name;
            $slug = Str::slug(mb_strtolower($schoolName));
            $slugParts = explode('-', $slug);
            $slugParts = array_slice($slugParts, 1, (count($slugParts) - 1));
            $schoolDomain = join('', $slugParts) . '.edu.vn';

            $fullName = $validated['full_name'];
            $nameParts = explode(' ', $fullName);
            $lastName = array_pop($nameParts);
            $lastName = mb_strtolower(Str::ascii($lastName));

            $firstLetters = '';
            foreach ($nameParts as $part) {
                $firstLetters .= mb_substr($part, 0, 1);
            }
            $firstLetters = mb_strtolower(Str::ascii($firstLetters));

            $username = $lastName . '.' . $firstLetters;
            $email = $username . '@' . $schoolDomain;
                $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = $username . $counter . '@' . $schoolDomain;
                $counter++;
            }

            $validated['email'] = $email;
            $validated['password'] = Hash::make('12345678');


            $student = User::create($validated);

            $student->studentGrades()->attach($request->grade_level_id, [
                'academic_year_id' => $request->academic_year_id,
                'school_id' => $school->id,
            ]);

            DB::commit();
            return redirect()->route('students.index')->with('success', 'Học sinh đã được thêm thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in StudentController@store:' . $e->getMessage());
            return back()
                ->with('error', 'Đã xảy ra lỗi khi thêm học sinh: ' . $e->getMessage())
                ->withInput();
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
        $student->gradeLevels()->first();

        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)
            ->orderBy('start_date', 'asc')
            ->get();

        $gradeLevels = GradeLevel::where('school_id', auth()->user()->school_id)
            ->orderBy('grade_number', 'asc')
            ->get();

        return view('students.edit', compact(
            'student',
            'academicYears',
            'gradeLevels'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student)
    {
        DB::beginTransaction();
        try {
            if ($student->school_id !== auth()->user()->school_id || !$student->isStudent()) {
                abort(403, 'Không được phép cập nhật học sinh từ trường khác');
            }

            $validator = Validator::make(
                $request->all(),
                $this->getValidationRules($request, true, $student),
                $this->getValidationMessages()
            );

            if ($validator->fails()) {

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $validated = $validator->validated();
            $validated['is_active'] = $request->has('is_active');
            $validated['email'] = $student->email;


            $gradeLevel = $student->studentGrades->first()->gradeLevel ?? null;
            if ($gradeLevel && $gradeLevel->grade_number == 10) {
                $validated['entry_score'] = $request->entry_score;
            }

            $student->update($validated);
            $student->studentGrades()->sync([
                $request->grade_level_id => [
                    'academic_year_id' => $request->academic_year_id,
                    'school_id' => auth()->user()->school_id
                ]
            ]);


            DB::commit();

            return redirect()->route('students.index')
                ->with('success', 'Cập nhật học sinh thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating student: ' . $e->getMessage());
            return back()
                ->with('error', 'Đã xảy ra lỗi khi cập nhật học sinh: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
    }

    /**
     * Xử lý import học sinh từ file Excel
     */
    public function exportTemplate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'required|exists:grade_levels,id'
        ]);

        $gradeLevelId = $request->grade_level_id;
        $fileName = 'student_import_template_' . $gradeLevelId . '.xlsx';

        return Excel::download(new StudentsTemplateExport($gradeLevelId), $fileName);
    }

    public function importDirect(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120',
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'required|exists:grade_levels,id'
        ]);

        DB::beginTransaction();
        try {
            $import = new StudentsImport(
                $request->academic_year_id,
                $request->grade_level_id
            );

            Excel::import($import, $request->file('file'));
            $errors = $import->getErrors();

            if (!empty($errors)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi import dữ liệu',
                    'errors' => $errors,
                    'error_count' => count($errors)
                ], 422);
            }

            DB::commit();

            $newStudents = User::where('role', User::ROLE_STUDENT)
                ->where('school_id', auth()->user()->school_id)
                ->whereHas('studentGrades', function($q) use ($request) {
                    $q->where('grade_id', $request->grade_level_id)
                        ->where('academic_year_id', $request->academic_year_id);
                })
                ->with('school')
                ->latest()
                ->take($import->getRowCount())
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Đã import thành công ' . $import->getRowCount() . ' học sinh',
                'new_student' => $import->getRowCount() === 1 ? $newStudents->first() : null,
                'reload' => $import->getRowCount() > 1
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi import file: ' . $e->getMessage()
            ], 500);
        }
    }
    }
