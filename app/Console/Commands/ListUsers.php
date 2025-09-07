<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users with their roles and status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📋 รายการผู้ใช้ในระบบ');
        $this->newLine();

        $users = User::select('id', 'username', 'email', 'role', 'status', 'created_at')
                    ->orderBy('role')
                    ->orderBy('username')
                    ->get();

        if ($users->isEmpty()) {
            $this->error('❌ ไม่มีผู้ใช้ในระบบ');
            return;
        }

        $headers = ['ID', 'Username', 'Email', 'Role', 'Status', 'Created'];
        $rows = [];

        foreach ($users as $user) {
            $rows[] = [
                $user->id,
                $user->username,
                $user->email,
                $user->role,
                $user->status,
                $user->created_at->format('Y-m-d H:i')
            ];
        }

        $this->table($headers, $rows);

        // Summary
        $totalUsers = $users->count();
        $superAdmins = $users->where('role', 'super_admin')->count();
        $admins = $users->where('role', 'admin')->count();
        $regularUsers = $users->where('role', 'user')->count();
        $activeUsers = $users->where('status', 'active')->count();

        $this->newLine();
        $this->info("📊 สรุป:");
        $this->info("👥 ผู้ใช้ทั้งหมด: {$totalUsers}");
        $this->info("👑 Super Admin: {$superAdmins}");
        $this->info("🛡️  Admin: {$admins}");
        $this->info("👤 User: {$regularUsers}");
        $this->info("✅ Active: {$activeUsers}");
    }
}
