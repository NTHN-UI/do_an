<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAttachment extends Model
{
    protected $fillable = [
        'notification_id',
        'file_name',
        'file_path',
        'mime_type',
        'size'
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
