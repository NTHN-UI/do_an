<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SchoolAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = User::where('role', 'school_admin')
            ->with('school')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhereHas('school', function($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            });
        }

        $schoolAdmins = $query->paginate(10);
        $schools = School::all();

        return view('school_admins.index', compact('schoolAdmins', 'schools', 'request'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::all();
        return view('school_admins.create', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'full_name.required' => 'Tên không được để trống',
            'full_name.max' => 'Tối đa 50 ký tự',
            'email.required' => 'Email không được để trống',
            'email.regex' => 'Email không hợp lệ',
            'email.unique' => 'Email đã tồn tại',
            'school_id.required' => 'Trường học không được để trống',
            'school_id.unique_school_admin' => 'Trường học đã có tài khoản admin',
            'password.required' => 'Mật khẩu không được để trống',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ];

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:50',
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
                'unique:users'
            ],
            'school_id' => [
                'required',
                'exists:schools,id',
                function ($attribute, $value, $fail) {
                    if (User::where('school_id', $value)->where('role', 'school_admin')->exists()) {
                        $fail('Trường học đã có admin');
                    }
                }
            ],            'password' => 'required|min:8|confirmed'
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'school_admin',
            'school_id' => $request->school_id,
            'is_active' => true
        ]);

        return redirect()->route('school_admins.index')
            ->with('success', 'Thêm admin trường thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $admin = User::with('school')->findOrFail($id);
        return view('school_admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $admin = User::findOrFail($id);
        $schools = School::all();
        return view('school_admins.edit', compact('admin', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $messages = [
            'full_name.required' => 'Họ tên không được để trống',
            'full_name.max' => 'Tối đa 50 ký tự',
            'email.required' => 'Email không được để trống',
            'email.regex' => 'Email không hợp lệ',
            'email.unique' => 'Email đã tồn tại',
            'school_id.required' => 'Trường học không được để trống',
            'school_id.unique_school_admin' => 'Trường học đã có admin',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ];

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:50',
            'email' => [
                'required',
                'email',
                'unique:users,email,'.$id,
                'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/'
            ],
            'school_id' => [
                'required',
                'exists:schools,id',
                function ($attribute, $value, $fail) use ($id) {
                    if (User::where('school_id', $value)
                        ->where('role', 'school_admin')
                        ->where('id', '!=', $id)
                        ->exists()) {
                        $fail('Trường học đã có admin');
                    }
                }
            ],            'password' => 'nullable|min:8|confirmed'
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'full_name' => $request->full_name,
            'email' => $request->email,
            'school_id' => $request->school_id
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        User::find($id)->update($data);

        return redirect()->route('school_admins.index')
            ->with('success', 'Cập nhật admin trường thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::find($id)->delete();
        return redirect()->route('school_admins.index')
            ->with('success', 'Xóa admin trường thành công!');
    }
}
