<?php

// app/Imports/StudentsImport.php
namespace App\Imports;

use App\Models\User;
use App\Models\School;
use App\Models\GradeLevel;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected $academicYearId;
    protected $gradeLevelId;
    protected $school;
    protected $gradeNumber;
    protected $errors = [];

    public function __construct($academicYearId, $gradeLevelId)
    {
        $this->academicYearId = $academicYearId;
        $this->gradeLevelId = $gradeLevelId;

        $this->school = School::find(auth()->user()->school_id);
        $gradeLevel = GradeLevel::find($gradeLevelId);
        $this->gradeNumber = $gradeLevel ? $gradeLevel->grade_number : null;
    }
    protected $rowCount = 0;

    public function getRowCount()
    {
        return $this->rowCount;
    }

    public function model(array $row)

    {
        $this->rowCount++;

        // Validate ngày sinh
        $dateOfBirth = $this->transformDate($row['ngay_sinh']);
        if (!$dateOfBirth) {
            throw new \Exception("Dòng {$this->rowCount}: Ngày sinh không hợp lệ");
        }
        // Generate email
        $email = $this->generateEmail($row['ho_va_ten']);

        // Generate password
        $password = Hash::make('12345678'); // Default password

        $studentData = [
            'full_name' => $row['ho_va_ten'],
            'email' => $email,
            'password' => $password,
            'gender' => $row['gioi_tinh'] ?? null,
            'date_of_birth' => $this->transformDate($row['ngay_sinh']),
            'phone' => $row['so_dien_thoai'] ?? null,
            'address' => $row['dia_chi'] ?? null,
            'guardian_name' => $row['ten_phu_huynh'] ?? null,
            'guardian_phone' => $row['sdt_phu_huynh'] ?? null,
            'guardian_email' => $row['email_phu_huynh'] ?? null,
            'role' => User::ROLE_STUDENT,
            'school_id' => $this->school->id,
            'is_active' => true,
            'academic_year_id' => $this->academicYearId,

        ];

        // Add entry score if grade 10
        if ($this->gradeNumber == 10 && isset($row['diem_dau_vao'])) {
            $studentData['entry_score'] = $row['diem_dau_vao'];
        }

        $student = new User($studentData);
        $student->studentGrades()->attach($this->gradeLevelId, [
            'academic_year_id' => $this->academicYearId,
            'school_id' => $this->school->id,
            'grade_id' => $this->gradeLevelId

        ]);
        return $student;
    }

        public function rules(): array
    {
        $rules = [
            'ho_va_ten' => 'required|string|max:50',
            'ngay_sinh' => [
                'required',
                function ($attribute, $value, $fail) {
                    $date = $this->transformDate($value);
                    if (!$date) {
                        $fail('Ngày sinh không hợp lệ. Định dạng phải là dd/mm/yyyy');
                    }
                }
            ],
            'gioi_tinh' => 'nullable|in:Nam,Nữ,Khác',
            'email_phu_huynh' => 'required|email',
        ];

        // Thêm rule cho điểm đầu vào nếu là khối 10
        if ($this->gradeNumber == 10) {
            $rules['diem_dau_vao'] = 'required|numeric|min:0|max:50';
        }

        return $rules;
    }

    public function customValidationMessages()
    {
        return [
            'ho_va_ten.required' => 'Họ và tên là bắt buộc',
            'ho_va_ten.max' => 'Họ và tên không quá 50 ký tự',
            'ngay_sinh.required' => 'Ngày sinh là bắt buộc',
            'ngay_sinh.date' => 'Ngày sinh không hợp lệ',
            'gioi_tinh.in' => 'Giới tính phải là Nam, Nữ hoặc Khác',
            'email_phu_huynh.required' => 'Email phụ huynh là bắt buộc',
            'email_phu_huynh.email' => 'Email phụ huynh không hợp lệ',
            'diem_dau_vao.required' => 'Điểm đầu vào là bắt buộc cho khối 10',
            'diem_dau_vao.numeric' => 'Điểm đầu vào phải là số',
            'diem_dau_vao.min' => 'Điểm đầu vào tối thiểu là 0',
            'diem_dau_vao.max' => 'Điểm đầu vào tối đa là 50',
        ];
    }

    protected function generateEmail($fullName)
    {
        $schoolName = $this->school->name;
        $slug = Str::slug(mb_strtolower($schoolName));
        $slugParts = explode('-', $slug);
        $slugParts = array_slice($slugParts, 1, (count($slugParts) - 1));
        $schoolDomain = join('', $slugParts) . '.edu.vn';

        $nameParts = explode(' ', $fullName);
        $lastName = array_pop($nameParts);
        $lastName = mb_strtolower(Str::ascii($lastName));

        $firstLetters = '';
        foreach ($nameParts as $part) {
            $firstLetters .= mb_substr($part, 0, 1);
        }
        $firstLetters = mb_strtolower(Str::ascii($firstLetters));

        $username = $lastName . '.' . $firstLetters;
        $email = $username . '@' . $schoolDomain;
        $originalEmail = $email;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $username . $counter . '@' . $schoolDomain;
            $counter++;
        }

        return $email;
    }

    protected function transformDate($value)
    {
        // Nếu giá trị rỗng hoặc null
        if (empty($value)) {
            return null;
        }

        // Nếu là số (định dạng Excel)
        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            // Thử định dạng d/m/Y (ví dụ: 15/01/2005)
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            // Thử định dạng Y-m-d (ví dụ: 2005-01-15)
            if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            }

            // Thử định dạng m/d/Y (ví dụ: 1/15/2005)
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('m/d/Y', $value)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            Log::error("Lỗi chuyển đổi ngày sinh: " . $value . " - " . $e->getMessage());
        }

        return null; // Trả về null nếu không chuyển đổi được
    }
}
