<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // เพิ่มฟิลด์สำหรับ User Management
            $table->unsignedInteger('login_count')
                   ->default(0)
                   ->after('last_login_at')
                   ->comment('จำนวนครั้งที่เข้าสู่ระบบ');
            
            $table->text('admin_notes')
                   ->nullable()
                   ->after('login_count')
                   ->comment('หมายเหตุจากแอดมิน');
            
            $table->timestamp('account_verified_at')
                   ->nullable()
                   ->after('admin_notes')
                   ->comment('วันที่ยืนยันบัญชี');
            
            $table->string('last_ip_address', 45)
                   ->nullable()
                   ->after('account_verified_at')
                   ->comment('IP Address ล่าสุด');
            
            $table->string('user_agent', 500)
                   ->nullable()
                   ->after('last_ip_address')
                   ->comment('User Agent ล่าสุด');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'login_count',
                'admin_notes', 
                'account_verified_at',
                'last_ip_address',
                'user_agent'
            ]);
        });
    }
};
