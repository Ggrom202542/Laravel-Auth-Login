<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('registers')->insert([
            [
                'prefix' => 'นาย',
                'name' => 'สมชาย ใจดี',
                'phone' => '0812345678',
                'email' => 'somchai@example.com',
                'username' => 'somchai',
                'password' => Hash::make('password123'),
                'avatar' => null,
                'user_type' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'นางสาว',
                'name' => 'สุดารัตน์ สวยงาม',
                'phone' => '0898765432',
                'email' => 'sudarat@example.com',
                'username' => 'sudarat',
                'password' => Hash::make('password456'),
                'avatar' => null,
                'user_type' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'นาง',
                'name' => 'กานดา ใจดี',
                'phone' => '0865432198',
                'email' => 'kanda@example.com',
                'username' => 'kanda',
                'password' => Hash::make('password789'),
                'avatar' => null,
                'user_type' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
