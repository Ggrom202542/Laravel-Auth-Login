<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. สร้างบทบาทก่อน
            RoleSeeder::class,
            
            // 2. สร้างสิทธิ์
            PermissionSeeder::class,
            
            // 3. เชื่อมบทบาทกับสิทธิ์
            RolePermissionSeeder::class,
            
            // 4. สร้างผู้ใช้และกำหนดบทบาท
            SuperAdminSeeder::class,
            
            // 5. สร้างการตั้งค่าระบบ
            SystemSettingSeeder::class,
        ]);
    }
}
