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
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('action'); // login, logout, activity, force_logout, expired
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('location_country')->nullable();
            $table->string('location_city')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable(); // admin who performed action
            $table->text('reason')->nullable(); // reason for force logout etc.
            $table->json('metadata')->nullable(); // additional data
            $table->timestamp('performed_at')->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'action']);
            $table->index(['user_id', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_logs');
    }
};
