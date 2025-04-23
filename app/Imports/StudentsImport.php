<?php

    namespace App\Imports;

    use App\Models\School;
    use App\Models\User;
    use App\Models\AcademicYear;
    use App\Models\GradeLevel;
    use Carbon\Carbon;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Hash;
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
                try {
                    // Kiểm tra trường bắt buộc
                    if (empty($row['ho_ten'])) {
                        $this->errors[] = "Dòng $lineNumber: Thiếu họ tên";
                        $lineNumber++;
                        continue;
                    }

                    // Tạo email tự động
                    $fullName = $row['ho_ten'];
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
                    $counter = 1;
                    while (User::where('email', $email)->exists()) {
                        $email = $username . $counter . '@' . $schoolDomain;
                        $counter++;
                    }

                    // Tạo học sinh
                    $student = User::create([
                        'full_name' => $row['ho_ten'],
                        'email' => $email,
                        'phone' => $row['so_dien_thoai'] ?? null,
                        'gender' => $this->mapGender($row['gioi_tinh'] ?? null),
                        'date_of_birth' => $this->parseDate($row['ngay_sinh'] ?? null),
                        'address' => $row['dia_chi'] ?? null,
                        'password' => Hash::make('12345678'),
                        'role' => User::ROLE_STUDENT,
                        'school_id' => $this->schoolId,
                        'is_active' => true,
                    ]);

                    // Gán học sinh vào năm học và khối (sử dụng pivot table student_academic_years)
                    $student->studentAcademicYears()->attach($this->academicYearId, [
                        'grade_level_id' => $this->gradeLevelId
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



        protected function mapGender($gender)
        {
            if (empty($gender)) return null;

            $gender = mb_strtolower(trim($gender));

            if (in_array($gender, ['nam', 'male'])) {
                return 'Nam';
            } elseif (in_array($gender, ['nữ', 'nu', 'female'])) {
                return 'Nữ';
            } else {
                return 'Khác';
            }
        }

        protected function parseDate($date)
        {
            if (empty($date)) {
                return null;
            }

            try {
                // Nếu là số Excel (serial date)
                if (is_numeric($date)) {
                    return Date::excelToDateTimeObject($date)->format('Y-m-d');
                }

                // Thử các định dạng phổ biến
                $formatsToTry = [
                    'd/m/Y', 'd/m/y', // DD/MM/YYYY hoặc DD/MM/YY
                    'm/d/Y', 'm/d/y', // MM/DD/YYYY hoặc MM/DD/YY
                    'Y-m-d',           // YYYY-MM-DD
                    'd-m-Y', 'd-m-y',  // DD-MM-YYYY hoặc DD-MM-YY
                    'm-d-Y', 'm-d-y'   // MM-DD-YYYY hoặc MM-DD-YY
                ];

                foreach ($formatsToTry as $format) {
                    try {
                        return Carbon::createFromFormat($format, $date)->format('Y-m-d');
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                // Nếu không parse được, thử parse tự động
                try {
                    return Carbon::parse($date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $this->errors[] = "Định dạng ngày sinh không hợp lệ: " . $date;
                    return null;
                }
            } catch (\Exception $e) {
                $this->errors[] = "Lỗi xử lý ngày sinh: " . $e->getMessage();
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
