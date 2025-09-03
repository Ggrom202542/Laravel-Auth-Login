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
        Schema::create('registration_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('ผู้ใช้ที่สมัคร');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('สถานะการอนุมัติ');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null')->comment('ผู้ที่ทำการอนุมัติ/ปฏิเสธ');
            $table->timestamp('reviewed_at')->nullable()->comment('เวลาที่ทำการอนุมัติ/ปฏิเสธ');
            $table->text('rejection_reason')->nullable()->comment('เหตุผลการปฏิเสธ');
            $table->json('additional_data')->nullable()->comment('ข้อมูลเพิ่มเติมจากการสมัคร');
            $table->string('approval_token')->unique()->comment('Token สำหรับยืนยันทาง email');
            $table->timestamp('token_expires_at')->comment('เวลาหมดอายุของ token');
            $table->ipAddress('registration_ip')->comment('IP ที่ใช้สมัคร');
            $table->string('user_agent')->comment('User Agent ที่ใช้สมัคร');
            $table->timestamps();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index('approval_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_approvals');
    }
};
