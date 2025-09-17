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
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // ประเภทของการกระทำ (device_revoked, forced_logout, etc.)
            $table->unsignedBigInteger('user_id')->nullable(); // ผู้ใช้ที่ถูกดำเนินการ
            $table->unsignedBigInteger('admin_id'); // ผู้ดูแลระบบที่ดำเนินการ
            $table->string('ip_address')->nullable(); // IP ที่ดำเนินการ
            $table->json('details'); // รายละเอียดการกระทำ
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_logs');
    }
};
