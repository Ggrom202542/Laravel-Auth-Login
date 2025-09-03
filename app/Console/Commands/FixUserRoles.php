<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FixUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix user role fields to match user_roles relationship';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== UPDATING USER ROLE FIELDS ===');
        
        // Get users with their primary roles from user_roles table
        $userRoles = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->select('users.id as user_id', 'users.username', 'roles.name as role_name')
            ->get();

        foreach ($userRoles as $userRole) {
            $this->line("Updating {$userRole->username}: role field → '{$userRole->role_name}'");
            
            DB::table('users')
                ->where('id', $userRole->user_id)
                ->update(['role' => $userRole->role_name]);
        }

        $this->info('');
        $this->info('=== VERIFICATION ===');
        $users = User::all();
        foreach ($users as $user) {
            $this->line("ID: {$user->id}, Username: {$user->username}, Role: {$user->role}");
        }

        $this->info('');
        $this->success('✅ User role fields updated successfully!');
    }
}
