<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RegistrationApproval;

class CheckUserStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check-status {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user status and approval details';

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
        
        $this->info("User Details for: {$username}");
        $this->line('=====================================');
        $this->info("ID: {$user->id}");
        $this->info("Name: {$user->first_name} {$user->last_name}");
        $this->info("Email: {$user->email}");
        $this->info("Status: {$user->status}");
        $this->info("Approval Status: {$user->approval_status}");
        $this->info("Role: {$user->role}");
        $this->info("Created: {$user->created_at}");
        $this->info("Approved At: " . ($user->approved_at ? $user->approved_at : 'Not approved'));
        
        // Check approval record
        $approval = RegistrationApproval::where('user_id', $user->id)->first();
        
        if ($approval) {
            $this->line('');
            $this->info("Approval Record:");
            $this->line('=====================================');
            $this->info("Status: {$approval->status}");
            $this->info("Reviewed By: " . ($approval->reviewed_by ? "User ID {$approval->reviewed_by}" : 'Not reviewed'));
            $this->info("Reviewed At: " . ($approval->reviewed_at ? $approval->reviewed_at : 'Not reviewed'));
            $this->info("Rejection Reason: " . ($approval->rejection_reason ?: 'N/A'));
        } else {
            $this->line('');
            $this->warn("No approval record found!");
        }
        
        // Recommendations
        $this->line('');
        $this->info("Recommendations:");
        $this->line('=====================================');
        
        if ($user->status !== 'active') {
            $this->warn("⚠️  User status is not 'active' - this will prevent login");
        }
        
        if ($user->approval_status !== 'approved') {
            $this->warn("⚠️  User approval_status is not 'approved' - check if approval process completed");
        }
        
        if ($approval && $approval->status !== 'approved') {
            $this->warn("⚠️  Approval record status is not 'approved'");
        }
        
        if ($user->status === 'active' && $user->approval_status === 'approved') {
            $this->info("✅ User should be able to login successfully");
        }
    }
}
