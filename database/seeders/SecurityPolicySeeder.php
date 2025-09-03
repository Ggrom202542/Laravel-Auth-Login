<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SecurityPolicy;
use App\Models\User;

class SecurityPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // หา Super Admin
        $superAdmin = User::where('role', 'super_admin')->first();

        // สร้าง Security Policies
        SecurityPolicy::create([
            'policy_name' => 'IP Restriction for Super Admins',
            'description' => 'จำกัดการเข้าถึงจาก IP ที่ระบุสำหรับ Super Admin',
            'policy_type' => 'ip_restriction',
            'applies_to' => 'super_admin',
            'policy_rules' => [
                'allowed_ips' => ['127.0.0.1', '192.168.1.0/24', '10.0.0.0/8'],
                'strict_mode' => true
            ],
            'is_active' => true,
            'created_by' => $superAdmin ? $superAdmin->id : 1
        ]);

        SecurityPolicy::create([
            'policy_name' => 'Admin Working Hours',
            'description' => 'จำกัดเวลาทำงานสำหรับ Admin',
            'policy_type' => 'time_restriction',
            'applies_to' => 'admin',
            'policy_rules' => [
                'allowed_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                'allowed_hours' => '07:00-19:00',
                'timezone' => 'Asia/Bangkok'
            ],
            'is_active' => true,
            'created_by' => $superAdmin ? $superAdmin->id : 1
        ]);

        SecurityPolicy::create([
            'policy_name' => '2FA Requirement for Admins',
            'description' => 'บังคับใช้ 2FA สำหรับ Admin ทุกคน',
            'policy_type' => '2fa_requirement',
            'applies_to' => 'admin',
            'policy_rules' => [
                'enforce_2fa' => true,
                'grace_period_days' => 7,
                'exceptions' => []
            ],
            'is_active' => true,
            'created_by' => $superAdmin ? $superAdmin->id : 1
        ]);

        // Inactive policy for testing
        SecurityPolicy::create([
            'policy_name' => 'Deprecated User Restriction',
            'description' => 'นโยบายที่ไม่ใช้งานแล้ว',
            'policy_type' => 'ip_restriction',
            'applies_to' => 'user',
            'policy_rules' => [
                'allowed_ips' => ['192.168.0.0/16'],
                'strict_mode' => false
            ],
            'is_active' => false,
            'created_by' => $superAdmin ? $superAdmin->id : 1
        ]);

        $this->command->info('SecurityPolicy test data created successfully!');
        $this->command->info('Active policies: ' . SecurityPolicy::active()->count());
    }
}
