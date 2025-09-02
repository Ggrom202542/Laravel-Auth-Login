<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // สร้างบัญชี Super Admin เริ่มต้น
        $superAdminData = [
            'prefix' => 'นาย',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@laravel-auth.com',
            'phone' => '0800000001',
            'username' => 'superadmin',
            'password' => Hash::make('SuperAdmin123!'),
            'status' => 'active',
            'email_verified_at' => now(),
        ];

        $superAdmin = User::updateOrCreate(
            ['username' => $superAdminData['username']],
            $superAdminData
        );

        // กำหนดบทบาท Super Admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdmin->assignRole('super_admin');
            $this->command->info('✅ กำหนดบทบาท Super Admin ให้ผู้ใช้: ' . $superAdmin->username);
        }

        // สร้างบัญชี Admin ตัวอย่าง
        $adminData = [
            'prefix' => 'นาย',
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin@laravel-auth.com',
            'phone' => '0800000002',
            'username' => 'admin',
            'password' => Hash::make('Admin123!'),
            'status' => 'active',
            'email_verified_at' => now(),
        ];

        $admin = User::updateOrCreate(
            ['username' => $adminData['username']],
            $adminData
        );

        // กำหนดบทบาท Admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->assignRole('admin');
            $this->command->info('✅ กำหนดบทบาท Admin ให้ผู้ใช้: ' . $admin->username);
        }

        // สร้างบัญชี User ตัวอย่าง
        $userData = [
            'prefix' => 'นาย',
            'first_name' => 'User',
            'last_name' => 'Test',
            'email' => 'user@laravel-auth.com',
            'phone' => '0800000003',
            'username' => 'user',
            'password' => Hash::make('User123!'),
            'status' => 'active',
            'email_verified_at' => now(),
        ];

        $user = User::updateOrCreate(
            ['username' => $userData['username']],
            $userData
        );

        // กำหนดบทบาท User
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $user->assignRole('user');
            $this->command->info('✅ กำหนดบทบาท User ให้ผู้ใช้: ' . $user->username);
        }

        $this->command->info('');
        $this->command->info('🎉 สร้างบัญชีทดสอบเรียบร้อยแล้ว:');
        $this->command->info('👑 Super Admin - Username: superadmin, Password: SuperAdmin123!');
        $this->command->info('🛡️ Admin - Username: admin, Password: Admin123!');
        $this->command->info('👤 User - Username: user, Password: User123!');
    }
}
