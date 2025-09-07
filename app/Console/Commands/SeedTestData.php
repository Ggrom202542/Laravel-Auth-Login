<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\SecurityPolicy;
use App\Models\UserActivity;
use App\Models\AdminSession;
use Illuminate\Support\Facades\Hash;

class SeedTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed additional test data for system testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌱 เพิ่มข้อมูลทดสอบเข้าระบบ...');
        $this->newLine();

        // สร้าง Security Policies
        $this->info('1. สร้าง Security Policies:');
        // หา superadmin ID ปัจจุบัน
        $superadminId = User::where('username', 'superadmin')->value('id');
        
        if (!$superadminId) {
            $this->error('❌ ไม่พบ Super Admin ในระบบ');
            return;
        }

        $policies = [
            [
                'policy_name' => 'Default Security Policy',
                'description' => 'นโยบายความปลอดภัยพื้นฐานสำหรับระบบ',
                'policy_type' => 'security',
                'policy_rules' => json_encode([
                    'max_login_attempts' => 5,
                    'lockout_duration' => 15,
                    'password_min_length' => 8,
                    'require_2fa' => false,
                    'session_timeout' => 120
                ]),
                'applies_to' => 'user',
                'is_active' => true,
                'effective_from' => now(),
                'created_by' => $superadminId
            ],
            [
                'policy_name' => 'Admin Security Policy',
                'description' => 'นโยบายความปลอดภัยสำหรับ Administrator',
                'policy_type' => 'security',
                'policy_rules' => json_encode([
                    'max_login_attempts' => 3,
                    'lockout_duration' => 30,
                    'password_min_length' => 12,
                    'require_2fa' => true,
                    'session_timeout' => 60
                ]),
                'applies_to' => 'admin',
                'is_active' => true,
                'effective_from' => now(),
                'created_by' => $superadminId
            ]
        ];

        foreach ($policies as $policy) {
            if (!SecurityPolicy::where('policy_name', $policy['policy_name'])->exists()) {
                SecurityPolicy::create($policy);
                $this->info("   ✅ สร้าง: {$policy['policy_name']}");
            } else {
                $this->info("   ⚠️  มีอยู่แล้ว: {$policy['policy_name']}");
            }
        }

        // สร้าง User Activities
        $this->info('2. สร้าง User Activities:');
        $users = User::all();
        $activities = [
            'user_login',
            'user_logout', 
            'profile_updated',
            'password_changed',
            '2fa_enabled',
            '2fa_disabled',
            'role_assigned',
            'account_locked',
            'account_unlocked'
        ];

        $activityCount = 0;
        foreach ($users as $user) {
            for ($i = 0; $i < rand(3, 8); $i++) {
                UserActivity::create([
                    'user_id' => $user->id,
                    'action' => $activities[array_rand($activities)],
                    'description' => 'Test activity for user ' . $user->username,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 Test Browser',
                    'properties' => json_encode(['test' => true]),
                    'created_at' => now()->subHours(rand(1, 72))
                ]);
                $activityCount++;
            }
        }
        $this->info("   ✅ สร้าง User Activities: {$activityCount} รายการ");

        // สร้าง Admin Sessions
        $this->info('3. สร้าง Admin Sessions:');
        $adminUsers = User::whereIn('role', ['admin', 'super_admin'])->get();
        $sessionCount = 0;
        
        foreach ($adminUsers as $admin) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $loginTime = now()->subHours(rand(1, 24));
                AdminSession::create([
                    'user_id' => $admin->id,
                    'session_id' => 'test_session_' . uniqid(),
                    'ip_address' => '127.0.0.' . rand(1, 255),
                    'user_agent' => 'Mozilla/5.0 Admin Browser',
                    'login_at' => $loginTime,
                    'last_activity' => now()->subMinutes(rand(5, 60)),
                    'is_active' => rand(0, 1) ? true : false
                ]);
                $sessionCount++;
            }
        }
        $this->info("   ✅ สร้าง Admin Sessions: {$sessionCount} รายการ");

        // เพิ่มผู้ใช้ทดสอบเพิ่มเติม
        $this->info('4. สร้างผู้ใช้ทดสอบเพิ่มเติม:');
        $testUsers = [
            [
                'username' => 'testuser1',
                'email' => 'testuser1@test.com',
                'first_name' => 'Test',
                'last_name' => 'User One',
                'prefix' => 'นาย',
                'phone' => '081-111-1111',
                'password' => Hash::make('Test123!'),
                'role' => 'user',
                'status' => 'active'
            ],
            [
                'username' => 'testuser2',
                'email' => 'testuser2@test.com',
                'first_name' => 'Test',
                'last_name' => 'User Two',
                'prefix' => 'นางสาว',
                'phone' => '081-222-2222',
                'password' => Hash::make('Test123!'),
                'role' => 'user',
                'status' => 'suspended'
            ],
            [
                'username' => 'testadmin',
                'email' => 'testadmin@test.com',
                'first_name' => 'Test',
                'last_name' => 'Admin',
                'prefix' => 'นาง',
                'phone' => '081-333-3333',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'status' => 'active'
            ]
        ];

        foreach ($testUsers as $userData) {
            $user = User::create($userData);
            $this->info("   ✅ สร้างผู้ใช้: {$userData['username']} ({$userData['role']})");
        }

        $this->newLine();
        $this->info('🎉 เพิ่มข้อมูลทดสอบเสร็จสิ้น!');
        
        // สรุปข้อมูลปัจจุบัน
        $this->newLine();
        $this->info('📊 สรุปข้อมูลในระบบ:');
        $this->info('👥 Users: ' . User::count());
        $this->info('🛡️  Security Policies: ' . SecurityPolicy::count());
        $this->info('📝 User Activities: ' . UserActivity::count());
        $this->info('🔐 Admin Sessions: ' . AdminSession::count());
        
        $this->newLine();
        $this->info('🔑 ข้อมูลสำหรับ Login:');
        $this->info('👑 Super Admin: superadmin / SuperAdmin123!');
        $this->info('🛡️  Admin: admin / Admin123!');
        $this->info('🛡️  Test Admin: testadmin / Admin123!');
        $this->info('👤 User: user / User123!');
        $this->info('👤 Test Users: testuser1, testuser2 / Test123!');
    }
}
