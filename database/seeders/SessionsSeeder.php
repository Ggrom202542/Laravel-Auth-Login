<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, UserSession};
use Carbon\Carbon;

class SessionsSeeder extends Seeder
{
    public function run()
    {
        // สร้าง user ตัวอย่าง
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'username' => 'demo_user',
                'email' => 'demo@example.com',
                'password' => bcrypt('password123'),
                'first_name' => 'Demo',
                'last_name' => 'User',
                'role' => 'user',
                'is_approved' => true,
                'email_verified_at' => now()
            ]);
        }

        // สร้าง session ปกติ
        UserSession::create([
            'session_id' => 'normal_session_' . uniqid(),
            'user_id' => $user->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'device_type' => 'desktop',
            'device_name' => 'Windows PC',
            'platform' => 'Windows',
            'browser' => 'Chrome',
            'location_country' => 'Thailand',
            'location_city' => 'Bangkok',
            'last_activity' => now(),
            'login_at' => now()->subMinutes(30),
            'expires_at' => now()->addHours(2),
            'is_current' => false,
            'is_trusted' => true,
            'is_active' => true,
            'is_suspicious' => false
        ]);

        // สร้าง suspicious session
        UserSession::create([
            'session_id' => 'suspicious_session_' . uniqid(),
            'user_id' => $user->id,
            'ip_address' => '203.144.144.144',
            'user_agent' => 'Mozilla/5.0 (Unknown) AppleWebKit/537.36',
            'device_type' => 'mobile',
            'device_name' => 'Unknown Device',
            'platform' => 'Unknown',
            'browser' => 'Unknown',
            'location_country' => 'Russia',
            'location_city' => 'Moscow',
            'last_activity' => now()->subMinutes(5),
            'login_at' => now()->subMinutes(15),
            'expires_at' => now()->addHours(2),
            'is_current' => false,
            'is_trusted' => false,
            'is_active' => true,
            'is_suspicious' => true,
            'suspicious_reason' => 'Login from unusual location (Russia)',
            'suspicious_detected_at' => now()->subMinutes(15)
        ]);

        echo "Created sample session data with suspicious session\n";
    }
}
