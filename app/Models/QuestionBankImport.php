<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankImport extends Model
{
    protected $fillable = ['file_path', 'imported_count', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
