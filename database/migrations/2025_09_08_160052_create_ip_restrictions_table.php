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
        Schema::create('ip_restrictions', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip_address');
            $table->enum('type', ['whitelist', 'blacklist'])->default('blacklist');
            $table->string('reason')->nullable();
            $table->text('description')->nullable();
            
            // Geographic and ISP information
            $table->string('country_code', 2)->nullable();
            $table->string('country_name')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('isp')->nullable();
            $table->string('organization')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Security metrics
            $table->integer('failed_login_attempts')->default(0);
            $table->integer('suspicious_activities')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            
            // Management fields
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('auto_generated')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->unique('ip_address');
            $table->index(['type', 'status']);
            $table->index('last_activity_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_restrictions');
    }
};
