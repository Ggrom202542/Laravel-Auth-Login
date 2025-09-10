<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActivity;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // ดึงกิจกรรมล่าสุดของผู้ใช้ 5 รายการสำหรับแสดงผล
        $recentActivities = UserActivity::where('user_id', $user->id)
                                      ->orderBy('created_at', 'desc')
                                      ->limit(5)
                                      ->get();
        
        // นับจำนวนกิจกรรมทั้งหมดของผู้ใช้
        $totalActivitiesCount = UserActivity::where('user_id', $user->id)->count();

        // สถิติการใช้งานของผู้ใช้
        $stats = [
            'total_logins' => UserActivity::where('user_id', $user->id)
                                        ->where('action', 'login')
                                        ->count(),
            'today_activities' => UserActivity::where('user_id', $user->id)
                                            ->whereDate('created_at', today())
                                            ->count(),
            'total_activities' => $totalActivitiesCount,
            'last_login' => $user->last_login_at,
            'account_created' => $user->created_at,
            'profile_completion' => $this->calculateProfileCompletion($user),
        ];

        // ข้อมูลกราฟกิจกรรม 7 วันที่ผ่านมา
        $chartData = $this->getActivityChartData($user->id);

        return view('user.dashboard', compact('user', 'recentActivities', 'stats', 'chartData', 'totalActivitiesCount'));
    }

    /**
     * คำนวณเปอร์เซ็นต์ความสมบูรณ์ของโปรไฟล์
     */
    private function calculateProfileCompletion($user)
    {
        $fields = ['first_name', 'last_name', 'email', 'phone', 'profile_image'];
        $completed = 0;
        
        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $completed++;
            }
        }
        
        return round(($completed / count($fields)) * 100);
    }

    /**
     * ดึงข้อมูลกราฟกิจกรรม 7 วันที่ผ่านมา
     */
    private function getActivityChartData($userId)
    {
        $labels = [];
        $data = [];
        
        // สร้างข้อมูล 7 วันที่ผ่านมา
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('j M'); // เช่น "6 ก.ย."
            
            // นับจำนวนกิจกรรมในวันนั้น
            $activityCount = UserActivity::where('user_id', $userId)
                                        ->whereDate('created_at', $date->format('Y-m-d'))
                                        ->count();
            $data[] = $activityCount;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}
