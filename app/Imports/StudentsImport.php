<?php

namespace App\Imports;

use App\Models\School;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentsImport implements ToCollection, WithHeadingRow
{
    protected $schoolId;
    protected $academicYearId;
    protected $gradeLevelId;
    protected $importedCount = 0;
    protected $errors = [];

    public function __construct($schoolId, $academicYearId, $gradeLevelId)
    {
        $this->schoolId = $schoolId;
        $this->academicYearId = $academicYearId;
        $this->gradeLevelId = $gradeLevelId;
    }

    public function collection(Collection $rows)
    {
        $lineNumber = 2;
        $school = School::find($this->schoolId);

        if (!$school) {
            $this->errors[] = "Không tìm thấy thông tin trường học";
            return;
        }

        // Kiểm tra năm học và khối học có tồn tại không
        $academicYear = AcademicYear::find($this->academicYearId);
        $gradeLevel = GradeLevel::find($this->gradeLevelId);

        if (!$academicYear || !$gradeLevel) {
            $this->errors[] = "Năm học hoặc khối học không tồn tại";
            return;
        }

        $schoolName = $school->name;
        $slug = Str::slug(mb_strtolower($schoolName));
        $slugParts = explode('-', $slug);
        $slugParts = array_slice($slugParts, 1, (count($slugParts) - 1));
        $schoolDomain = join('', $slugParts) . '.edu.vn';

        foreach ($rows as $row) {
            Log::info("zz:", [$row]);
            try {
                // Kiểm tra trường bắt buộc
                if (empty($row['ho_ten'])) {
                    $this->errors[] = "Dòng $lineNumber: Thiếu họ tên";
                    $lineNumber++;
                    continue;
                }
                // Create email
                $email = $this->generateEmail($row['ho_ten'], $schoolDomain);

                // Process gender with better validation
                $gender = $this->mapGender($row['gioi_tinh_namnukhac'] ?? null);

                // Process date with better validation
                $dateOfBirth = $this->parseDate($row['ngay_sinh_ddmmyyyy'] ?? null);

                // Tạo học sinh
                $student = User::create([
                    'full_name' => $row['ho_ten'],
                    'email' => $email,
                    'phone' => $row['so_dien_thoai'] ?? null,
                    'gender' => $gender,
                    'date_of_birth' => $dateOfBirth,
                    'address' => $row['dia_chi'] ?? null,
                    'password' => Hash::make('12345678'),
                    'role' => User::ROLE_STUDENT,
                    'school_id' => $this->schoolId,
                    'is_active' => true,
                ]);

                $student->studentGrades()->attach($this->academicYearId, [
                    'grade_id' => $this->gradeLevelId,
                    'school_id' => Auth::user()->school_id
                ]);

                $this->importedCount++;
                $lineNumber++;

            } catch (\Exception $e) {
                $this->errors[] = "Dòng $lineNumber: " . $e->getMessage();
                $lineNumber++;
                continue;
            }
        }
    }

    /**
     * Normalize row keys to handle different column name formats
     */
    protected function normalizeRowKeys($row)
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = mb_strtolower(str_replace([' ', '_', '-'], '', $key));
            $normalized[$normalizedKey] = $value;
        }

        // Map normalized keys to expected keys
        $mapping = [
            'hoten' => 'ho_ten',
            'sodienthoai' => 'so_dien_thoai',
            'dienthoai' => 'so_dien_thoai',
            'gioitinh' => 'gioi_tinh',
            'ngaysinh' => 'ngay_sinh',
            'diachi' => 'dia_chi',
        ];

        $result = [];
        foreach ($normalized as $key => $value) {
            if (isset($mapping[$key])) {
                $result[$mapping[$key]] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Generate email from name
     */
    protected function generateEmail($fullName, $domain)
    {
        $nameParts = explode(' ', $fullName);
        $lastName = array_pop($nameParts);
        $lastName = mb_strtolower(Str::ascii($lastName));
        $firstLetters = '';

        foreach ($nameParts as $part) {
            $firstLetters .= mb_substr($part, 0, 1);
        }

        $firstLetters = mb_strtolower(Str::ascii($firstLetters));
        $username = $lastName . '.' . $firstLetters;
        $email = $username . '@' . $domain;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $username . $counter . '@' . $domain;
            $counter++;
        }

        return $email;
    }

    /**
     * Improved gender mapping
     */
    protected function mapGender($gender)
    {
        if (empty($gender)) {
            return null;
        }

        $gender = mb_strtolower(trim($gender));

        // Remove any special characters
        $gender = preg_replace('/[^a-z0-9\p{L}]/u', '', $gender);

        if (in_array($gender, ['nam', 'male', 'trai'])) {
            return 'Nam';
        } elseif (in_array($gender, ['nữ', 'nu', 'nu', 'female', 'gái'])) {
            return 'Nữ';
        } else {
            return 'Khác';
        }
    }

    /**
     * Improved date parsing
     */
    protected function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // If it's an Excel date (serial number)
        if (is_numeric($date)) {
            try {
                return Date::excelToDateTimeObject($date)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->errors[] = "Định dạng ngày Excel không hợp lệ: " . $date;
                return null;
            }
        }

        // Try common date formats
        $formats = [
            'd/m/Y', 'd/m/y', // Vietnamese common format
            'm/d/Y', 'm/d/y', // US format
            'Y-m-d',           // ISO format
            'd-m-Y', 'd-m-y',  // European format
            'd.m.Y', 'd.m.y',  // German format
        ];

        foreach ($formats as $format) {
            try {
                $dateObj = Carbon::createFromFormat($format, $date);
                if ($dateObj) {
                    return $dateObj->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try to parse as natural date
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->errors[] = "Không thể xác định định dạng ngày: " . $date;
            return null;
        }
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
