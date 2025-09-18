<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📝 สร้างผู้ใช้ทดสอบ...');
        
        $now = Carbon::now();
        
        // ตรวจสอบว่ามี User ทดสอบแล้วหรือไม่
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'prefix' => 'นาย',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin-test',
                'email' => 'admin@example.com',
                'email_verified_at' => $now,
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'status' => 'active',
                'approval_status' => 'approved',
                'approved_at' => $now,
                'phone' => '081-234-5678',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'admin_notes' => 'Admin user สำหรับทดสอบระบบ',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            $this->command->info('✅ สร้าง Admin User: admin@example.com');
        }
        
        if (!User::where('email', 'user@example.com')->exists()) {
            User::create([
                'prefix' => 'นางสาว',
                'first_name' => 'Test',
                'last_name' => 'User',
                'username' => 'user-test',
                'email' => 'user@example.com',
                'email_verified_at' => $now,
                'password' => Hash::make('User123!'),
                'role' => 'user',
                'status' => 'active',
                'approval_status' => 'approved',
                'approved_at' => $now,
                'phone' => '081-987-6543',
                'date_of_birth' => '1995-05-15',
                'gender' => 'female',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            $this->command->info('✅ สร้าง Regular User: user@example.com');
        }
        
        $this->command->info('🎯 สร้างผู้ใช้ทดสอบเสร็จสิ้น');
    }
}
