<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsTemplateExport implements FromArray, WithHeadings
{
    protected $academicYearId;

    public function __construct($academicYearId)
    {
        $this->academicYearId = $academicYearId;
    }

    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'ho_va_ten',
            'ngay_sinh',
            'gioi_tinh',
            'so_dien_thoai',
            'dia_chi',
            'khoi_dang_ky',
            'diem_dau_vao',
            'ten_phu_huynh',
            'sdt_phu_huynh',
            'email_phu_huynh'
        ];
    }
}
