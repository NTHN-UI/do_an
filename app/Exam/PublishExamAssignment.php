<?php

namespace App\Exam;

use App\Models\ExamAssignment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class PublishExamAssignment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $assignment;

    public function __construct(ExamAssignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function handle()
    {
        $this->assignment->update(['is_published' => true]);

        // Gửi thông báo cho học sinh
        if ($this->assignment->assignable_type === 'App\Models\ClassModel') {
            $students = $this->assignment->assignable->students;
        } else {
            $students = collect([$this->assignment->assignable]);
        }

        Notification::send($students, new NewExamAssignment($this->assignment));
    }
}
