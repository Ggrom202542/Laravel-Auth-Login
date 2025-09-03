<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // สร้างผู้ใช้ Admin สำหรับทดสอบ
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'prefix' => 'นาย',
                'first_name' => 'ผู้ดูแลระบบ',
                'last_name' => 'ทดสอบ',
                'phone' => '0812345678',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'approval_status' => 'approved',
                'account_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // สร้างผู้ใช้ Super Admin สำหรับทดสอบ
        User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'prefix' => 'นาย',
                'first_name' => 'ผู้ดูแลระบบสูงสุด',
                'last_name' => 'ทดสอบ',
                'phone' => '0887654321',
                'email' => 'superadmin@test.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'approval_status' => 'approved',
                'account_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // สร้างผู้ใช้ทั่วไปสำหรับทดสอบ (3 คน)
        for ($i = 1; $i <= 3; $i++) {
            User::firstOrCreate(
                ['username' => "user{$i}"],
                [
                    'prefix' => $i % 2 == 0 ? 'นาง' : 'นาย',
                    'first_name' => "ผู้ใช้ทดสอบ{$i}",
                    'last_name' => 'ระบบ',
                    'phone' => '08' . str_pad($i, 8, '0', STR_PAD_LEFT),
                    'email' => "user{$i}@test.com",
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'status' => $i == 3 ? 'inactive' : 'active',
                    'approval_status' => 'approved',
                    'account_verified_at' => now(),
                    'login_count' => rand(1, 50),
                    'last_login_at' => now()->subDays(rand(1, 30)),
                    'last_ip_address' => "192.168.1." . (100 + $i),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'admin_notes' => $i == 2 ? 'ผู้ใช้ที่มีประสบการณ์ดี' : null,
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now(),
                ]
            );
        }

        // สร้างผู้ใช้รอการอนุมัติ
        User::firstOrCreate(
            ['username' => 'pending_user'],
            [
                'prefix' => 'นางสาว',
                'first_name' => 'ผู้ใช้รอ',
                'last_name' => 'อนุมัติ',
                'phone' => '0899999999',
                'email' => 'pending@test.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'inactive',
                'approval_status' => 'pending',
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
            ]
        );

        $this->command->info('Admin and test users created successfully!');
    }
}
