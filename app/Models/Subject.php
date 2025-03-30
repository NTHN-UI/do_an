<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'education_level', 'school_id', 'is_core'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
    public static function getDefaultSubjects(string $level): array
    {
        return match ($level) {
            School::LEVEL_PRIMARY => [
                'Toán', 'Tiếng Việt', 'Đạo đức', 'Tự nhiên và Xã hội',
                'Lịch sử và Địa lý', 'Khoa học', 'Tin học và Công nghệ',
                'Giáo dục thể chất', 'Nghệ thuật (Âm nhạc, Mỹ thuật)',
                'Tiếng Anh'
            ],
            School::LEVEL_SECONDARY => [
                'Toán', 'Ngữ văn', 'Ngoại ngữ', 'Vật lý', 'Hóa học',
                'Sinh học', 'Lịch sử', 'Địa lý', 'Giáo dục công dân',
                'Công nghệ', 'Tin học', 'Giáo dục thể chất', 'Âm nhạc', 'Mỹ thuật'
            ],
            School::LEVEL_HIGH => [
                'Toán', 'Ngữ văn', 'Ngoại ngữ', 'Vật lý', 'Hóa học',
                'Sinh học', 'Lịch sử', 'Địa lý', 'Giáo dục kinh tế và pháp luật',
                'Giáo dục quốc phòng và an ninh', 'Công nghệ', 'Tin học',
                'Giáo dục thể chất', 'Nghệ thuật'
            ],
            default => [],
        };

    }
}


