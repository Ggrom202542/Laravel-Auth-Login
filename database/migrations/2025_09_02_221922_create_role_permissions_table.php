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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade')->comment('Reference to roles table');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade')->comment('Reference to permissions table');
            $table->timestamps();

            // Composite primary key to prevent duplicates
            $table->primary(['role_id', 'permission_id']);
            
            // Add indexes for better performance
            $table->index('role_id');
            $table->index('permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
