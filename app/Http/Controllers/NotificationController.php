<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\NotificationRecipient;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\Notification;
use App\Mail\ParentNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->firstOrFail();

        // Lấy lớp chủ nhiệm HIỆN TẠI với tên lớp
        $homeroomClass = $teacher->homeroomClasses()
            ->where('academic_year_id', $currentYear->id)
            ->with(['class' => function($query) {
                $query->select('id', 'name');
            }])
            ->first();

        if (!$homeroomClass) {
            return redirect()->back()
                ->with('error', 'Bạn không chủ nhiệm lớp nào trong năm học này');
        }

        $templates = NotificationTemplate::where('school_id', $teacher->school_id)
            ->where('is_active', true)
            ->get();

        return view('notifications.create', [
            'className' => $homeroomClass->class->name, // Chỉ truyền tên lớp
            'classId' => $homeroomClass->class_id,      // Truyền id lớp
            'templates' => $templates,
            'priorities' => [
                ['value' => 'low', 'label' => 'Thấp'],
                ['value' => 'medium', 'label' => 'Trung bình'],
                ['value' => 'high', 'label' => 'Cao'],
                ['value' => 'urgent', 'label' => 'Khẩn cấp']
            ]
        ]);
    }

    public function store(Request $request)
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->firstOrFail();

        $request->validate([
            'template_id' => 'required|exists:notification_templates,id',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120'
        ]);

        // Kiểm tra lớp chủ nhiệm
        $homeroomClass = $teacher->homeroomClasses()
            ->where('academic_year_id', $currentYear->id)
            ->firstOrFail();

        $notification = Notification::create([
            'sender_id' => $teacher->id,
            'class_id' => $homeroomClass->class_id,
            'template_id' => $request->template_id,
            'academic_year_id' => $currentYear->id,
            'subject' => $request->subject,
            'content' => $request->input('content'),
            'status' => 'draft', // Tạo bản nháp trước
            'priority' => $request->priority,
        ]);

        // Xử lý file đính kèm
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('notifications/attachments');
                $notification->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize()
                ]);
            }
        }

        // Chuyển hướng đến trang preview
        return redirect()->route('notifications.preview', $notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function preview(Notification $notification)
    {

        if (!auth()->user()->isTeacher()) {
            abort(403, 'Chỉ giáo viên mới được xem trang này');
        }

        if (!$notification->class) {
            return redirect()->back()
                ->with('error', 'Lớp học không tồn tại hoặc đã bị xóa');
        }

        $studentsWithoutEmail = $notification->class->students()
            ->where(function ($query) {
                $query->whereNull('guardian_email')
                    ->orWhere('guardian_email', '');
            })
            ->get();

        return view('notifications.preview', [
            'notification' => $notification,
            'studentsWithoutEmail' => $studentsWithoutEmail
        ]);
    }

    public function send(Notification $notification)
    {
        $teacher = Auth::user();

        // Kiểm tra quyền
        if (!$teacher->isHomeroomTeacherOfClass($notification->class_id)) {
            return redirect()->back()
                ->with('error', 'Bạn không phải giáo viên chủ nhiệm lớp này');
        }

        // Lấy danh sách học sinh
        $recipients = ClassModel::find($notification->class_id)
            ->students()
            ->whereNotNull('guardian_email')
            ->where('guardian_email', '!=', '')
            ->get();

        if ($recipients->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Không có học sinh nào có email phụ huynh hợp lệ');
        }

        // Cấu hình email
        $emailSettings = $notification->class->school->emailSettings;

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => $emailSettings->host,
                'port' => $emailSettings->port,
                'username' => $emailSettings->username,
                'password' => $emailSettings->password,
                'encryption' => $emailSettings->encryption,
            ],
            'mail.from' => [
                'address' => $emailSettings->from_address,
                'name' => $emailSettings->from_name,
            ]
        ]);
        Mail::purge();
        // Gửi email
        $sentCount = 0;
        $failedCount = 0;
        $failedRecipients = [];

        foreach ($recipients as $student) {
            try {
                Log::info('Đang gửi mail cho: ' . $student->guardian_email);
                Mail::to($student->guardian_email)
                    ->send(new ParentNotification($notification, $student));
                if ($recipients->count() > 1) {
                    sleep(2);
                }
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'student_id' => $student->id,
                    'guardian_email' => $student->guardian_email,
                    'is_sent' => true,
                    'sent_at' => now()
                ]);
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Lỗi gửi mail cho: ' . $student->guardian_email . ' - ' . $e->getMessage());
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'student_id' => $student->id,
                    'guardian_email' => $student->guardian_email,
                    'is_sent' => false,
                    'error_message' => $e->getMessage()
                ]);
                $failedCount++;
                $failedRecipients[] = [
                    'student' => $student->full_name,
                    'email' => $student->guardian_email,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Cập nhật trạng thái đã gửi
        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount
        ]);

        // Chuyển hướng về trang lịch sử
        return redirect()->route('notifications.history')
            ->with('success', "Đã gửi thành công đến $sentCount phụ huynh")
            ->with('failed', $failedRecipients);
    }

    public function history()
    {
        $teacher = Auth::user();
        $notifications = Notification::where('sender_id', $teacher->id)
            ->with(['class', 'template'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('notifications.history', ['notifications' => $notifications]);
    }

    public function getTemplateContent(NotificationTemplate $template)
    {
        return response()->json([
            'subject' => $template->subject_template,
            'content' => $template->body_template,
            'variables' => $template->variables
        ]);
    }
}
