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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Permission name (users.view, users.create, etc.)');
            $table->string('display_name')->comment('Display name for UI');
            $table->text('description')->nullable()->comment('Permission description');
            $table->string('module')->comment('Module this permission belongs to (users, admin, system)');
            $table->timestamps();

            // Add index for better performance
            $table->index(['module', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
