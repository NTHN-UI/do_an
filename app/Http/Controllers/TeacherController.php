<?php

    namespace App\Http\Controllers;

    use App\Models\School;
    use App\Models\Subject;
    use App\Models\TeacherAssignment;
    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Str;
    use Illuminate\Validation\Rule;
    use Illuminate\Validation\Rules\Password;


    class TeacherController extends Controller
    {
        /**
         * Display a listing of the resource.
         */
        private function getValidationRules(bool $isUpdate = false, User $teacher = null): array
        {
            $rules = [
                'full_name' => [
                    'required',
                    'string',
                    'max:50',
                    'regex:/^[\p{L}\s\-]+$/u'
                ],
                'phone' => [
                    'required',
                    'string',
                    'regex:/^(0[3|5|7|8|9])[0-9]{8,9}$/',
                    function ($attribute, $value, $fail) {
                        $cleanNumber = preg_replace('/[^0-9]/', '', $value);
                        if (!in_array(strlen($cleanNumber), [10, 11])) {
                            $fail('Số điện thoại phải có 10 hoặc 11 số');
                        }
                    },
                    Rule::unique('users')->where(function ($query) {
                        return $query->where('school_id', auth()->user()->school_id);
                    })->ignore($isUpdate ? $teacher->id : null)
                ],
                'gender' => [
                    'required',
                    'in:Nam,Nữ,Khác'
                ],
                'date_of_birth' => [
                    'required'
                ],
                'address' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[\p{L}0-9\s\-\/,]+$/u'
                ],
                'subject_id' => [
                    'required',
                    Rule::exists('subjects', 'id')->where('school_id', auth()->user()->school_id)
                ],
                'is_active' => 'sometimes|boolean'
            ];

            return $rules;
        }

        private function getValidationMessages(): array
        {
            return [
                'full_name.required' => 'Họ và tên không được để trống',
                'full_name.max' => 'Họ và tên không được vượt quá 50 ký tự',
                'full_name.regex' => 'Họ và tên chỉ được chứa chữ cái, khoảng trắng và dấu gạch ngang',

                'phone.required' => 'Số điện thoại không được để trống',
                'phone.regex' => 'Số điện thoại phải bắt đầu bằng 03, 05, 07, 08 hoặc 09',
                'phone.unique' => 'Số điện thoại này đã được sử dụng',

                'gender.required' => 'Vui lòng chọn giới tính',


                'date_of_birth.required' => 'Ngày sinh không được để trống',

                'address.required' => 'Địa chỉ không được để trống',
                'address.max' => 'Địa chỉ không được vượt quá 100 ký tự',
                'address.regex' => 'Địa chỉ không được chứa ký tự đặc biệt',

                'subject_id.required' => 'Vui lòng chọn môn học giảng dạy',

            ];
        }
        public function index(Request $request)
        {
            $search = $request->input('search');

            $teachers = User::with(['school', 'subject'])
            ->where('role', User::ROLE_TEACHER)
                ->where('school_id', auth()->user()->school_id)
                ->when($search, function($query) use ($search) {
                    return $query->where(function($q) use ($search) {
                        $q->where('full_name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%");
                    });
                })
                ->latest('created_at')
                ->paginate(10);

            return view('teachers.index', compact('teachers', 'search'));
        }
        /**
         * Show the form for creating a new resource.
         */
        public function create()
        {
            $subjects = Subject::where('school_id', auth()->user()->school_id)->get();
            return view('teachers.create', compact('subjects'));    }
        /**
         * Store a newly created resource in storage.
         */
        public function store(Request $request)
        {
            $validator = Validator::make(
                $request->all(),
                $this->getValidationRules(),
                $this->getValidationMessages()
            );

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $school = School::find(auth()->user()->school_id);

            // Xử lý tên trường để tạo domain
            $schoolName = $school->name;
            $slug = Str::slug(mb_strtolower($schoolName));
            $slugParts = explode('-', $slug);
            $slugParts = array_slice($slugParts, 1, (count($slugParts) - 1));
            $schoolDomain = join('', $slugParts) . '.edu.vn';

            // Tạo email theo định dạng: tên.họ+tên đệm@domain
            $fullName = $request->full_name;
            $nameParts = explode(' ', $fullName);

            // Lấy tên (phần cuối)
            $lastName = array_pop($nameParts);
            $lastName = mb_strtolower(Str::ascii($lastName));

            // Lấy chữ cái đầu của họ và tên đệm
            $firstLetters = '';
            foreach ($nameParts as $part) {
                $firstLetters .= mb_substr($part, 0, 1);
            }
            $firstLetters = mb_strtolower(Str::ascii($firstLetters));

            $username = $lastName . '.' . $firstLetters;

            // Kiểm tra nếu email đã tồn tại thì thêm số vào cuối
            $email = $username . '@' . $schoolDomain;
            $originalEmail = $email;
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = $username . $counter . '@' . $schoolDomain;
                $counter++;
            }

            User::create([
                'full_name' => $request->full_name,
                'email' => $email,
                'phone' => $request->phone,
                'password' => Hash::make('12345678'),
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
                'role' => User::ROLE_TEACHER,
                'school_id' => auth()->user()->school_id,
                'subject_id' => $request->subject_id,
                'is_active' => $request->has('is_active')
            ]);

            return redirect()->route('teachers.index')
                ->with('success', 'Thêm giáo viên thành công!');
        }

        /**
         * Display the specified resource.
         */
        public function show($id)
        {
            $teacher = User::where('school_id', auth()->user()->school_id)
                ->where('role', User::ROLE_TEACHER)
                ->findOrFail($id);
            return view('teachers.show', compact('teacher'));
        }

        /**
         * Show the form for editing the specified resource.
         */
        public function edit($id)
        {
            $teacher = User::where('school_id', auth()->user()->school_id)
                ->where('role', User::ROLE_TEACHER)
                ->findOrFail($id);

            $subjects = Subject::where('school_id', auth()->user()->school_id)->get();

            return view('teachers.edit', compact('teacher', 'subjects'));
        }

        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, $id)
        {
            $teacher = User::where('school_id', auth()->user()->school_id)
                ->where('role', User::ROLE_TEACHER)
                ->findOrFail($id);

            $validator = Validator::make(
                $request->all(),
                $this->getValidationRules(true, $teacher),
                $this->getValidationMessages()
            );

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->only([
                'full_name', 'phone', 'gender',
                'date_of_birth', 'address', 'subject_id',
                'is_active'
            ]);

            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $teacher->update($data);

            return redirect()->route('teachers.index')
                ->with('success', 'Cập nhật giáo viên thành công!');
        }
        /**
         * Remove the specified resource from storage.
         */
        public function destroy($id)
        {

        }
    }
