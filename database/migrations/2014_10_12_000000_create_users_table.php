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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->comment('คำนำหน้า');
            $table->string('name')->comment('ชื่อ - นามสกุล');
            $table->string('phone')->comment('หมายเลขโทรศัพท์');
            $table->string('email')->unique()->comment('อีเมล');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('username')->unique()->comment('ชื่อผู้ใช้');
            $table->string('password')->comment('รหัสผ่าน');
            $table->string('avatar')->nullable()->comment('รูปประจำตัว');
            $table->string('user_type')->default('user')->comment('ประเภทผู้ใช้');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
