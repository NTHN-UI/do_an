<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'district',
        'province',

    ];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',

    ];
    const HIGH_SCHOOL_SUBJECTS = [
        'Toán', 'Ngữ văn', 'Ngoại ngữ', 'Vật lý', 'Hóa học',
        'Sinh học', 'Lịch sử', 'Địa lý', 'Giáo dục kinh tế và pháp luật',
        'Giáo dục quốc phòng và an ninh', 'Công nghệ', 'Tin học',
        'Giáo dục thể chất', 'Nghệ thuật'
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

    // Tự động tạo môn học khi tạo trường
    protected static function booted()
    {
        static::created(function (School $school) {
            foreach (self::HIGH_SCHOOL_SUBJECTS as $name) {
                $school->subjects()->create(['name' => $name]);
            }
        });
    }

}
