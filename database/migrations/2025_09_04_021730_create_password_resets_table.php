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
        // Drop existing table if exists
        Schema::dropIfExists('password_resets');
        
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reset_by')->constrained('users')->onDelete('cascade'); // Admin who reset
            $table->string('reset_type')->default('admin_reset'); // admin_reset, user_request, system
            $table->text('reason')->nullable(); // Reason for reset
            $table->boolean('notification_sent')->default(false); // Was notification sent?
            $table->json('notification_methods')->nullable(); // SMS, Email, etc.
            $table->json('notification_results')->nullable(); // Results of each method
            $table->timestamp('password_changed_at')->nullable(); // When user actually changed password
            $table->boolean('is_used')->default(false); // Has temporary password been used?
            $table->string('ip_address', 45)->nullable(); // IP where reset was requested
            $table->text('user_agent')->nullable(); // User agent
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['reset_by', 'created_at']);
            $table->index('is_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
