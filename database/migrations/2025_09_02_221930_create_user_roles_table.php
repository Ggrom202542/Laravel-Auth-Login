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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Reference to users table');
            $table->foreignId('role_id')->constrained()->onDelete('cascade')->comment('Reference to roles table');
            $table->timestamp('assigned_at')->useCurrent()->comment('When this role was assigned');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->comment('Who assigned this role');
            $table->timestamps();

            // Unique constraint to prevent duplicate role assignments
            $table->unique(['user_id', 'role_id']);
            
            // Add indexes for better performance
            $table->index('user_id');
            $table->index('role_id');
            $table->index('assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
