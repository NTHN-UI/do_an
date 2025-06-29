<?php

namespace App\Mail;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class ParentNotification extends Mailable
{
    use Queueable, SerializesModels;


    public $notification;
    public $student;
    public $mailData;
    public function __construct(Notification $notification, User $student)
    {
        $this->notification = $notification;
        $this->student = $student;

        $this->mailData = [
            'notification' => $notification,
            'student' => $student,
            'subject' => $this->replaceVariables($notification->subject, $notification, $student),
            'content' => $this->replaceVariables($notification->content, $notification, $student)
        ];
    }
    public function build()
    {
        return $this->view('notifications.parent_notification')
            ->subject($this->mailData['subject'])
            ->with($this->mailData);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailData['subject'],
            from: new Address(
                $this->notification->class->school->emailSettings->from_address,
                $this->notification->class->school->emailSettings->from_name
            )
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'notifications.parent_notification',
            with: [
                'notification' => $this->notification,
                'student' => $this->student,
                'subject' => $this->mailData['subject'],
                'content' => $this->mailData['content']
            ]
        );
    }

    protected function replaceVariables($text, $notification, $student)
    {
        $replacements = [
            '{TEN_HOC_SINH}' => $student->full_name,
            '{LOP}' => $notification->class->name,
            '{GIAO_VIEN}' => $notification->sender->full_name,
        ];

        if (!empty($notification->custom_variables)) {
            $customVars = json_decode($notification->custom_variables, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $replacements = array_merge($replacements, $customVars);
            }
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $text
        );
    }
    public function attachments()
    {
        $attachments = [];

        foreach ($this->notification->attachments as $attachment) {
            $attachments[] = Attachment::fromStorage($attachment->file_path)
                ->as($attachment->file_name)
                ->withMime($attachment->mime_type);
        }

        return $attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    /**
     * Get the message envelope.
     */

}
