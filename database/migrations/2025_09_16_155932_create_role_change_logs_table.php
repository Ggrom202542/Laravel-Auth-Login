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
        Schema::create('role_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->string('old_role');
            $table->string('new_role');
            $table->text('reason');
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['user_id', 'performed_at']);
            $table->index(['changed_by', 'performed_at']);
            $table->index('performed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_change_logs');
    }
};
