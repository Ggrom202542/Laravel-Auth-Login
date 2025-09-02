<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'ผู้ใช้งานทั่วไป - สามารถใช้งานฟีเจอร์พื้นฐานของระบบได้'
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'ผู้ดูแลระบบ - สามารถจัดการผู้ใช้และดูรายงานได้'
            ],
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'ผู้ดูแลระบบระดับสูงสุด - สามารถเข้าถึงและจัดการทุกอย่างในระบบได้'
            ]
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('✅ สร้างบทบาท (Roles) เรียบร้อย: ' . count($roles) . ' รายการ');
    }
}
