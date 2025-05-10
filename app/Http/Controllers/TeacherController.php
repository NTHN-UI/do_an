<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;


class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('school_id', auth()->user()->school_id)
            ->when($search, function($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                });
            })
            ->orderBy('full_name')
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
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,NULL,id,school_id,'.auth()->user()->school_id,
            'gender' => 'required|in:Nam,Nữ,Khác',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'subject_id' => 'required|exists:subjects,id,school_id,'.auth()->user()->school_id,
        ]);

        // Lấy thông tin trường học
        $school = School::find(auth()->user()->school_id);

        // Xử lý tên trường để tạo domain
        $schoolName = $school->name;
        $slug = Str::slug(mb_strtolower($schoolName));
        $slugParts = explode('-', $slug);
        $slugParts = array_slice($slugParts, 1, (count($slugParts) - 1));
        $schoolDomain = join('', $slugParts) . '.edu.vn';

        // Tạo email tự động theo định dạng: tên.họ+tên đệm@domain
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
            'password' => Hash::make('12345678'), // Mật khẩu mặc định
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'role' => User::ROLE_TEACHER,
            'school_id' => auth()->user()->school_id,
            'subject_id' => $request->subject_id,
            'is_active' => true
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
        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $teacher = User::where('school_id', auth()->user()->school_id)
            ->where('role', User::ROLE_TEACHER)
            ->findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,'.$teacher->id.',id,school_id,'.auth()->user()->school_id,
            'gender' => 'required|in:Nam,Nữ,Khác',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'is_active' => 'boolean'
        ]);

        $data = $request->only([
            'full_name', 'phone', 'gender',
            'date_of_birth', 'address', 'is_active'
        ]);

        // Giữ nguyên email và không cho phép thay đổi
        $data['email'] = $teacher->email;

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

        private function reorderTeacherNumbers($school_id)
        {
            $teachers = User::where('school_id', $school_id)
                ->where('role', User::ROLE_TEACHER)
                ->orderBy('school_auto_id')
                ->get();

            foreach ($teachers as $index => $teacher) {
                $teacher->school_auto_id = $index + 1;
                $teacher->save();
            }
        }
}
