<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

class CheckData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check database data for roles, users, and assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ROLES TABLE ===');
        $roles = Role::all();
        if ($roles->count() > 0) {
            foreach ($roles as $role) {
                $this->line("ID: {$role->id}, Name: {$role->name}, Display Name: {$role->display_name}");
            }
        } else {
            $this->error("No roles found in database");
        }

        $this->info('');
        $this->info('=== USERS TABLE ===');
        $users = User::all();
        if ($users->count() > 0) {
            foreach ($users as $user) {
                $this->line("ID: {$user->id}, Username: {$user->username}, Role: " . ($user->role ?? 'null'));
            }
        } else {
            $this->error("No users found in database");
        }

        $this->info('');
        $this->info('=== USER_ROLES TABLE ===');
        $userRoles = DB::table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('users.username', 'roles.name as role_name', 'user_roles.assigned_at')
            ->get();

        if ($userRoles->count() > 0) {
            foreach ($userRoles as $ur) {
                $this->line("User: {$ur->username}, Role: {$ur->role_name}, Assigned: {$ur->assigned_at}");
            }
        } else {
            $this->error("No user role assignments found");
        }

        $this->info('');
        $this->info('=== ROLE STATISTICS ===');
        $superAdmins = User::whereHas('roles', function($q) { $q->where('name', 'super_admin'); })->count();
        $admins = User::whereHas('roles', function($q) { $q->where('name', 'admin'); })->count();
        $regularUsers = User::whereHas('roles', function($q) { $q->where('name', 'user'); })->count();
        
        $this->line("Super Admins: {$superAdmins}");
        $this->line("Admins: {$admins}");
        $this->line("Regular Users: {$regularUsers}");
    }
}
