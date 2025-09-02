<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActivity;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // ดึงกิจกรรมล่าสุดของผู้ใช้ 10 รายการ
        $recentActivities = UserActivity::where('user_id', $user->id)
                                      ->orderBy('created_at', 'desc')
                                      ->limit(10)
                                      ->get();

        // สถิติการใช้งานของผู้ใช้
        $stats = [
            'total_logins' => UserActivity::where('user_id', $user->id)
                                        ->where('action', 'login')
                                        ->count(),
            'last_login' => $user->last_login_at,
            'account_created' => $user->created_at,
            'profile_completion' => $this->calculateProfileCompletion($user),
        ];

        return view('user.dashboard', compact('user', 'recentActivities', 'stats'));
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
}
