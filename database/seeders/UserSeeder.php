<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert(
            [
                [
                    'prefix' => 'นาย',
                    'name' => 'ผู้ใช้งานทั่วไป (User)',
                    'phone' => '0935962430',
                    'email' => '-',
                    'username' => 'user',
                    'password' => bcrypt('2011'),
                    'user_type' => 'user',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'prefix' => 'นาย',
                    'name' => 'ผู้ดูแลระบบทั่วไป (Admin)',
                    'phone' => '0935962430',
                    'email' => '-',
                    'username' => 'admin',
                    'password' => bcrypt('2011'),
                    'user_type' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'prefix' => 'นาย',
                    'name' => 'ผู้ดูแลระบบสูงสุด (Super Admin)',
                    'phone' => '0935962430',
                    'email' => 'prapreut.1803@gmail.com',
                    'username' => 'superadmin',
                    'password' => bcrypt('2011'),
                    'user_type' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]
        );
    }
}
