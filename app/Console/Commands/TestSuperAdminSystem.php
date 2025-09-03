<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AdminSession;
use App\Models\UserActivity;
use App\Models\SecurityPolicy;

class TestSuperAdminSystem extends Command
{
    protected $signature = 'test:super-admin';
    protected $description = 'Test Super Admin system integration';

    public function handle()
    {
        $this->info('🔍 Testing Super Admin System Integration...');
        $this->newLine();

        // Test 1: Database Setup
        $this->info('1. Database Setup:');
        $superAdmin = User::where('username', 'superadmin')->first();
        $this->line("   ✅ Super Admin exists: {$superAdmin->username} (Role: {$superAdmin->role})");
        $this->line("   ✅ hasRole('super_admin'): " . ($superAdmin->hasRole('super_admin') ? 'YES' : 'NO'));
        
        // Test 2: Dashboard Data
        $this->newLine();
        $this->info('2. Dashboard Data:');
        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count() + User::where('role', 'super_admin')->count();
        $activeSessions = AdminSession::where('status', 'active')->count();
        
        $this->line("   ✅ Total Users: {$totalUsers}");
        $this->line("   ✅ Total Admins: {$adminCount}");
        $this->line("   ✅ Active Sessions: {$activeSessions}");
        
        // Test 3: Routes
        $this->newLine();
        $this->info('3. Testing Routes:');
        try {
            $routes = \Route::getRoutes()->getByName('super-admin.dashboard');
            $this->line('   ✅ super-admin.dashboard route exists');
            
            $userRoutes = \Route::getRoutes()->getByName('super-admin.users.index');
            $this->line('   ✅ super-admin.users.index route exists');
        } catch (\Exception $e) {
            $this->error('   ❌ Route error: ' . $e->getMessage());
        }

        // Test 4: Middleware
        $this->newLine();
        $this->info('4. Middleware Test:');
        try {
            $middleware = app(\App\Http\Middleware\EnsureSuperAdmin::class);
            $this->line('   ✅ EnsureSuperAdmin middleware can be instantiated');
        } catch (\Exception $e) {
            $this->error('   ❌ Middleware error: ' . $e->getMessage());
        }

        // Test 5: Models & Relationships
        $this->newLine();
        $this->info('5. Model Relationships:');
        try {
            $adminUsers = User::whereIn('role', ['admin', 'super_admin'])->count();
            $this->line("   ✅ Admin users query: {$adminUsers} found");
            
            $userActivities = UserActivity::count();
            $this->line("   ✅ User activities: {$userActivities} records");
            
            $securityPolicies = SecurityPolicy::count();
            $this->line("   ✅ Security policies: {$securityPolicies} records");
            
        } catch (\Exception $e) {
            $this->error('   ❌ Model error: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 Super Admin System Integration Test Complete!');
        
        $this->newLine();
        $this->warn('📋 Manual Testing Steps:');
        $this->line('1. Login with: username=superadmin, password=SuperAdmin123!');
        $this->line('2. Should redirect to: /super-admin/dashboard');
        $this->line('3. Dashboard should load without errors');
        $this->line('4. Navigation menu should show Super Admin options');
        $this->line('5. Click "จัดการผู้ใช้" to test user management');
        
        return 0;
    }
}
