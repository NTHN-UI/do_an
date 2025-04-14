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
        $teacherId = $request->input('teacher_id');

        // Lấy tất cả tài liệu thuộc trường hiện tại
        $materials = Material::whereHas('teacher', function($query) {
            $query->where('school_id', Auth::user()->school_id);
        });

        // Lấy danh sách giáo viên cùng trường
        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('school_id', Auth::user()->school_id)
            ->get();

        // GIÁO VIÊN: chỉ xem tài liệu của mình
        if (Auth::user()->isTeacher()) {
            $materials = $materials->where('teacher_id', Auth::id());

            // Chỉ hiển thị môn học mà giáo viên này dạy
            $subjects = Subject::whereIn('id',
                TeacherAssignment::where('teacher_id', Auth::id())
                    ->pluck('subject_id')
            )->get();
        }
        // HỌC SINH: chỉ xem tài liệu của giáo viên dạy lớp mình
        elseif (Auth::user()->isStudent()) {
            // Lấy danh sách lớp học của học sinh
            $studentClassIds = Auth::user()->studentClasses()->pluck('class_id');

            // Lấy danh sách giáo viên dạy các lớp của học sinh
            $allowedTeacherIds = TeacherAssignment::whereIn('class_id', $studentClassIds)
                ->pluck('teacher_id')
                ->unique();

            $materials = $materials->whereIn('teacher_id', $allowedTeacherIds);

            // Lọc danh sách giáo viên chỉ hiển thị những người dạy học sinh này
            $teachers = $teachers->whereIn('id', $allowedTeacherIds);

            $subjects = Subject::all();
        }
        // ADMIN: xem tất cả tài liệu trong trường
        else {
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
            ->when($teacherId, function($query, $teacherId) {
                return $query->where('teacher_id', $teacherId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('materials.index', compact('materials', 'subjects', 'teachers', 'search', 'subjectId', 'teacherId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Giáo viên chỉ được chọn môn học mình dạy
        $subjects = Auth::user()->isTeacher()
            ? Subject::whereIn('id',
                TeacherAssignment::where('teacher_id', Auth::id())
                    ->pluck('subject_id')
            )->get()
            : Subject::where('school_id', Auth::user()->school_id)->get();

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
                    // Kiểm tra giáo viên có dạy môn này không
                    if (Auth::user()->isTeacher() &&
                        !TeacherAssignment::where('teacher_id', Auth::id())
                            ->where('subject_id', $value)
                            ->exists()) {
                        $fail('Bạn không được phép tải lên tài liệu cho môn học này.');
                    }
                    // Kiểm tra môn học có thuộc trường không
                    if (!Subject::where('id', $value)
                        ->where('school_id', Auth::user()->school_id)
                        ->exists()) {
                        $fail('Môn học không thuộc về trường của bạn.');
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
            'teacher_id' => Auth::id()
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

        // Giáo viên chỉ được chọn môn học mình dạy
        $subjects = Auth::user()->isTeacher()
            ? Subject::whereIn('id',
                TeacherAssignment::where('teacher_id', Auth::id())
                    ->pluck('subject_id')
            )->get()
            : Subject::where('school_id', Auth::user()->school_id)->get();

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
            'subject_id' => [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) {
                    // Kiểm tra tương tự như store
                }
            ]
        ]);

        $data = $request->only(['title', 'description', 'subject_id']);

        if ($request->hasFile('file')) {
            Storage::delete($material->file_path);
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
