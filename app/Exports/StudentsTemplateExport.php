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
                'Nguyễn Văn A',
                '0987654321',
                'Nam',
                '15/05/2010',
                '123 Đường ABC, Quận 1, TP.HCM',
            ],
            [
                'Trần Thị B',
                '0987654322',
                'Nữ',
                '20/08/2010',
                '456 Đường XYZ, Quận 2, TP.HCM',
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
            ]
        ];

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
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');

        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']]],
            2 => ['font' => ['italic' => true]],
            4 => ['font' => ['bold' => true]],
            'A:E' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => 'top'
                ]
            ]
        ];
    }
}
