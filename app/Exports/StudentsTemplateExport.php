<?php

namespace App\Exports;

use App\Models\GradeLevel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsTemplateExport implements FromArray, WithHeadings
{
    protected $gradeLevelId;

    public function __construct($gradeLevelId)
    {
        $this->gradeLevelId = $gradeLevelId;
    }

    public function array(): array
    {
        return [
        ];
    }

    public function headings(): array
    {
        $headings = [
            'ho_va_ten',
            'ngay_sinh',
            'gioi_tinh',
            'so_dien_thoai',
            'dia_chi',
            'ten_phu_huynh',
            'sdt_phu_huynh',
            'email_phu_huynh',
        ];

        $gradeLevel = GradeLevel::find($this->gradeLevelId);
        if ($gradeLevel && $gradeLevel->grade_number == 10) {
            $headings[] = 'diem_dau_vao';
        }

        return $headings;
    }
}
