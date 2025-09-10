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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // ข้อมูลพื้นฐาน
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('activity_type', 100); // ประเภทกิจกรรม
            $table->text('description'); // คำอธิบายกิจกรรม
            
            // ข้อมูลเครือข่าย
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            
            // ข้อมูลเพิ่มเติม
            $table->json('payload')->nullable(); // ข้อมูลที่ส่งมา
            $table->integer('response_status')->nullable(); // HTTP status code
            $table->decimal('response_time', 8, 3)->nullable(); // เวลาตอบสนอง (วินาที)
            
            // ข้อมูลตำแหน่ง
            $table->string('location', 255)->nullable(); // ตำแหน่งทางภูมิศาสตร์
            $table->string('device_type', 50)->nullable(); // desktop, mobile, tablet
            $table->string('browser', 100)->nullable(); // Chrome, Firefox, Safari
            $table->string('platform', 100)->nullable(); // Windows, macOS, Linux, iOS, Android
            
            // ข้อมูลความปลอดภัย
            $table->boolean('is_suspicious')->default(false);
            $table->string('session_id', 255)->nullable();
            
            // Polymorphic relationships
            $table->string('causer_type')->nullable(); // ประเภทของผู้ทำกิจกรรม
            $table->unsignedBigInteger('causer_id')->nullable(); // ID ของผู้ทำกิจกรรม
            $table->string('subject_type')->nullable(); // ประเภทของหัวข้อ
            $table->unsignedBigInteger('subject_id')->nullable(); // ID ของหัวข้อ
            
            // ข้อมูลเพิ่มเติม
            $table->json('properties')->nullable(); // ข้อมูลเพิ่มเติมแบบ flexible
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes สำหรับการค้นหาที่มีประสิทธิภาพ
            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['is_suspicious', 'created_at']);
            $table->index(['session_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['created_at']);
            
            // Composite indexes
            $table->index(['user_id', 'activity_type', 'created_at'], 'user_activity_type_date_idx');
            $table->index(['activity_type', 'is_suspicious', 'created_at'], 'activity_suspicious_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
