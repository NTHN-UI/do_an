<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GradeTemplateExport implements WithMultipleSheets
{
    protected $students;
    protected $subjects;
    protected $semesterId;
    protected $class;
    protected $academicYearId;

    public function __construct($students, $subjects, $semesterId, $class, $academicYearId)
    {
        $this->students = $students;
        $this->subjects = $subjects;
        $this->semesterId = $semesterId;
        $this->class = $class;
        $this->academicYearId = $academicYearId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet hướng dẫn
        $sheets[] = new GradeTemplateGuideSheet();

        // Sheet nhập điểm cho từng môn
        foreach ($this->subjects as $subject) {
            if (!$subject) continue; // Bỏ qua nếu null

            $sheets[] = new GradeTemplateSubjectSheet(
                $this->students,
                $subject,
                $this->semesterId,
                $this->class,
                $this->academicYearId
            );
        }

        return $sheets;
    }
}

class GradeTemplateGuideSheet implements FromCollection, WithTitle, WithStyles
{
    public function collection()
    {
        return collect([
            ['HƯỚNG DẪN NHẬP ĐIỂM'],
            [''],
            ['1. File này dùng để nhập điểm cho học sinh theo mẫu của Bộ Giáo dục và Đào tạo'],
            ['2. Mỗi môn học có một sheet riêng'],
            ['3. Đối với môn nhập điểm: Nhập điểm số từ 0 đến 10 (có thể nhập 1 chữ số thập phân)'],
            ['4. Đối với môn nhập đạt/chưa đạt: Nhập "Đạt" hoặc "Chưa đạt" (viết liền, không dấu)'],
            ['5. Không thay đổi cấu trúc file, chỉ nhập dữ liệu vào các ô quy định'],
            ['6. Sau khi nhập xong, lưu file và tải lên hệ thống'],
            ['7. Hệ thống sẽ tự động tính điểm trung bình môn học kỳ theo công thức:'],
            ['   - Điểm 15 phút: Hệ số 1'],
            ['   - Điểm 1 tiết: Hệ số 2'],
            ['   - Điểm học kỳ: Hệ số 3'],
            ['8. Đối với môn đạt/chưa đạt, hệ thống sẽ tự động chuyển đổi:'],
            ['   - "Đạt" tương đương điểm 10'],
            ['   - "Chưa đạt" tương đương điểm 0'],
        ]);
    }

    public function title(): string
    {
        return 'Hướng dẫn';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class GradeTemplateSubjectSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $students;
    protected $subject;
    protected $semesterId;
    protected $class;
    protected $academicYearId;

    public function __construct($students, $subject, $semesterId, $class, $academicYearId)
    {
        $this->students = $students;
        $this->subject = $subject;
        $this->semesterId = $semesterId;
        $this->class = $class;
        $this->academicYearId = $academicYearId;
    }
    public function collection()
    {
        $data = [];
        $isTextSubject = $this->subject->education_level === 'primary';

        foreach ($this->students as $student) {
            $row = [
                $student->school_auto_id,
                $student->full_name,
                '', // Điểm 15 phút 1 hoặc Đánh giá 1
                '', // Điểm 15 phút 2 hoặc Đánh giá 2
                '', // Điểm 1 tiết 1 hoặc Đánh giá 3
                '', // Điểm 1 tiết 2 hoặc Đánh giá 4
                '', // Điểm học kỳ hoặc Đánh giá HK
            ];

            $data[] = $row;
        }

        return collect($data);
    }

    public function headings(): array
    {
        $isTextSubject = $this->subject->education_level === 'primary';

        return [
            ['THÔNG TIN LỚP: ' . $this->class->name . ' (Mã: ' . $this->class->school_auto_id . ')'],
            ['NĂM HỌC: ' . $this->academicYearId],
            ['HỌC KỲ: ' . $this->semesterId],
            ['MÔN HỌC: ' . $this->subject->name . ' - Cấp: ' . $this->getEducationLevelName()],
            [''],
            $isTextSubject
                ? [
                'Mã HS',
                'Họ và tên',
                'Đánh giá 1',
                'Đánh giá 2',
                'Đánh giá 3',
                'Đánh giá 4',
                'Đánh giá HK'
            ]
                : [
                'Mã HS',
                'Họ và tên',
                'Điểm 15p lần 1',
                'Điểm 15p lần 2',
                'Điểm 1 tiết lần 1',
                'Điểm 1 tiết lần 2',
                'Điểm học kỳ'
            ]
        ];
    }

    public function title(): string
    {
        // Giới hạn độ dài tên sheet (tối đa 31 ký tự)
        return substr($this->subject->name, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        // Merge các ô tiêu đề
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');
        $sheet->mergeCells('A4:G4');

        // Định dạng tiêu đề
        $sheet->getStyle('A1:G5')->getFont()->setBold(true);

        // Căn giữa các tiêu đề
        $sheet->getStyle('A1:G5')->getAlignment()->setHorizontal('center');

        // Đặt độ rộng cột
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);

        // Đặt kiểu dữ liệu cho các cột điểm
        $isTextSubject = $this->subject->education_level === 'primary';

        if ($isTextSubject) {
            // Thiết lập data validation cho môn đạt/chưa đạt
            $validationValues = ['Đạt', 'Chưa đạt'];

            for ($i = 6; $i <= count($this->students) + 5; $i++) {
                for ($col = 'C'; $col <= 'G'; $col++) {
                    $sheet->getCell("{$col}{$i}")->getDataValidation()
                        ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                        ->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION)
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('Lỗi nhập liệu')
                        ->setError('Giá trị không hợp lệ, chỉ được nhập "Đạt" hoặc "Chưa đạt"')
                        ->setPromptTitle('Chọn giá trị')
                        ->setPrompt('Chọn "Đạt" hoặc "Chưa đạt"')
                        ->setFormula1('"' . implode(',', $validationValues) . '"');
                }
            }
        } else {
            // Thiết lập data validation cho môn nhập điểm (0-10)
            for ($i = 6; $i <= count($this->students) + 5; $i++) {
                for ($col = 'C'; $col <= 'G'; $col++) {
                    $sheet->getCell("{$col}{$i}")->getDataValidation()
                        ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL)
                        ->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN)
                        ->setFormula1('0')
                        ->setFormula2('10')
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Lỗi nhập liệu')
                        ->setError('Điểm phải từ 0 đến 10')
                        ->setPromptTitle('Nhập điểm')
                        ->setPrompt('Nhập điểm từ 0 đến 10 (có thể nhập 1 chữ số thập phân)');
                }
            }
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
        ];
    }
    protected function getEducationLevelName()
    {
        $levels = [
            'secondary' => 'THCS',
            'high' => 'THPT'
        ];

        return $levels[$this->subject->education_level] ?? $this->subject->education_level;
    }
}
