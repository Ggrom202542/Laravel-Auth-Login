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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('User who performed the action');
            $table->string('action')->comment('Action performed (login, logout, create_user, etc.)');
            $table->text('description')->comment('Detailed description of the action');
            $table->ipAddress('ip_address')->comment('IP address of the user');
            $table->text('user_agent')->comment('User agent string');
            $table->json('properties')->nullable()->comment('Additional properties/metadata');
            $table->timestamp('created_at')->useCurrent();

            // Add indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
