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
            // แยก name เป็น first_name และ last_name แทน
            $table->string('first_name')->after('prefix')->comment('ชื่อ');
            $table->string('last_name')->after('first_name')->comment('นามสกุล');
            
            // เปลี่ยนชื่อ avatar เป็น profile_image เพื่อความชัดเจน
            $table->string('profile_image')->nullable()->after('last_name')->comment('รูปโปรไฟล์');
            
            // เพิ่มฟิลด์สำหรับความปลอดภัยและการจัดการ
            $table->enum('status', ['active', 'inactive', 'suspended'])
                   ->default('active')
                   ->after('profile_image')
                   ->comment('สถานะบัญชี');
                   
            $table->timestamp('last_login_at')
                   ->nullable()
                   ->after('status')
                   ->comment('เข้าใช้งานครั้งล่าสุด');
                   
            $table->unsignedTinyInteger('failed_login_attempts')
                   ->default(0)
                   ->after('last_login_at')
                   ->comment('จำนวนครั้งที่พยายามเข้าระบบผิด');
                   
            $table->timestamp('locked_until')
                   ->nullable()
                   ->after('failed_login_attempts')
                   ->comment('ล็อกบัญชีจนถึงเวลา');
            
            // เพิ่ม soft deletes
            $table->softDeletes();
            
            // ลบฟิลด์เก่าที่ไม่ใช้แล้ว
            $table->dropColumn(['name', 'avatar', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // คืนค่าฟิลด์เก่า
            $table->string('name')->after('prefix')->comment('ชื่อ - นามสกุล');
            $table->string('avatar')->nullable()->after('password')->comment('รูปประจำตัว');
            $table->string('user_type')->default('user')->after('avatar')->comment('ประเภทผู้ใช้');
            
            // ลบฟิลด์ใหม่
            $table->dropColumn([
                'first_name', 'last_name', 'profile_image', 'status',
                'last_login_at', 'failed_login_attempts', 'locked_until', 'deleted_at'
            ]);
        });
    }
};
