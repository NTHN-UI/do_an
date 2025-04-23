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
        return [
            'ID',
            'Họ và tên',
            'Email',
            'Số điện thoại',
            'Giới tính',
            'Ngày sinh',
            'Địa chỉ',
            'Tên phụ huynh',
            'SĐT phụ huynh',
            'Email phụ huynh',
            'Trường',
            'Lớp',
            'Khối',
            'Năm học',
            'Trạng thái'
        ];
    }

    public function map($student): array
    {
        $currentClass = $student->studentClasses->first();

        return [
            $student->school_auto_id,
            $student->full_name,
            $student->email,
            $student->phone,
            $student->gender,
            $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : '',
            $student->address,
            $student->guardian_name,
            $student->guardian_phone,
            $student->guardian_email,
            $student->school->name ?? '',
            $currentClass->name ?? '',
            $currentClass->gradeLevel->name ?? '',
            $currentClass->academicYear->name ?? '',
            $student->is_active ? 'Hoạt động' : 'Ngừng'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
