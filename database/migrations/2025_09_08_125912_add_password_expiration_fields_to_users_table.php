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
            // password_changed_at มีอยู่แล้ว ไม่ต้องเพิ่ม
            $table->timestamp('password_expires_at')->nullable()->after('password_changed_at');
            $table->timestamp('password_warned_at')->nullable()->after('password_expires_at');
            $table->boolean('password_expiration_enabled')->default(true)->after('password_warned_at');
            $table->index(['password_expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['password_expires_at', 'status']);
            $table->dropColumn([
                'password_expires_at', 
                'password_warned_at',
                'password_expiration_enabled'
            ]);
        });
    }
};
