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
            // เพิ่ม approval status สำหรับระบบอนุมัติ
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                   ->default('pending')
                   ->after('status')
                   ->comment('สถานะการอนุมัติการสมัครสมาชิก');
                   
            // เก็บข้อมูลเพิ่มเติมเมื่อสมัคร
            $table->timestamp('registered_at')
                   ->nullable()
                   ->after('approval_status')
                   ->comment('เวลาที่สมัครสมาชิก');
                   
            $table->timestamp('approved_at')
                   ->nullable()
                   ->after('registered_at')
                   ->comment('เวลาที่ได้รับการอนุมัติ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'registered_at', 'approved_at']);
        });
    }
};
