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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Setting key (app.name, auth.lockout_time, etc.)');
            $table->text('value')->nullable()->comment('Setting value');
            $table->string('type')->default('string')->comment('Data type (string, integer, boolean, json)');
            $table->text('description')->nullable()->comment('Description of this setting');
            $table->boolean('is_editable')->default(true)->comment('Can this setting be edited via UI');
            $table->timestamps();

            // Add index for better performance
            $table->index('key');
            $table->index('is_editable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
