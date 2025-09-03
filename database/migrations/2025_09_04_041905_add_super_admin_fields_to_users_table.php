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
            // Two-Factor Authentication
            $table->boolean('two_factor_enabled')->default(false)->after('password')->comment('2FA enabled status');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled')->comment('2FA secret key');
            $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_secret')->comment('2FA recovery codes');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes')->comment('2FA confirmation time');

            // Admin-specific fields  
            $table->timestamp('last_admin_login')->nullable()->after('two_factor_confirmed_at')->comment('Last admin login time');
            $table->ipAddress('last_admin_ip')->nullable()->after('last_admin_login')->comment('Last admin login IP');
            $table->integer('failed_admin_attempts')->default(0)->after('last_admin_ip')->comment('Failed admin login attempts');
            $table->timestamp('admin_locked_until')->nullable()->after('failed_admin_attempts')->comment('Admin account locked until');

            // Security and access control
            $table->json('allowed_ip_addresses')->nullable()->after('admin_locked_until')->comment('Allowed IP addresses (null = all allowed)');
            $table->boolean('require_password_change')->default(false)->after('allowed_ip_addresses')->comment('Force password change on next login');
            $table->integer('admin_session_timeout')->nullable()->after('require_password_change')->comment('Session timeout in minutes (null = system default)');

            // Admin management fields
            $table->foreignId('created_by_admin')->nullable()->constrained('users')->after('admin_session_timeout')->comment('Which admin created this user');
            $table->timestamp('admin_role_assigned_at')->nullable()->after('created_by_admin')->comment('When admin role was assigned');
            $table->foreignId('admin_role_assigned_by')->nullable()->constrained('users')->after('admin_role_assigned_at')->comment('Who assigned admin role');
            // Note: admin_notes column already exists from previous migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_secret', 
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'last_admin_login',
                'last_admin_ip',
                'failed_admin_attempts',
                'admin_locked_until',
                'allowed_ip_addresses',
                'require_password_change',
                'admin_session_timeout',
                'created_by_admin',
                'admin_role_assigned_at', 
                'admin_role_assigned_by'
                // Note: admin_notes is from previous migration, don't drop it
            ]);
        });
    }
};
