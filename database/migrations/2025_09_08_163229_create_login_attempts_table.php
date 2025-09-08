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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Attempt Information
            $table->string('username_attempted')->nullable(); // ชื่อผู้ใช้ที่พยายาม login
            $table->enum('status', ['success', 'failed', 'blocked', 'suspicious'])->default('failed');
            $table->string('failure_reason')->nullable(); // เหตุผลที่ล้มเหลว
            
            // Location & Network Information
            $table->string('ip_address', 45);
            $table->string('country_code', 2)->nullable();
            $table->string('country_name')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('isp')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Device & Browser Information
            $table->text('user_agent')->nullable();
            $table->string('browser_name')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_fingerprint')->nullable();
            
            // Timing Analysis
            $table->timestamp('attempted_at');
            $table->time('time_of_day'); // เวลาในวัน
            $table->tinyInteger('day_of_week'); // วันในสัปดาห์ (1-7)
            $table->integer('time_since_last_attempt')->nullable(); // วินาทีจาก attempt ล่าสุด
            
            // Behavioral Analysis
            $table->integer('typing_speed')->nullable(); // ความเร็วในการพิมพ์ (chars/min)
            $table->json('mouse_patterns')->nullable(); // รูปแบบการเคลื่อนไหวเมาส์
            $table->integer('form_completion_time')->nullable(); // เวลาในการกรอกฟอร์ม (วินาที)
            
            // Risk Assessment
            $table->decimal('risk_score', 5, 2)->default(0); // คะแนนความเสี่ยง 0-100
            $table->json('risk_factors')->nullable(); // ปัจจัยเสี่ยงที่ตรวจพบ
            $table->boolean('is_suspicious')->default(false);
            $table->string('alert_level')->default('low'); // low, medium, high, critical
            
            // Response Actions
            $table->json('security_actions')->nullable(); // การดำเนินการรักษาความปลอดภัย
            $table->boolean('admin_notified')->default(false);
            $table->timestamp('investigated_at')->nullable();
            $table->foreignId('investigated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Session Information
            $table->string('session_id')->nullable();
            $table->json('request_headers')->nullable();
            $table->text('referer')->nullable();
            
            $table->timestamps();
            
            // Indexes for analysis queries
            $table->index(['user_id', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
            $table->index(['status', 'attempted_at']);
            $table->index(['is_suspicious', 'attempted_at']);
            $table->index(['alert_level', 'attempted_at']);
            $table->index(['country_code', 'attempted_at']);
            $table->index(['risk_score']);
            $table->index(['day_of_week', 'time_of_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
