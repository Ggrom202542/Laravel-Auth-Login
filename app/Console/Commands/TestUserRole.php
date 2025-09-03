<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-role {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user role checking for a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User '{$username}' not found!");
            return;
        }

        $this->info("Testing user: {$user->username} (ID: {$user->id})");
        $this->info("Direct role field: " . ($user->role ?? 'null'));
        $this->info('');

        // Test relationships
        $this->info('=== ROLES RELATIONSHIP ===');
        $roles = $user->roles()->get();
        if ($roles->count() > 0) {
            foreach ($roles as $role) {
                $this->line("- {$role->name} ({$role->display_name})");
            }
        } else {
            $this->error('No roles found via relationship');
        }

        $this->info('');
        $this->info('=== HASROLE TESTS ===');
        $testRoles = ['super_admin', 'admin', 'user'];
        foreach ($testRoles as $roleName) {
            $hasRole = $user->hasRole($roleName);
            $status = $hasRole ? '✅ YES' : '❌ NO';
            $this->line("hasRole('{$roleName}'): {$status}");
        }

        $this->info('');
        $this->info('=== RAW QUERY TEST ===');
        $superAdminCheck = $user->roles()->where('name', 'super_admin')->exists();
        $this->line("Raw super_admin check: " . ($superAdminCheck ? '✅ TRUE' : '❌ FALSE'));
    }
}
