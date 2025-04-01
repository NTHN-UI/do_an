<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Subject;
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

        $materials = Material::with(['subject', 'teacher'])
            ->when($search, function($query) use ($search) {
                return $query->where('title', 'like', "%$search%");
            })
            ->when($subjectId, function($query) use ($subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->when($teacherId, function($query) use ($teacherId) {
                return $query->where('teacher_id', $teacherId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $subjects = Subject::all();
        $teachers = User::where('role', User::ROLE_TEACHER)->get();

        return view('materials.index', compact('materials', 'subjects', 'teachers', 'search', 'subjectId', 'teacherId'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subjects = Subject::all();
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
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $filePath = $request->file('file')->store('materials');

        Material::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'subject_id' => $request->subject_id,
            'teacher_id' => auth()->id() ?? 1// Sau này sẽ thay bằng giáo viên thực tế
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
