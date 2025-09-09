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
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->boolean('is_suspicious')->default(false)->after('is_trusted');
            $table->text('suspicious_reason')->nullable()->after('is_suspicious');
            $table->timestamp('suspicious_detected_at')->nullable()->after('suspicious_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropColumn(['is_suspicious', 'suspicious_reason', 'suspicious_detected_at']);
        });
    }
};
