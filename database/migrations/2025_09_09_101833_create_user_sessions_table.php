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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('device_name')->nullable(); // Chrome, Safari, etc.
            $table->string('platform')->nullable(); // Windows, macOS, iOS, Android
            $table->string('browser')->nullable(); // Chrome, Firefox, Safari
            $table->string('location_country')->nullable();
            $table->string('location_city')->nullable();
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_lng', 11, 8)->nullable();
            $table->timestamp('last_activity')->index();
            $table->timestamp('login_at');
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('is_current')->default(false); // current session of user
            $table->boolean('is_trusted')->default(false); // trusted device
            $table->boolean('is_active')->default(true)->index();
            $table->json('payload')->nullable(); // additional session data
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'last_activity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
