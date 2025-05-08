<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        return view('schools.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'name.required' => 'Tên trường không được để trống',
            'name.max' => 'Tên trường không được vượt quá 50 ký tự',
            'address.required' => 'Địa chỉ không được để trống',
            'address.max' => 'Địa chỉ không được vượt quá 50 ký tự',
            'district.required' => 'Quận/Huyện không được để trống',
            'district.max' => 'Quận/Huyện không được vượt quá 50 ký tự',
            'province.required' => 'Tỉnh/Thành không được để trống',
            'province.max' => 'Tỉnh/Thành không được vượt quá 50 ký tự',


        ];
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'address' => 'required|string|max:50',
            'district' => 'required|max:50',
            'province' => 'required|max:50',
        ], $messages);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        School::create($request->all());

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
        return view('schools.edit', compact('school'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school)
    {
        $messages = [
            'name.required' => 'Tên trường không được để trống',
            'name.max' => 'Tên trường không được vượt quá 50 ký tự',
            'address.required' => 'Địa chỉ không được để trống',
            'address.max' => 'Địa chỉ không được vượt quá 50 ký tự',
            'district.required' => 'Quận/Huyện không được để trống',
            'district.max' => 'Quận/Huyện không được vượt quá 50 ký tự',
            'province.required' => 'Tỉnh/Thành không được để trống',
            'province.max' => 'Tỉnh/Thành không được vượt quá 50 ký tự',


        ];
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'address' => 'required|string|max:50',
            'district' => 'required|max:50',
            'province' => 'required|max:50'
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $school->update($request->all());

        return redirect()->route('schools.index')
            ->with('success', 'Cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {

    }
}
