<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $search = $request->input('search');
        $subjectId = $request->input('subject_id');

        // GIÁO VIÊN: chỉ xem tài liệu của mình
        if (Auth::user()->isTeacher()) {
            $materials = Material::where('teacher_id', Auth::id());
            $subjects = Subject::whereIn('id',
                TeacherAssignment::where('teacher_id', Auth::id())->pluck('subject_id')
            )->get();
        }
        // HỌC SINH: xem tất cả tài liệu (hoặc có thể lọc theo giáo viên dạy mình)
        elseif (Auth::user()->isStudent()) {
            $materials = Material::query();

            // Nếu muốn học sinh chỉ xem tài liệu của giáo viên dạy mình:

            $teacherIds = TeacherAssignment::whereIn('class_id',
                Auth::user()->classes()->pluck('classes.id')
            )->pluck('teacher_id')->unique();
            $materials = Material::whereIn('teacher_id', $teacherIds);


            $subjects = Subject::all();
        }
        // ADMIN: xem tất cả
        else {
            $materials = Material::query();
            $subjects = Subject::all();
        }

        // Áp dụng filter chung
        $materials = $materials->with(['subject', 'teacher'])
            ->when($search, function($query, $search) {
                return $query->where('title', 'like', "%$search%");
            })
            ->when($subjectId, function($query, $subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('materials.index', compact('materials', 'subjects', 'search', 'subjectId'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subjects = Auth::user()->isTeacher()
            ? Subject::whereIn('id', TeacherAssignment::where('teacher_id', Auth::id())->pluck('subject_id'))->get()
            : Subject::all();

        return view('materials.create', compact('subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
            'subject_id' => [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) {
                    if (Auth::user()->isTeacher() &&
                        !TeacherAssignment::where('teacher_id', Auth::id())
                            ->where('subject_id', $value)
                            ->exists()) {
                        $fail('Bạn không được phép tải lên tài liệu cho môn học này.');
                    }
                }
            ]
        ]);

        $filePath = $request->file('file')->store('materials');

        Material::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'subject_id' => $request->subject_id,
            'teacher_id' => Auth::id() // Luôn là giáo viên hiện tại
        ]);

        return redirect()->route('materials.index')->with('success', 'Tài liệu đã được tải lên thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        return view('materials.show', compact('material'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Material $material)
    {
        $subjects = Subject::all();
        return view('materials.edit', compact('material', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Material $material)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'subject_id' => $request->subject_id
        ];

        if ($request->hasFile('file')) {
            // Xóa file cũ
            Storage::delete($material->file_path);
            // Lưu file mới
            $data['file_path'] = $request->file('file')->store('materials');
        }

        $material->update($data);

        return redirect()->route('materials.index')->with('success', 'Cập nhật tài liệu thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        Storage::delete($material->file_path);
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'Tài liệu đã được xóa!');
    }
    public function download(Material $material)
    {
        return Storage::download($material->file_path, $material->title);

    }

}
