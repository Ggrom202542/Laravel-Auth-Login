<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class Test2FALogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:test-2fa-login {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test 2FA login flow for a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User '{$username}' not found!");
            return 1;
        }

        $this->info("Testing 2FA login flow for: {$user->username}");
        $this->info("User ID: {$user->id}");
        $this->info("Role: {$user->role}");
        
        // ตรวจสอบ configuration
        $this->info("\n=== Configuration Check ===");
        $this->info("TWO_FACTOR_ENABLED (env): " . (env('TWO_FACTOR_ENABLED') ? 'true' : 'false'));
        $this->info("config('auth.two_factor.enabled'): " . (config('auth.two_factor.enabled') ? 'true' : 'false'));
        $this->info("config('auth.two_factor.enforce_for_all_users'): " . (config('auth.two_factor.enforce_for_all_users') ? 'true' : 'false'));

        // ตรวจสอบสถานะ 2FA ของผู้ใช้
        $this->info("\n=== User 2FA Status ===");
        $this->info("two_factor_enabled: " . ($user->two_factor_enabled ? 'true' : 'false'));
        $this->info("google2fa_enabled: " . ($user->google2fa_enabled ? 'true' : 'false'));
        $this->info("google2fa_secret: " . ($user->google2fa_secret ? 'exists' : 'empty'));
        $this->info("google2fa_confirmed_at: " . ($user->google2fa_confirmed_at ? $user->google2fa_confirmed_at : 'null'));

        // ทดสอบ hasTwoFactorEnabled method
        $this->info("\n=== Method Testing ===");
        $hasTwoFactor = $user->hasTwoFactorEnabled();
        $this->info("hasTwoFactorEnabled(): " . ($hasTwoFactor ? 'true' : 'false'));

        // สร้าง instance ของ LoginController เพื่อทดสอบ requiresTwoFactorAuth
        $loginController = new \App\Http\Controllers\Auth\LoginController();
        $reflection = new \ReflectionClass($loginController);
        $method = $reflection->getMethod('requiresTwoFactorAuth');
        $method->setAccessible(true);
        
        $requiresTwoFactor = $method->invoke($loginController, $user);
        $this->info("requiresTwoFactorAuth(): " . ($requiresTwoFactor ? 'true' : 'false'));

        // สรุปผล
        $this->info("\n=== Summary ===");
        if (config('auth.two_factor.enabled') && $hasTwoFactor && $requiresTwoFactor) {
            $this->info("✅ 2FA should be triggered for this user during login");
        } else {
            $this->error("❌ 2FA will NOT be triggered for this user");
            
            if (!config('auth.two_factor.enabled')) {
                $this->error("   - 2FA is disabled in configuration");
            }
            if (!$hasTwoFactor) {
                $this->error("   - User does not have 2FA properly enabled");
            }
            if (!$requiresTwoFactor) {
                $this->error("   - requiresTwoFactorAuth() returned false");
            }
        }

        return 0;
    }
}
