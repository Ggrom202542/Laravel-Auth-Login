<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class EmergencyLogout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:emergency-logout {--force : Force logout without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emergency logout all users and clear all sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  This will logout ALL users and clear ALL sessions. Continue?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('🚨 Starting Emergency Logout...');

        // 1. Clear all caches
        $this->info('📝 Clearing application caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // 2. Clear session files
        $sessionPath = storage_path('framework/sessions');
        if (File::exists($sessionPath)) {
            $this->info('🗑️  Clearing session files...');
            File::cleanDirectory($sessionPath);
        }

        // 3. Clear cache data
        $cachePath = storage_path('framework/cache/data');
        if (File::exists($cachePath)) {
            $this->info('🧹 Clearing cache data...');
            File::cleanDirectory($cachePath);
        }

        // 4. Truncate sessions table (if using database driver)
        try {
            if (config('session.driver') === 'database') {
                $this->info('💾 Clearing database sessions...');
                DB::table('sessions')->truncate();
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not clear database sessions: ' . $e->getMessage());
        }

        // 5. Clear remember_me tokens
        try {
            $this->info('🔑 Clearing remember tokens...');
            DB::table('users')->update(['remember_token' => null]);
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not clear remember tokens: ' . $e->getMessage());
        }

        // 6. Clear personal access tokens (if using Sanctum/Passport)
        try {
            if (DB::getSchemaBuilder()->hasTable('personal_access_tokens')) {
                $this->info('🎫 Clearing personal access tokens...');
                DB::table('personal_access_tokens')->truncate();
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not clear personal access tokens: ' . $e->getMessage());
        }

        $this->info('✅ Emergency logout completed successfully!');
        $this->info('🔒 All users have been logged out.');
        $this->info('🧽 All sessions and caches have been cleared.');
        
        $this->newLine();
        $this->info('💡 Users will need to login again to access the application.');
    }
}
