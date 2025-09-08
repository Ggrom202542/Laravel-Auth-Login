<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class Check2FAStatus extends Command
{
    protected $signature = 'check:2fa {username?}';
    protected $description = 'Check 2FA status and recovery codes for a user';

    public function handle()
    {
        $username = $this->argument('username');
        
        if ($username) {
            $user = User::where('username', $username)->first();
            if (!$user) {
                $this->error("User '{$username}' not found!");
                return;
            }
            $users = collect([$user]);
        } else {
            $users = User::all();
        }

        $this->info('=== 2FA Status Check ===');
        $this->info('TWO_FACTOR_ENABLED: ' . (config('auth.two_factor.enabled') ? 'true' : 'false'));
        $this->info('');

        foreach ($users as $user) {
            $this->info("User: {$user->username} (ID: {$user->id})");
            $this->info("  Email: {$user->email}");
            $this->info("  Role: {$user->role}");
            $this->info("  2FA Enabled: " . ($user->google2fa_enabled ? 'YES' : 'NO'));
            $this->info("  Has Secret: " . ($user->google2fa_secret ? 'YES' : 'NO'));
            $this->info("  Has Recovery Codes: " . ($user->hasRecoveryCodes() ? 'YES' : 'NO'));
            
            if ($user->hasRecoveryCodes()) {
                $this->info("  Recovery Codes Count: " . count($user->recovery_codes));
                foreach ($user->recovery_codes as $i => $code) {
                    $this->info("    " . ($i + 1) . ". {$code}");
                }
            }
            
            $this->info('');
        }
    }
}
