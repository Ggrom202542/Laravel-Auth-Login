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
            // เพิ่มฟิลด์สำหรับ Two-Factor Authentication
            $table->text('google2fa_secret')->nullable()->after('two_factor_recovery_codes');
            $table->boolean('google2fa_enabled')->default(false)->after('google2fa_secret');
            $table->timestamp('google2fa_confirmed_at')->nullable()->after('google2fa_enabled');
            $table->json('recovery_codes')->nullable()->after('google2fa_confirmed_at');
            $table->timestamp('recovery_codes_generated_at')->nullable()->after('recovery_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google2fa_secret',
                'google2fa_enabled', 
                'google2fa_confirmed_at',
                'recovery_codes',
                'recovery_codes_generated_at'
            ]);
        });
    }
};
