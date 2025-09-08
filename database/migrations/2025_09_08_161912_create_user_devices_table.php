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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Device Identification
            $table->string('device_fingerprint', 255)->unique();
            $table->string('device_name')->nullable(); // Custom name set by user
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            
            // Browser & OS Information
            $table->string('browser_name')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('platform')->nullable();
            
            // Device Specifications
            $table->string('screen_resolution')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language')->nullable();
            $table->text('user_agent')->nullable();
            
            // Security Information
            $table->string('ip_address', 45)->nullable();
            $table->string('location')->nullable(); // City, Country
            $table->boolean('is_trusted')->default(false);
            $table->boolean('is_active')->default(true);
            
            // Trust & Activity Tracking
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('trusted_at')->nullable();
            $table->integer('login_count')->default(0);
            
            // Security Flags
            $table->boolean('requires_verification')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_method')->nullable(); // email, sms, authenticator
            
            // Management
            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable(); // For temporary device access
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'is_trusted']);
            $table->index(['user_id', 'is_active']);
            $table->index(['device_fingerprint']);
            $table->index(['last_seen_at']);
            $table->index(['ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
