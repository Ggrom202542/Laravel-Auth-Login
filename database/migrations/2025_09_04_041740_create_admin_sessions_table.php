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
        Schema::create('admin_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('Admin user ID');
            $table->string('session_id')->unique()->comment('Laravel session ID');
            $table->ipAddress('ip_address')->comment('Login IP address');
            $table->text('user_agent')->comment('Browser/device info');
            $table->timestamp('login_at')->comment('Login timestamp');
            $table->timestamp('last_activity')->comment('Last activity timestamp');
            $table->timestamp('logout_at')->nullable()->comment('Logout timestamp');
            $table->enum('status', ['active', 'expired', 'terminated'])
                   ->default('active')
                   ->comment('Session status');
            $table->enum('login_method', ['password', '2fa', 'social'])
                   ->default('password')
                   ->comment('Login method used');
            $table->json('security_flags')->nullable()->comment('Security-related flags');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['session_id', 'status']);
            $table->index(['ip_address', 'login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_sessions');
    }
};
