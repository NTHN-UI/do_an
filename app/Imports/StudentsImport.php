<?php

namespace App\Imports;

use App\Models\User;
use App\Models\School;
use App\Models\GradeLevel;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Validators\Failure;


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

        $dateOfBirth = $this->transformDate($row['ngay_sinh']);
        if (!$dateOfBirth) {
            throw new \Exception("Dòng {$this->rowCount}: Ngày sinh không hợp lệ");
        }
        $email = $this->generateEmail($row['ho_va_ten']);

        $password = Hash::make('12345678');

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

        if ($this->gradeNumber == 10 && isset($row['diem_dau_vao'])) {
            $studentData['entry_score'] = $row['diem_dau_vao'];
        }

        $student = new User($studentData);
        $student->save();
        DB::transaction(function () use ($student) {
            DB::table('grade_users')->insert([
                'user_id' => $student->id,
                'grade_id' => $this->gradeLevelId,
                'academic_year_id' => $this->academicYearId,
                'school_id' => $this->school->id
        ]);
        });

        return $student;
    }

        public function rules(): array
    {
        $rules = [
            '*.ho_va_ten' => [
                'required',
                'string',
                'max:50',
                'regex:/^[\p{L}\s\-]+$/u'
            ],
            '*.ngay_sinh' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->transformDate($value)) {
                        $fail('Ngày sinh không hợp lệ. Định dạng phải là dd/mm/yyyy hoặc yyyy-mm-dd');
                    }
                }
            ],
            '*.gioi_tinh' => [
                'required',
                'in:Nam,Nữ,Khác'
            ],
            '*.so_dien_thoai' => [
                'nullable',
                'string',
                'regex:/^(0[3|5|7|8|9])[0-9]{8,9}$/',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $cleanNumber = preg_replace('/[^0-9]/', '', $value);
                        if (!in_array(strlen($cleanNumber), [10, 11])) {
                            $fail('Số điện thoại phải có 10 hoặc 11 số');
                        }
                    }
                }
            ],
            '*.email_phu_huynh' => [
                'required',
                'email',
                Rule::unique('users', 'guardian_email')
            ],
            '*.sdt_phu_huynh' => [
                'nullable',
                'regex:/^(0[3|5|7|8|9])[0-9]{8,9}$/'
            ],
            '*.ten_phu_huynh' => [
                'nullable',
                'string',
                'max:50'
            ],
            '*.dia_chi' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[\p{L}0-9\s\-\/,]+$/u'
            ]
        ];

        if ($this->gradeNumber == 10) {
            $rules['*.diem_dau_vao'] = [
                'required',
                'numeric',
                'min:0',
                'max:50'
            ];
        }

        return $rules;
    }

    public function customValidationMessages()
    {
        return [
            '*.ho_va_ten.required' => 'Họ và tên không được để trống',
            '*.ho_va_ten.max' => 'Họ và tên không được vượt quá 50 ký tự',
            '*.ho_va_ten.regex' => 'Họ và tên chỉ được chứa chữ cái, khoảng trắng và dấu gạch ngang',

            '*.ngay_sinh.required' => 'Ngày sinh không được để trống',

            '*.gioi_tinh.required' => 'Giới tính không được để trống',
            '*.gioi_tinh.in' => 'Giới tính phải là Nam, Nữ hoặc Khác',

            '*.so_dien_thoai.regex' => 'Số điện thoại phải bắt đầu bằng 03, 05, 07, 08 hoặc 09',

            '*.email_phu_huynh.required' => 'Email phụ huynh không được để trống',
            '*.email_phu_huynh.email' => 'Email phụ huynh không hợp lệ',
            '*.email_phu_huynh.unique' => 'Email phụ huynh đã được sử dụng',

            '*.sdt_phu_huynh.regex' => 'Số điện thoại phụ huynh không hợp lệ',

            '*.ten_phu_huynh.max' => 'Tên phụ huynh không được vượt quá 50 ký tự',

            '*.dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự',
            '*.dia_chi.regex' => 'Địa chỉ không được chứa ký tự đặc biệt',

            '*.diem_dau_vao.required' => 'Điểm đầu vào là bắt buộc cho khối 10',
            '*.diem_dau_vao.numeric' => 'Điểm đầu vào phải là số',
            '*.diem_dau_vao.min' => 'Điểm đầu vào không được nhỏ hơn 0',
            '*.diem_dau_vao.max' => 'Điểm đầu vào không được lớn hơn 50'
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
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $row = $failure->row();
            $errors = $failure->errors();
            $values = $failure->values();

            foreach ($errors as $error) {
                $this->errors[] = [
                    'row' => $row,
                    'error' => $error,
                    'values' => $values
                ];
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function transformDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            }

            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('m/d/Y', $value)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            Log::error("Lỗi chuyển đổi ngày sinh: " . $value . " - " . $e->getMessage());
        }

        return null;
    }
}
