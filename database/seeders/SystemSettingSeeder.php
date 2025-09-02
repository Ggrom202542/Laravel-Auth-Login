<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Application Settings
            [
                'key' => 'app.name',
                'value' => 'Laravel Auth System',
                'type' => 'string',
                'description' => 'ชื่อแอปพลิเคชัน',
                'is_editable' => true
            ],
            [
                'key' => 'app.version',
                'value' => '1.0.0',
                'type' => 'string',
                'description' => 'เวอร์ชันของแอปพลิเคชัน',
                'is_editable' => false
            ],

            // Authentication Settings
            [
                'key' => 'auth.registration_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'เปิดใช้งานการลงทะเบียนใหม่',
                'is_editable' => true
            ],
            [
                'key' => 'auth.email_verification_required',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'ต้องยืนยันอีเมลก่อนใช้งาน',
                'is_editable' => true
            ],
            [
                'key' => 'auth.max_login_attempts',
                'value' => '5',
                'type' => 'integer',
                'description' => 'จำนวนครั้งสูงสุดที่สามารถพยายามเข้าสู่ระบบได้',
                'is_editable' => true
            ],
            [
                'key' => 'auth.lockout_duration',
                'value' => '15',
                'type' => 'integer',
                'description' => 'ระยะเวลาการล็อกบัญชี (นาที)',
                'is_editable' => true
            ],
            [
                'key' => 'auth.session_timeout',
                'value' => '120',
                'type' => 'integer',
                'description' => 'ระยะเวลาหมดอายุ session (นาที)',
                'is_editable' => true
            ],

            // Password Settings
            [
                'key' => 'password.min_length',
                'value' => '8',
                'type' => 'integer',
                'description' => 'ความยาวรหัสผ่านขั้นต่ำ',
                'is_editable' => true
            ],
            [
                'key' => 'password.require_uppercase',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'ต้องมีตัวพิมพ์ใหญ่',
                'is_editable' => true
            ],
            [
                'key' => 'password.require_lowercase',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'ต้องมีตัวพิมพ์เล็ก',
                'is_editable' => true
            ],
            [
                'key' => 'password.require_numbers',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'ต้องมีตัวเลข',
                'is_editable' => true
            ],
            [
                'key' => 'password.require_special_chars',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'ต้องมีอักขระพิเศษ',
                'is_editable' => true
            ],

            // File Upload Settings
            [
                'key' => 'upload.max_file_size',
                'value' => '5120',
                'type' => 'integer',
                'description' => 'ขนาดไฟล์สูงสุดที่อัปโหลดได้ (KB)',
                'is_editable' => true
            ],
            [
                'key' => 'upload.allowed_image_types',
                'value' => 'jpg,jpeg,png,gif,webp',
                'type' => 'string',
                'description' => 'ประเภทไฟล์รูปภาพที่อนุญาต',
                'is_editable' => true
            ],

            // Maintenance Settings
            [
                'key' => 'maintenance.enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'เปิดโหมดบำรุงรักษา',
                'is_editable' => true
            ],
            [
                'key' => 'maintenance.message',
                'value' => 'ระบบกำลังอยู่ในช่วงบำรุงรักษา กรุณาลองใหม่ภายหลัง',
                'type' => 'string',
                'description' => 'ข้อความแสดงเมื่ออยู่ในโหมดบำรุงรักษา',
                'is_editable' => true
            ],

            // Email Settings
            [
                'key' => 'mail.from_name',
                'value' => 'Laravel Auth System',
                'type' => 'string',
                'description' => 'ชื่อผู้ส่งอีเมล',
                'is_editable' => true
            ],
            [
                'key' => 'mail.from_address',
                'value' => 'noreply@laravel-auth.com',
                'type' => 'string',
                'description' => 'อีเมลผู้ส่ง',
                'is_editable' => true
            ],

            // Backup Settings
            [
                'key' => 'backup.auto_backup',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'สำรองข้อมูลอัตโนมัติ',
                'is_editable' => true
            ],
            [
                'key' => 'backup.frequency',
                'value' => 'daily',
                'type' => 'string',
                'description' => 'ความถี่ในการสำรองข้อมูล (daily, weekly, monthly)',
                'is_editable' => true
            ],
            [
                'key' => 'backup.retention_days',
                'value' => '30',
                'type' => 'integer',
                'description' => 'จำนวนวันที่เก็บไฟล์สำรอง',
                'is_editable' => true
            ]
        ];

        foreach ($settings as $settingData) {
            SystemSetting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }

        $this->command->info('✅ สร้างการตั้งค่าระบบเรียบร้อย: ' . count($settings) . ' รายการ');
    }
}
