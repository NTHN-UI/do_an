<?php

namespace App\Exports;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $academicYearId;
    protected $gradeLevelId;

    public function __construct($academicYearId, $gradeLevelId)
    {
        $this->academicYearId = $academicYearId;
        $this->gradeLevelId = $gradeLevelId;
    }

    public function array(): array
    {
        return [
            [
                'Nguyễn Văn A', // Họ tên
                '0987654321', // SĐT
                'Nam', // Giới tính
                '15/05/2010', // Ngày sinh
                '123 Đường ABC, Quận 1, TP.HCM', // Địa chỉ
                'Nguyễn Văn Bố', // Tên phụ huynh
                'bonguyenvana@example.com', // Email phụ huynh
                '0987654333' // SĐT phụ huynh
            ],
            [
                'Trần Thị B',
                '0987654322',
                'Nữ',
                '20/08/2010',
                '456 Đường XYZ, Quận 2, TP.HCM',
                'Trần Thị Mẹ',
                'metranthib@example.com',
                '0987654334'
            ]
        ];
    }

    public function headings(): array
    {
        $academicYear = AcademicYear::find($this->academicYearId);
        $gradeLevel = GradeLevel::find($this->gradeLevelId);

        $headings = [
            [
                'Họ tên*',
                'Số điện thoại',
                'Giới tính (Nam/Nữ/Khác)',
                'Ngày sinh (dd/mm/yyyy)',
                'Địa chỉ',
                'Tên phụ huynh',
                'Email phụ huynh',
                'Số điện thoại phụ huynh'
            ]
        ];
        if ($this->gradeLevelId == 10) {
            $headings[] = 'Điểm đầu vào';
        }

        if ($academicYear || $gradeLevel) {
            $headings[] = [];

            if ($academicYear) {
                $headings[] = ['Năm học: ' . ($academicYear->year ?? $academicYear->name ?? 'N/A')];
            }

            if ($gradeLevel) {
                $headings[] = ['Khối: ' . ($gradeLevel->grade_number ?? $gradeLevel->name ?? 'N/A')];
            }
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        // Merge instruction rows
//        $sheet->mergeCells('A1:E1');
//        $sheet->mergeCells('A2:E2');

        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']]],
            2 => ['font' => ['italic' => true]],
            4 => ['font' => ['bold' => true]],
            'A:H' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => 'top'
                ]
            ]
        ];
    }
}
