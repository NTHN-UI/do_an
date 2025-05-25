<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'student_id',
        'guardian_email',
        'is_sent',
        'sent_at',
        'error_message'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_sent' => 'boolean'
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
