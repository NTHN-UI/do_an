<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Thông báo học phí',
                'type' => 'tuition',
                'subject_template' => 'Thông báo đóng học phí tháng {THANG} - {LOP}',
                'body_template' => "Kính gửi Quý phụ huynh em {TEN_HOC_SINH}, {LOP},

Nhà trường trân trọng thông báo đến Quý phụ huynh về việc đóng học phí cho tháng {THANG}.

Chi tiết:
- Học phí tháng: 2.500.000 VNĐ
- Hạn đóng: Trước ngày 15/{THANG}
- Phương thức đóng: Chuyển khoản hoặc nộp trực tiếp tại phòng kế toán

Kính mong Quý phụ huynh đóng học phí đúng hạn để con em được học tập tốt nhất.

Trân trọng,
{GIAO_VIEN} - Giáo viên chủ nhiệm {LOP}",
                'variables' => json_encode(['{TEN_HOC_SINH}', '{LOP}', '{GIAO_VIEN}']),
                'school_id' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Thông báo họp phụ huynh',
                'type' => 'meeting',
                'subject_template' => 'Thông báo họp phụ huynh {LOP}',
                'body_template' => "Kính gửi Quý phụ huynh em {TEN_HOC_SINH},

{LOP} tổ chức cuộc họp phụ huynh với nội dung:
- Thời gian: 19h00 ngày {NGAY_THANG}
- Địa điểm: Phòng học {LOP}
- Nội dung: Tổng kết học tập và hoạt động của học sinh

Kính mong Quý phụ huynh sắp xếp thời gian tham dự.

Trân trọng,
{GIAO_VIEN}",
                'variables' => json_encode(['{TEN_HOC_SINH}', '{LOP}', '{GIAO_VIEN}']),
                'school_id' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Thông báo thi cử',
                'type' => 'exam',
                'subject_template' => 'Thông báo lịch thi {MON_THI} - {LOP}',
                'body_template' => "Kính gửi Quý phụ huynh em {TEN_HOC_SINH},

Nhà trường thông báo lịch thi {MON_THI} như sau:
- Môn thi: {MON_THI}
- Thời gian: {GIO_THI} ngày {NGAY_THI}
- Hình thức: Thi viết/trắc nghiệm

Lưu ý:
- Học sinh cần có mặt trước 30 phút
- Mang theo thẻ học sinh và dụng cụ học tập
- Không được mang điện thoại vào phòng thi

Kính mong Quý phụ huynh nhắc nhở con em chuẩn bị tốt cho kỳ thi.

Trân trọng,
{GIAO_VIEN}",
                'variables' => json_encode(['{TEN_HOC_SINH}', '{LOP}', '{GIAO_VIEN}']),
                'school_id' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Thông báo nghỉ học',
                'type' => 'absence',
                'subject_template' => 'Thông báo nghỉ học ngày {NGAY_THANG}',
                'body_template' => "Kính gửi Quý phụ huynh em {TEN_HOC_SINH},

Nhà trường thông báo lịch nghỉ học vào ngày {NGAY_THANG} do {LY_DO}.

Học sinh đi học lại vào ngày {NGAY_DI_HOC_LAI}.

Trân trọng,
{GIAO_VIEN}",
                'variables' => json_encode(['{TEN_HOC_SINH}', '{GIAO_VIEN}']),
                'school_id' => 1,
                'is_active' => true
            ]
        ];

        foreach ($templates as $template) {
            NotificationTemplate::create($template);
        }
    }
}
