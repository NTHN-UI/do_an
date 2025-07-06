<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'class_id',
        'template_id',
        'academic_year_id',
        'subject',
        'content',
        'status',
        'sent_at',
        'sent_count',
        'failed_count'
    ];


    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = $value;
    }

    public function getContentAttribute($value)
    {
        return $value;
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function attachments()
    {
        return $this->hasMany(NotificationAttachment::class);
    }


}
