<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private function getDocumentValidationRules(bool $isUpdate = false, Document $document = null): array
    {
        $rules = [
            'title' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}0-9\s\-\_.]+$/u'
            ],
            'description' => 'nullable|string|max:255',
            'subject_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (Auth::user()->isTeacher() && !TeacherAssignment::where('teacher_id', Auth::id())
                            ->where('subject_id', $value)
                            ->exists()) {
                        $fail('Bạn không được phép tải lên tài liệu cho môn học này.');
                    }
                    if (!Subject::where('id', $value)
                        ->where('school_id', Auth::user()->school_id)
                        ->exists()) {
                        $fail('Môn học không thuộc về trường của bạn.');
                    }
                }
            ]
        ];
        if ($isUpdate) {
            $rules['file'] = 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240';
        } else {
            $rules['file'] = 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240';
        }

        return $rules;
    }
    private function getDocumentValidationMessages(): array
    {
        return [
            'title.required' => 'Tiêu đề tài liệu không được để trống.',
            'title.max' => 'Tiêu đề tài liệu tối đa :max ký tự.',
            'title.regex' => 'Tiêu đề tài liệu chỉ chứa chữ cái, số, khoảng trắng, dấu gạch ngang, gạch dưới và dấu chấm.',

            'file.required' => 'Vui lòng chọn file tài liệu.',
            'file.mimes' => 'File tài liệu phải có định dạng PDF, Word, PowerPoint hoặc Excel.',
            'file.max' => 'Kích thước file tối đa là 10MB.',

            'subject_id.required' => 'Môn học không được để trống.',

            'description.max' => 'Mô tả tối đa :max ký tự.',
        ];
    }
    public function index(Request $request)
    {
        $search = $request->input('search');
        $subjectId = $request->input('subject_id');
        $teacherId = $request->input('teacher_id');

        $documents = Document::whereHas('teacher', function ($query) {
            $query->where('school_id', Auth::user()->school_id);
        });

        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('school_id', Auth::user()->school_id)
            ->get();

        if (Auth::user()->isTeacher()) {
            $documents = $documents->where('teacher_id', Auth::id());

            $subjects = Subject::whereIn('id',
                TeacherAssignment::where('teacher_id', Auth::id())
                    ->pluck('subject_id')
            )->get();
        }
        elseif (Auth::user()->isStudent()) {

            $studentClassIds = Auth::user()->studentClasses()->pluck('class_id');

            $allowedTeacherIds = TeacherAssignment::whereIn('class_id', $studentClassIds)
                ->pluck('teacher_id')
                ->unique();

            $documents = $documents->whereIn('teacher_id', $allowedTeacherIds);
            $teachers = $teachers->whereIn('id', $allowedTeacherIds);

            $subjects = Subject::all();
        }
        else {
            $subjects = Subject::all();
        }

        $documents = $documents->with(['subject', 'teacher'])
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%$search%");
            })
            ->when($subjectId, function ($query, $subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->when($teacherId, function ($query, $teacherId) {
                return $query->where('teacher_id', $teacherId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('documents.index', compact('documents', 'subjects', 'teachers', 'search', 'subjectId', 'teacherId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subjects = Auth::user()->isTeacher()
            ? Subject::whereIn('id',
                TeacherAssignment::where('teacher_id', Auth::id())
                    ->pluck('subject_id')
            )->get()
            : Subject::where('school_id', Auth::user()->school_id)->get();

        return view('documents.create', compact('subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->getDocumentValidationRules(false),
            $this->getDocumentValidationMessages()
        );
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $filePath = $request->file('file')->store('documents');

        Document::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'subject_id' => $request->subject_id,
            'school_id' => Auth::user()->school_id,
            'teacher_id' => Auth::id()
        ]);

        return redirect()->route('documents.index')->with('success', 'Tài liệu đã được tải lên thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {

        $subjects = Auth::user()->isTeacher()
            ? Subject::whereIn('id',
                TeacherAssignment::where('teacher_id', Auth::id())
                    ->pluck('subject_id')
            )->get()
            : Subject::where('school_id', Auth::user()->school_id)->get();

        return view('documents.edit', compact('document', 'subjects'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        if ($document->school_id !== Auth::user()->school_id || $document->teacher_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa tài liệu này.');
        }

        $validator = Validator::make(
            $request->all(),
            $this->getDocumentValidationRules(true, $document), // isUpdate = true cho update
            $this->getDocumentValidationMessages()
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only(['title', 'description', 'subject_id']);

        if ($request->hasFile('file')) {
            Storage::delete($document->file_path);
            $data['file_path'] = $request->file('file')->store('documents');
        }

        $document->update($data);

        return redirect()->route('documents.index')->with('success', 'Cập nhật tài liệu thành công!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        if ($document->school_id !== Auth::user()->school_id || $document->teacher_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xóa tài liệu này.');
        }

        Storage::delete($document->file_path);
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Tài liệu đã được xóa!');

}

    public function download(Document $document)
    {
        return Storage::download($document->file_path, $document->title);

    }

}
