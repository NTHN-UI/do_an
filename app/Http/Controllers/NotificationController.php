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
use Illuminate\Support\Facades\Storage;
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


        $homeroomClass = $teacher->homeroomClasses()
            ->where('academic_year_id', $currentYear->id)
            ->with(['class' => function($query) {
                $query->select('id', 'name');
            }])
            ->first();


        $templates = NotificationTemplate::where('school_id', $teacher->school_id)
            ->where('is_active', true)
            ->get();

        return view('notifications.create', [
            'className' => $homeroomClass->class->name,
            'classId' => $homeroomClass->class_id,
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
            'status' => 'draft',
            'priority' => $request->priority,
        ]);

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


    public function edit(Notification $notification)
    {

        $teacher = Auth::user();
        $currentYear = AcademicYear::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->firstOrFail();

        $homeroomClass = $teacher->homeroomClasses()
            ->where('academic_year_id', $currentYear->id)
            ->with(['class' => function($query) {
                $query->select('id', 'name');
            }])
            ->first();


        $templates = NotificationTemplate::where('school_id', $teacher->school_id)
            ->where('is_active', true)
            ->get();

        return view('notifications.edit', [
            'notification' => $notification,
            'className' => $homeroomClass->class->name,
            'classId' => $homeroomClass->class_id,
            'templates' => $templates,
            'priorities' => [
                ['value' => 'low', 'label' => 'Thấp'],
                ['value' => 'medium', 'label' => 'Trung bình'],
                ['value' => 'high', 'label' => 'Cao'],
                ['value' => 'urgent', 'label' => 'Khẩn cấp']
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {

        $request->validate([
            'template_id' => 'required|exists:notification_templates,id',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120'
        ]);

        $notification->update([
            'template_id' => $request->template_id,
            'subject' => $request->input('subject'),
            'content' => $request->input('content'),
            'priority' => $request->priority,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($notification->attachments as $attachment) {
                Storage::delete($attachment->file_path);
                $attachment->delete();
            }

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
        return redirect()->route('notifications.preview', $notification)
            ->with('success', 'Thông báo đã được cập nhật thành công!');
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

        return view('notifications.preview', [
            'notification' => $notification,
        ]);
    }

    public function send(Notification $notification)
    {
        $teacher = Auth::user();
        $notification->load('attachments');

        $recipients = ClassModel::find($notification->class_id)
            ->students()
            ->whereNotNull('guardian_email')
            ->where('guardian_email', '!=', '')
            ->get();

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
        $sentCount = 0;
        $failedCount = 0;
        $failedRecipients = [];

        foreach ($recipients as $student) {
            try {
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

        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount
        ]);

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
