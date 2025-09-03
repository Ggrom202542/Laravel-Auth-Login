<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\AdminSession;
use App\Models\SecurityPolicy;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index()
    {
        // สถิติระบบโดยรวม (ปรับให้ตรงกับ view)
        $stats = [
            'total_users' => User::count(),
            'admin_count' => User::where('role', 'admin')->count() + User::where('role', 'super_admin')->count(),
            'online_users' => AdminSession::where('status', 'active')->count(),
            'today_activities' => UserActivity::whereDate('created_at', today())->count(),
            'locked_accounts' => User::where('status', 'suspended')->count(),
            'total_roles' => 3, // user, admin, super_admin
        ];

        // สถิติระบบโดยรวม
        $systemStats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_super_admins' => User::where('role', 'super_admin')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'pending_approvals' => User::where('approval_status', 'pending')->count(),
        ];

        // สถิติการใช้งานวันนี้
        $todayStats = [
            'total_logins' => AdminSession::whereDate('created_at', today())->count(),
            'active_sessions' => AdminSession::where('status', 'active')->count(),
            'failed_logins' => AdminSession::where('login_method', 'failed')
                                         ->whereDate('created_at', today())->count(),
            'new_registrations' => User::whereDate('created_at', today())->count(),
        ];

        // สถิติ Security
        $securityStats = [
            'two_fa_enabled' => User::where('two_factor_enabled', true)->count(),
            'ip_restricted_users' => User::whereNotNull('allowed_ip_addresses')->count(),
            'active_security_policies' => SecurityPolicy::active()->count(),
            'recent_security_alerts' => UserActivity::where('action', 'like', '%security%')
                                                  ->whereDate('created_at', '>=', now()->subDays(7))
                                                  ->count(),
        ];

        // Admin Users สำหรับ view (แค่ admins และ super admins)
        $adminUsers = User::whereIn('role', ['admin', 'super_admin'])
                         ->orderBy('created_at', 'desc')
                         ->limit(10)
                         ->get();

        // Security Logs สำหรับ view
        $securityLogs = UserActivity::with('user')
                                   ->whereIn('action', ['login', 'login_failed', 'logout', 'password_changed', 'account_locked'])
                                   ->orderBy('created_at', 'desc')
                                   ->limit(10)
                                   ->get();

        // System Health (mock data - ในการใช้งานจริงจะดึงจากระบบ)
        $systemHealth = [
            'cpu' => rand(10, 80),
            'memory' => rand(20, 75),
            'disk' => rand(15, 60),
            'uptime' => '15 วัน',
            'load' => number_format(rand(100, 300) / 100, 2),
        ];

        // Performance Chart Data สำหรับ view
        $performanceChartData = [
            'labels' => collect(range(6, 0))->map(fn($day) => now()->subDays($day)->format('M j'))->toArray(),
            'logins' => collect(range(6, 0))->map(fn($day) => AdminSession::whereDate('created_at', now()->subDays($day))->count())->toArray(),
            'activities' => collect(range(6, 0))->map(fn($day) => UserActivity::whereDate('created_at', now()->subDays($day))->count())->toArray(),
            'errors' => collect(range(6, 0))->map(fn($day) => rand(0, 5))->toArray(),
        ];

        // กิจกรรม Admin ล่าสุด 15 รายการ
        $recentActivities = UserActivity::with('user')
                                      ->whereHas('user', function($query) {
                                          $query->whereIn('role', ['admin', 'super_admin']);
                                      })
                                      ->orderBy('created_at', 'desc')
                                      ->limit(20)
                                      ->get();

        // ผู้ใช้ใหม่ล่าสุด 10 คน
        $recentUsers = User::orderBy('created_at', 'desc')
                          ->limit(10)
                          ->get();

        // Sessions ที่กำลังใช้งาน
        $activeSessions = AdminSession::with('user')
                                    ->where('status', 'active')
                                    ->orderBy('last_activity', 'desc')
                                    ->limit(10)
                                    ->get();

        // การตั้งค่าระบบที่สำคัญ (ใช้ config แทน SystemSetting)
        $systemSettings = [
            'registration_enabled' => config('auth.registration_enabled', true),
            'maintenance_mode' => config('app.maintenance_mode', false),
            'max_login_attempts' => config('auth.max_login_attempts', 5),
            'session_timeout' => config('session.lifetime', 120),
        ];

        // ข้อมูลกราฟการใช้งาน 30 วันล่าสุด
        $usageData = $this->getUsageChartData();

        // ข้อมูลกราฟการลงทะเบียน 30 วันล่าสุด
        $registrationData = $this->getRegistrationChartData();

        return view('super-admin.dashboard', compact(
            'stats',
            'systemStats',
            'todayStats',
            'securityStats',
            'adminUsers',
            'securityLogs',
            'systemHealth',
            'performanceChartData',
            'recentActivities',
            'recentUsers',
            'activeSessions',
            'systemSettings',
            'usageData',
            'registrationData'
        ));
    }

    /**
     * ดึงข้อมูลสำหรับกราฟการใช้งาน 30 วันล่าสุด
     */
    private function getUsageChartData()
    {
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $loginCount = AdminSession::whereDate('created_at', $date)->count();
            $activeCount = User::where('last_login_at', '>=', $date->startOfDay())
                             ->where('last_login_at', '<=', $date->endOfDay())
                             ->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'logins' => $loginCount,
                'active_users' => $activeCount
            ];
        }
        
        return $data;
    }

    /**
     * ดึงข้อมูลสำหรับกราฟการลงทะเบียน 30 วันล่าสุด
     */
    private function getRegistrationChartData()
    {
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $registrationCount = User::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'registrations' => $registrationCount
            ];
        }
        
        return $data;
    }
}
