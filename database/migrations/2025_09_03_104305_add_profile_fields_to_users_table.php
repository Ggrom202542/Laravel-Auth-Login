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
        Schema::table('users', function (Blueprint $table) {
            // Personal Information (skip phone as it exists)
            $table->text('bio')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('bio');
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->after('date_of_birth');
            
            // Contact Information
            $table->text('address')->nullable()->after('gender');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('state');
            $table->string('country', 100)->nullable()->after('postal_code');
            
            // Rename profile_image to avatar if needed (skip for now - use existing profile_image)
            
            // User Preferences
            $table->string('theme', 20)->default('light')->after('profile_image');
            $table->string('language', 10)->default('th')->after('theme');
            
            // Notification Settings
            $table->boolean('email_notifications')->default(true)->after('language');
            $table->boolean('sms_notifications')->default(false)->after('email_notifications');
            $table->boolean('push_notifications')->default(true)->after('sms_notifications');
            
            // Account Activity (skip last_login_at as it exists)
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->text('login_history')->nullable()->after('last_login_ip');
            
            // Profile completion
            $table->boolean('profile_completed')->default(false)->after('login_history');
            $table->timestamp('profile_completed_at')->nullable()->after('profile_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'date_of_birth',
                'gender',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'theme',
                'language',
                'email_notifications',
                'sms_notifications',
                'push_notifications',
                'last_login_ip',
                'login_history',
                'profile_completed',
                'profile_completed_at'
            ]);
        });
    }
};
