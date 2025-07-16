<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $schoolId;
    protected $academicYearId;
    protected $gradeLevelId;

    public function __construct($schoolId, $academicYearId = null, $gradeLevelId = null)
    {
        $this->schoolId = $schoolId;
        $this->academicYearId = $academicYearId;
        $this->gradeLevelId = $gradeLevelId;
    }

    public function collection()
    {
        $query = User::where('role', User::ROLE_STUDENT)
            ->where('school_id', $this->schoolId)
            ->with(['school', 'studentClasses.gradeLevel']);

        if ($this->academicYearId) {
            $query->whereHas('studentAcademicYears', function($q) {
                $q->where('academic_year_id', $this->academicYearId);
            });
        }

        if ($this->gradeLevelId) {
            $query->whereHas('studentClasses', function($q) {
                $q->where('grade_level_id', $this->gradeLevelId);
            });
        }

        return $query->orderBy('full_name')->get();
    }

    public function headings(): array
    {
        $headings = [
            'Họ tên',
            'Số điện thoại',
            'Giới tính',
            'Ngày sinh',
            'Địa chỉ',
            'Khối đăng ký',
            'Tên phụ huynh',
            'Email phụ huynh',
            'Số điện thoại phụ huynh',

        ];

        if ($this->gradeLevelId == 10) {
            $headings[] = 'Điểm đầu vào';
        }

        return $headings;

    }

    public function map($student): array
    {
        $data = [
            $student->full_name,
            $student->phone,
            $student->gender,
            $student->date_of_birth?->format('d/m/Y'),
            $student->address,
            $student->exam_block,
            $student->guardian_name,
            $student->guardian_email,
            $student->guardian_phone

        ];

        if ($this->gradeLevelId == 10) {
            $data[] = $student->entry_score;
        }

        return $data;
    }
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
