<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class Sync2FAStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:sync-2fa-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync two_factor_enabled field with google2fa_enabled status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing 2FA status for all users...');

        $users = User::whereNotNull('google2fa_secret')
                    ->where('google2fa_enabled', true)
                    ->whereNotNull('google2fa_confirmed_at')
                    ->get();

        foreach ($users as $user) {
            $user->two_factor_enabled = true;
            $user->save();
            
            $this->info("Updated 2FA status for user: {$user->username} (ID: {$user->id})");
        }

        $this->info('2FA status sync completed!');
        
        // แสดงสถานะปัจจุบัน
        $this->info("\nCurrent 2FA enabled users:");
        $enabledUsers = User::where(function($query) {
            $query->where('two_factor_enabled', true)
                  ->orWhere('google2fa_enabled', true);
        })->whereNotNull('google2fa_secret')
          ->whereNotNull('google2fa_confirmed_at')
          ->get();

        foreach ($enabledUsers as $user) {
            $this->line("- {$user->username} ({$user->role}): two_factor_enabled={$user->two_factor_enabled}, google2fa_enabled={$user->google2fa_enabled}");
        }

        return 0;
    }
}
