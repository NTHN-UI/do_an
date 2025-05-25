<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['exam_id', 'content'];

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
