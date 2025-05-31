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
    protected $textBasedSubjects = ['Giáo dục quốc phòng và an ninh', 'Giáo dục thể chất', 'Nghệ thuật'];

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
        $isTextSubject = in_array($this->subject->name, $this->textBasedSubjects);


        foreach ($this->students as $student) {
            $row = [
                $student->id,
                $student->full_name,
                '', // Điểm 15p lần 1 (thay cho điểm miệng)
                '', // Điểm 15p lần 2 (thay cho điểm thực hành)
                '', // Điểm 15p lần 3
                '', // Điểm 1 tiết
                '', // Điểm cuối kỳ
            ];

            $data[] = $row;
        }

        return collect($data);
    }

    public function headings(): array
    {
        $isTextSubject = in_array($this->subject->name, $this->textBasedSubjects);


        return [
            ['THÔNG TIN LỚP: ' . $this->class->name . ' (Mã: ' . $this->class->id . ')'],
            ['NĂM HỌC: ' . $this->academicYearId],
            ['HỌC KỲ: ' . $this->semesterId],
            ['MÔN HỌC: ' . $this->subject->name . ' (Mã: ' . $this->subject->id . ')'],
            [''],
            [
                'Mã HS',
                'Họ và tên',
                'Điểm 15p lần 1' . ($isTextSubject ? ' (Đạt/CĐ)' : ''),
                'Điểm 15p lần 2' . ($isTextSubject ? ' (Đạt/CĐ)' : ''),
                'Điểm 15p lần 3' . ($isTextSubject ? ' (Đạt/CĐ)' : ''),
                'Điểm 1 tiết' . ($isTextSubject ? ' (Đạt/CĐ)' : ''),
                'Điểm học kỳ' . ($isTextSubject ? ' (Đạt/CĐ)' : '')
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
        // Thiết lập data validation
        $isTextSubject = in_array($this->subject->name, $this->textBasedSubjects);
        $lastRow = count($this->students) + 6;

        if ($isTextSubject) {
            // Thiết lập dropdown cho môn đạt/chưa đạt
            $validation = $sheet->getCell('C7')->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Lỗi nhập liệu');
            $validation->setError('Chỉ được nhập "Đạt" hoặc "Chưa đạt"');
            $validation->setPromptTitle('Chọn giá trị');
            $validation->setPrompt('Chọn "Đạt" hoặc "Chưa đạt"');
            $validation->setFormula1('"Đạt,Chưa đạt"');

            // Áp dụng cho tất cả các ô nhập liệu
            for ($row = 7; $row <= $lastRow; $row++) {
                for ($col = 'C'; $col <= 'G'; $col++) {
                    $sheet->getCell("{$col}{$row}")->setDataValidation(clone $validation);
                }
            }
        } else {
            // Thiết lập validation cho môn nhập điểm số
            $validation = $sheet->getCell('C7')->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
            $validation->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
            $validation->setFormula1('0');
            $validation->setFormula2('10');
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Lỗi nhập liệu');
            $validation->setError('Điểm phải từ 0 đến 10');
            $validation->setPromptTitle('Nhập điểm');
            $validation->setPrompt('Nhập điểm từ 0 đến 10 (có thể nhập 1 chữ số thập phân)');

            // Áp dụng cho tất cả các ô nhập liệu
            for ($row = 7; $row <= $lastRow; $row++) {
                for ($col = 'C'; $col <= 'G'; $col++) {
                    $sheet->getCell("{$col}{$row}")->setDataValidation(clone $validation);
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
}
