<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * สร้างข้อมูลเริ่มต้นทั้งหมดสำหรับระบบ
     * รันด้วยคำสั่ง: php artisan migrate:fresh --seed
     * 
     * ดูข้อมูลเพิ่มเติม:
     * - README-SEEDERS.md - คู่มือการใช้งาน seeders
     * - LOGIN-CREDENTIALS.md - ข้อมูลการเข้าสู่ระบบ
     * - TESTING-GUIDE.md - แนวทางการทดสอบ
     * - QUICK-COMMANDS.md - คำสั่งใช้งานด่วน
     */
    public function run(): void
    {
        // ปิด foreign key checks ชั่วคราว
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $this->command->info('🚀 เริ่มต้นการสร้างข้อมูลเริ่มต้น...');
        
        $this->call([
            // 1. ข้อมูลพื้นฐานของระบบ
            SystemSettingSeeder::class,
            
            // 2. สร้างบทบาทและสิทธิ์
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            
            // 3. สร้างผู้ใช้ระบบ
            SuperAdminSeeder::class,
            UserSeeder::class,
            
            // 4. ข้อมูลเสริม (หลังจากมี User แล้ว)
            SecurityPolicySeeder::class,
            // ActivityLogSeeder::class,
            // SessionsSeeder::class,
            // MessageSeeder::class,
        ]);
        
        // เปิด foreign key checks กลับ
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ สร้างข้อมูลเริ่มต้นเสร็จสิ้น!');
        $this->command->line('');
        $this->command->info('🔑 ข้อมูลเข้าสู่ระบบ:');
        $this->command->line('Super Admin: superadmin / SuperAdmin123!');
        $this->command->line('Admin: admin / Admin123!');
        $this->command->line('User: user / User123!');
    }
}
