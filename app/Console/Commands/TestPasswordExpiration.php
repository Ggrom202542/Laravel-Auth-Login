<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\PasswordExpirationService;
use Carbon\Carbon;

class TestPasswordExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:password-expiration {user_id} {--expired : Set password as expired} {--expiring : Set password expiring in 3 days} {--minutes= : Set password expiring in X minutes} {--reset : Reset to normal expiration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test password expiration functionality';

    protected $passwordService;

    public function __construct(PasswordExpirationService $passwordService)
    {
        parent::__construct();
        $this->passwordService = $passwordService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return;
        }

        $this->info("Testing password expiration for user: {$user->email}");

        if ($this->option('expired')) {
            // Set password as expired (yesterday)
            $user->password_expires_at = Carbon::yesterday();
            $user->password_expiration_enabled = true;
            $user->save();
            $this->warn("✅ Password set as EXPIRED (expires: {$user->password_expires_at})");
            
        } elseif ($this->option('expiring')) {
            // Set password expiring in 3 days
            $user->password_expires_at = Carbon::now()->addDays(3);
            $user->password_expiration_enabled = true;
            $user->save();
            $this->info("✅ Password set as EXPIRING in 3 days (expires: {$user->password_expires_at})");
            
        } elseif ($this->option('minutes')) {
            // Set password expiring in X minutes
            $minutes = (int) $this->option('minutes');
            $user->password_expires_at = Carbon::now()->addMinutes($minutes);
            $user->password_expiration_enabled = true;
            $user->save();
            $this->info("✅ Password set to expire in {$minutes} minutes (expires: {$user->password_expires_at})");
            
        } elseif ($this->option('reset')) {
            // Reset to normal expiration (90 days from now)
            $config = config('password_policy.expiration');
            $expirationDays = $config['days'] ?? 90;
            $user->password_expires_at = Carbon::now()->addDays($expirationDays);
            $user->password_expiration_enabled = true;
            $user->password_warned_at = null;
            $user->save();
            $this->info("✅ Password expiration RESET to normal ({$expirationDays} days from now)");
            
        } else {
            // Show current status
            $this->info("Current password expiration status:");
            $this->table([
                'Field', 'Value'
            ], [
                ['Expiration Enabled', $user->password_expiration_enabled ? 'Yes' : 'No'],
                ['Expires At', $user->password_expires_at ?? 'Not set'],
                ['Changed At', $user->password_changed_at ?? 'Not set'],
                ['Warned At', $user->password_warned_at ?? 'Not set'],
                ['Is Expired', $this->passwordService->isPasswordExpired($user) ? 'Yes' : 'No'],
                ['Days Until Expiration', $this->passwordService->getDaysUntilExpiration($user) ?? 'N/A'],
                ['Should Show Warning', $this->passwordService->shouldShowWarning($user) ? 'Yes' : 'No'],
            ]);
        }

        // Show usage examples
        $this->newLine();
        $this->comment('Usage examples:');
        $this->line("php artisan test:password-expiration {$userId} --expired         # Set as expired");
        $this->line("php artisan test:password-expiration {$userId} --expiring        # Set as expiring in 3 days");
        $this->line("php artisan test:password-expiration {$userId} --minutes=5       # Set to expire in 5 minutes");
        $this->line("php artisan test:password-expiration {$userId} --reset           # Reset to normal expiration");
        $this->line("php artisan test:password-expiration {$userId}                   # Show current status");
    }
}
