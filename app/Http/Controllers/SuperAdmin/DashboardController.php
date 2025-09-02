<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\SystemSetting;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index()
    {
        // สถิติระบบโดยรวม
        $systemStats = [
            'total_users' => User::count(),
            'total_admins' => User::withRole('admin')->count(),
            'total_super_admins' => User::withRole('super_admin')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'locked_accounts' => User::where('locked_until', '>', now())->count(),
        ];

        // สถิติการใช้งานวันนี้
        $todayStats = [
            'total_logins' => UserActivity::where('action', 'login')
                                        ->whereDate('created_at', today())
                                        ->count(),
            'failed_logins' => UserActivity::where('action', 'login')
                                         ->whereDate('created_at', today())
                                         ->where('description', 'like', '%failed%')
                                         ->count(),
            'new_registrations' => User::whereDate('created_at', today())->count(),
            'admin_activities' => UserActivity::whereHas('user', function($query) {
                                    $query->whereHas('roles', function($q) {
                                        $q->whereIn('name', ['admin', 'super_admin']);
                                    });
                                })
                                ->whereDate('created_at', today())
                                ->count(),
        ];

        // กิจกรรมระบบล่าสุด 20 รายการ
        $recentActivities = UserActivity::with('user')
                                      ->orderBy('created_at', 'desc')
                                      ->limit(20)
                                      ->get();

        // ผู้ใช้ที่เข้าระบบล่าสุด
        $recentUsers = User::orderBy('last_login_at', 'desc')
                          ->limit(10)
                          ->get();

        // การตั้งค่าระบบที่สำคัญ
        $systemSettings = [
            'registration_enabled' => SystemSetting::get('auth.registration_enabled', true),
            'maintenance_mode' => SystemSetting::get('maintenance.enabled', false),
            'max_login_attempts' => SystemSetting::get('auth.max_login_attempts', 5),
            'session_timeout' => SystemSetting::get('auth.session_timeout', 120),
        ];

        // ข้อมูลกราฟการใช้งาน 30 วันล่าสุด
        $usageData = $this->getUsageChartData();

        // ข้อมูลกราฟการลงทะเบียน 30 วันล่าสุด
        $registrationData = $this->getRegistrationChartData();

        return view('super-admin.dashboard', compact(
            'systemStats',
            'todayStats',
            'recentActivities',
            'recentUsers',
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
            $loginCount = UserActivity::where('action', 'login')
                                    ->whereDate('created_at', $date)
                                    ->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'logins' => $loginCount
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
            $count = User::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'registrations' => $count
            ];
        }
        
        return $data;
    }
}
