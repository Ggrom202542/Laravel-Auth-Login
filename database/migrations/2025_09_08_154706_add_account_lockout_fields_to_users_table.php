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
            // Account Lockout System
            if (!Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->integer('failed_login_attempts')->default(0)->after('password_changed_at');
            }
            if (!Schema::hasColumn('users', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('failed_login_attempts');
            }
            if (!Schema::hasColumn('users', 'unlock_token')) {
                $table->string('unlock_token')->nullable()->after('locked_at');
            }
            if (!Schema::hasColumn('users', 'last_failed_login_at')) {
                $table->timestamp('last_failed_login_at')->nullable()->after('unlock_token');
            }
            
            // IP Tracking for Security
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->ipAddress('last_login_ip')->nullable()->after('last_failed_login_at');
            }
            if (!Schema::hasColumn('users', 'trusted_ips')) {
                $table->json('trusted_ips')->nullable()->after('last_login_ip');
            }
            
            // Device Management
            if (!Schema::hasColumn('users', 'device_tokens')) {
                $table->json('device_tokens')->nullable()->after('trusted_ips');
            }
            if (!Schema::hasColumn('users', 'last_device_fingerprint')) {
                $table->string('last_device_fingerprint')->nullable()->after('device_tokens');
            }
            
            // Suspicious Activity Tracking
            if (!Schema::hasColumn('users', 'suspicious_login_count')) {
                $table->integer('suspicious_login_count')->default(0)->after('last_device_fingerprint');
            }
            if (!Schema::hasColumn('users', 'last_suspicious_login_at')) {
                $table->timestamp('last_suspicious_login_at')->nullable()->after('suspicious_login_count');
            }
            
            // Security Settings
            if (!Schema::hasColumn('users', 'enable_ip_restriction')) {
                $table->boolean('enable_ip_restriction')->default(false)->after('last_suspicious_login_at');
            }
            if (!Schema::hasColumn('users', 'require_device_verification')) {
                $table->boolean('require_device_verification')->default(false)->after('enable_ip_restriction');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'failed_login_attempts',
                'locked_at',
                'unlock_token',
                'last_failed_login_at',
                'last_login_ip',
                'trusted_ips',
                'device_tokens',
                'last_device_fingerprint',
                'suspicious_login_count',
                'last_suspicious_login_at',
                'enable_ip_restriction',
                'require_device_verification'
            ]);
        });
    }
};
