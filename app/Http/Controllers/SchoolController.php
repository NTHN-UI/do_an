<?php

namespace App\Http\Controllers;

use App\Models\EmailSetting;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use MongoDB\Driver\Session;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $schools = School::when($search, function($query, $search) {
            return $query->where('name', 'like', "%$search%")
                ->orWhere('district', 'like', "%$search%")
                ->orWhere('province', 'like', "%$search%");
        })->paginate(10);

        return view('schools.index', compact('schools', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Đọc dữ liệu từ file JSON
        $provinces = json_decode(file_get_contents(public_path('data/tinh_tp.json')), true);

        return view('schools.create', compact('provinces'));
    }
    public function getDistricts($provinceCode)
    {
        $allDistricts = json_decode(file_get_contents(public_path('data/quan_huyen.json')), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON data'], 500);
        }

        // Lọc quận/huyện theo mã tỉnh
        $districts = array_filter($allDistricts, function($district) use ($provinceCode) {
            return isset($district['parent_code']) && $district['parent_code'] == $provinceCode;
        });

        return response()->json(array_values($districts));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'name.required' => 'Tên trường không được để trống',
            'name.max' => 'Tối đa 50 ký tự',
            'address.required' => 'Địa chỉ không được để trống',
            'address.max' => 'Tối đa 50 ký tự',
            'district.required' => 'Quận/Huyện không được để trống',
            'province.required' => 'Tỉnh/Thành không được để trống',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'address' => 'required|string|max:50',
            'district' => 'required',
            'province' => 'required',
        ], $messages);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        // Lấy tên tỉnh và quận/huyện từ code
        $provinces = json_decode(file_get_contents(public_path('data/tinh_tp.json')), true);
        $districts = json_decode(file_get_contents(public_path('data/quan_huyen.json')), true);

        $provinceName = collect($provinces)->firstWhere('code', $request->province)['name'] ?? '';
        $districtName = collect($districts)->firstWhere('code', $request->district)['name'] ?? '';

        School::create([
            'name' => $request->name,
            'address' => $request->address,
            'district' => $districtName,
            'province' => $provinceName,
            // Lưu thêm code nếu cần
            'district_code' => $request->district,
            'province_code' => $request->province,
        ]);

        return redirect()->route('schools.index')
            ->with('success', 'Thêm trường học thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school)
    {
        return view('schools.show', compact('school'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school)
    {
        $provinces = json_decode(file_get_contents(public_path('data/tinh_tp.json')), true);
        $districts = json_decode(file_get_contents(public_path('data/quan_huyen.json')), true);

        return view('schools.edit', [
            'school' => $school,
            'provinces' => $provinces,
            'districts' => array_filter($districts, function($district) use ($school) {
                return isset($district['parent_code']) && $district['parent_code'] == $school->province_code;
            })
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school)
    {
        $messages = [
            'name.required' => 'Tên trường không được để trống',
            'name.max' => 'Tối đa 50 ký tự',
            'address.required' => 'Địa chỉ không được để trống',
            'address.max' => 'Tối đa 50 ký tự',
            'district.required' => 'Quận/Huyện không được để trống',
            'province.required' => 'Tỉnh/Thành không được để trống',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'address' => 'required|string|max:50',
            'district' => 'required',
            'province' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Lấy tên tỉnh và quận/huyện từ code
        $provinces = json_decode(file_get_contents(public_path('data/tinh_tp.json')), true);
        $districts = json_decode(file_get_contents(public_path('data/quan_huyen.json')), true);

        $provinceName = collect($provinces)->firstWhere('code', $request->province)['name'] ?? '';
        $districtName = collect($districts)->firstWhere('code', $request->district)['name'] ?? '';

        $school->update([
            'name' => $request->name,
            'address' => $request->address,
            'district' => $districtName,
            'province' => $provinceName,
            'district_code' => $request->district,
            'province_code' => $request->province,
        ]);

        return redirect()->route('schools.index')
            ->with('success', 'Cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {

    }
    public function emailSettings(School $school)
    {
        return view('schools.email-settings', [
            'school' => $school,
            'emailSettings' => $school->emailSettings ?? new EmailSetting()
        ]);
    }

    public function updateEmailSettings(Request $request, School $school)
    {
        $messages = [
            'host.required' => 'SMTP Host không được để trống',
            'port.required' => 'Port không được để trống',
            'port.numeric' => 'Port phải là số',
            'username.required' => 'Username không được để trống',
            'from_address.required' => 'From Address không được để trống',
            'from_address.email' => 'From Address phải là email hợp lệ',
            'from_name.required' => 'From Name không được để trống',
        ];

        $validator = Validator::make($request->all(), [
            'host' => 'required',
            'port' => 'required|numeric',
            'username' => 'required',
            'password' => 'nullable',
            'encryption' => 'nullable|in:tls,ssl',
            'from_address' => 'required|email',
            'from_name' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'host', 'port', 'username', 'encryption',
            'from_address', 'from_name'
        ]);

        // Chỉ cập nhật password nếu có giá trị
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $school->emailSettings()->updateOrCreate([], $data);

        return back()->with('success', 'Đã cập nhật cấu hình email');
    }
}
