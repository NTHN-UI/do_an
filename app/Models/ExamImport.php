<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamImport extends Model
{
    protected $fillable = [
        'exam_id', 'file_path', 'original_name',
        'file_size', 'import_status', 'import_log'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
