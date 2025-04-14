<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  School extends Model
{
    use HasFactory;
    // Các cấp học
    const LEVEL_SECONDARY = 'secondary';
    const LEVEL_HIGH = 'high';

    protected $fillable = [
        'name',
        'district',
        'province',
        'education_level'
    ];
    protected $appends = ['education_level_name'];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
        'education_level' => 'string'

    ];
    // Quan hệ với admin trường
    public function school_admins()
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_SCHOOL_ADMIN);
    }

    // Quan hệ với giáo viên
    public function teachers()
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_TEACHER);
    }

    // Quan hệ với học sinh
    public function students()
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_STUDENT);
    }

    // School.php
    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scope active
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Lấy tên cấp học
    public function getEducationLevelNameAttribute()
    {
        return match($this->education_level) {
            self::LEVEL_SECONDARY => 'Trung học cơ sở',
            self::LEVEL_HIGH => 'Trung học phổ thông',
            default => 'Không xác định',
        };
    }
    protected static function booted()
    {
        static::created(function (School $school) {
            $subjects = match ($school->education_level) {
                self::LEVEL_SECONDARY => [
                    'Toán', 'Ngữ văn', 'Ngoại ngữ', 'Vật lý', 'Hóa học',
                    'Sinh học', 'Lịch sử', 'Địa lý', 'Giáo dục công dân',
                    'Công nghệ', 'Tin học', 'Giáo dục thể chất', 'Âm nhạc', 'Mỹ thuật'
                ],
                self::LEVEL_HIGH => [
                    'Toán', 'Ngữ văn', 'Ngoại ngữ', 'Vật lý', 'Hóa học',
                    'Sinh học', 'Lịch sử', 'Địa lý', 'Giáo dục kinh tế và pháp luật',
                    'Giáo dục quốc phòng và an ninh', 'Công nghệ', 'Tin học',
                    'Giáo dục thể chất', 'Nghệ thuật'
                ],
                default => [],
            };

            foreach($subjects as $name) {
                $school->subjects()->create(compact('name'));
            }
        });
    }


}
