<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AdminSession;
use Carbon\Carbon;

class AdminSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // หาผู้ใช้ Super Admin
        $superAdmin = User::where('role', 'super_admin')->first();
        
        if (!$superAdmin) {
            $this->command->warn('ไม่พบ Super Admin user');
            return;
        }

        // สร้าง AdminSession สำหรับทดสอบ
        AdminSession::create([
            'user_id' => $superAdmin->id,
            'session_id' => 'test_super_session_' . uniqid(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'login_at' => now()->subHours(2),
            'last_activity' => now()->subMinutes(5),
            'status' => 'active',
            'login_method' => 'password',
        ]);

        // สร้าง Admin users และ sessions เพิ่มเติมเพื่อทดสอบ
        $adminUsers = User::where('role', 'admin')->take(3)->get();
        
        foreach ($adminUsers as $admin) {
            AdminSession::create([
                'user_id' => $admin->id,
                'session_id' => 'test_admin_session_' . $admin->id . '_' . uniqid(),
                'ip_address' => '192.168.1.' . rand(100, 200),
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'login_at' => now()->subHours(rand(1, 24)),
                'last_activity' => now()->subMinutes(rand(1, 60)),
                'status' => 'active',
                'login_method' => 'password',
            ]);
        }

        // สร้าง session ที่ terminated เพื่อทดสอบ
        if ($adminUsers->count() > 0) {
            AdminSession::create([
                'user_id' => $adminUsers->first()->id,
                'session_id' => 'terminated_session_' . uniqid(),
                'ip_address' => '10.0.0.' . rand(1, 255),
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15',
                'login_at' => now()->subDays(1),
                'last_activity' => now()->subHours(6),
                'logout_at' => now()->subHours(6),
                'status' => 'terminated',
                'login_method' => 'password',
            ]);
        }

        $this->command->info('AdminSession test data created successfully!');
        $this->command->info('Total active sessions: ' . AdminSession::where('status', 'active')->count());
    }
}
