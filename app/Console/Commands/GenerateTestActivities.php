<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;

class GenerateTestActivities extends Command
{
    protected $signature = 'generate:test-activities {count=50}';
    protected $description = 'Generate test activity logs';

    public function handle()
    {
        $count = $this->argument('count');
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->error('No users found. Please create some users first.');
            return;
        }

        $activityTypes = [
            'login', 'logout', 'view_page', 'update_profile', 'change_password',
            'upload_file', 'delete_file', 'create_post', 'edit_post', 'delete_post',
            'failed_login', 'password_reset', 'email_verification'
        ];

        $descriptions = [
            'login' => 'ผู้ใช้เข้าสู่ระบบ',
            'logout' => 'ผู้ใช้ออกจากระบบ',
            'view_page' => 'เข้าชมหน้า',
            'update_profile' => 'อัพเดทโปรไฟล์',
            'change_password' => 'เปลี่ยนรหัสผ่าน',
            'upload_file' => 'อัพโหลดไฟล์',
            'delete_file' => 'ลบไฟล์',
            'create_post' => 'สร้างโพสต์',
            'edit_post' => 'แก้ไขโพสต์',
            'delete_post' => 'ลบโพสต์',
            'failed_login' => 'พยายามเข้าสู่ระบบแต่ไม่สำเร็จ',
            'password_reset' => 'รีเซ็ตรหัสผ่าน',
            'email_verification' => 'ยืนยันอีเมล'
        ];

        $ipAddresses = [
            '192.168.1.100', '203.113.14.23', '110.164.77.88',
            '125.24.178.92', '27.254.103.45', '180.183.247.15',
            '202.28.34.67', '61.91.185.201'
        ];

        $locations = [
            'Bangkok, Thailand', 'Chiang Mai, Thailand', 'Phuket, Thailand',
            'Singapore', 'Kuala Lumpur, Malaysia', 'Ho Chi Minh, Vietnam'
        ];

        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];
        $platforms = ['Windows', 'macOS', 'Linux', 'iOS', 'Android'];

        $this->info("Generating {$count} test activities...");

        for ($i = 0; $i < $count; $i++) {
            $user = $users->random();
            $activityType = $activityTypes[array_rand($activityTypes)];
            $createdAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => $activityType,
                'description' => $descriptions[$activityType] ?? 'กิจกรรมทั่วไป',
                'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                'user_agent' => $browsers[array_rand($browsers)] . ' on ' . $platforms[array_rand($platforms)],
                'url' => '/dashboard',
                'method' => 'GET',
                'response_status' => rand(0, 10) > 8 ? 500 : 200,
                'response_time' => rand(50, 2000) / 1000,
                'location' => $locations[array_rand($locations)],
                'device_type' => rand(0, 1) ? 'desktop' : 'mobile',
                'browser' => $browsers[array_rand($browsers)],
                'platform' => $platforms[array_rand($platforms)],
                'is_suspicious' => rand(0, 20) == 0, // 5% chance of suspicious
                'session_id' => 'test_session_' . uniqid(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);
        }

        $this->info("✅ Generated {$count} test activities successfully!");
    }
}
