<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // สถิติผู้ใช้
        $userStats = [
            'total_users' => User::withRole('user')->count(),
            'active_users' => User::withRole('user')->where('status', 'active')->count(),
            'new_users_today' => User::withRole('user')->whereDate('created_at', today())->count(),
            'new_users_this_month' => User::withRole('user')->whereMonth('created_at', now()->month)->count(),
        ];

        // สถิติการใช้งานวันนี้
        $todayStats = [
            'total_logins' => UserActivity::where('action', 'login')
                                        ->whereDate('created_at', today())
                                        ->count(),
            'active_now' => UserActivity::where('created_at', '>=', now()->subMinutes(15))
                                      ->distinct('user_id')
                                      ->count(),
        ];

        // กิจกรรมล่าสุด 15 รายการ
        $recentActivities = UserActivity::with('user')
                                      ->orderBy('created_at', 'desc')
                                      ->limit(15)
                                      ->get();

        // ผู้ใช้ใหม่ล่าสุด 10 รายการ
        $recentUsers = User::withRole('user')
                          ->orderBy('created_at', 'desc')
                          ->limit(10)
                          ->get();

        // ข้อมูลกราฟการลงทะเบียนย้อนหลัง 7 วัน
        $registrationData = $this->getRegistrationChartData();

        return view('admin.dashboard', compact(
            'userStats',
            'todayStats', 
            'recentActivities',
            'recentUsers',
            'registrationData'
        ));
    }

    /**
     * ดึงข้อมูลสำหรับกราฟการลงทะเบียน 7 วันล่าสุด
     */
    private function getRegistrationChartData()
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = User::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'count' => $count
            ];
        }
        
        return $data;
    }
}
