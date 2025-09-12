<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
    }

    /**
     * Display reports dashboard
     */
    public function index()
    {
        // สถิติภาพรวม
        $overview = [
            'total_users' => User::where('role', 'user')->count(),
            'new_users_this_month' => User::where('role', 'user')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'active_users_today' => ActivityLog::whereDate('created_at', today())
                ->distinct('user_id')
                ->count('user_id'),
            'total_activities' => ActivityLog::count(),
            'activities_today' => ActivityLog::whereDate('created_at', today())->count(),
            'suspicious_activities' => ActivityLog::where('is_suspicious', true)->count(),
        ];

        // กราฟการลงทะเบียน 30 วันล่าสุด
        $registrationChart = $this->getRegistrationChartData(30);
        
        // กิจกรรมรายวัน 7 วันล่าสุด
        $activityChart = $this->getActivityChartData(7);
        
        // สถิติการใช้งานตามเวลา
        $hourlyStats = $this->getHourlyActivityStats();
        
        // Top 5 กิจกรรมที่ทำบ่อยที่สุด
        $topActivities = ActivityLog::select('activity_type', DB::raw('count(*) as count'))
            ->groupBy('activity_type')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.reports.index', compact(
            'overview',
            'registrationChart',
            'activityChart',
            'hourlyStats',
            'topActivities'
        ));
    }

    /**
     * User reports
     */
    public function users(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        // สถิติผู้ใช้งาน
        $userStats = [
            'total' => User::where('role', 'user')->count(),
            'active' => User::where('role', 'user')->where('status', 'active')->count(),
            'inactive' => User::where('role', 'user')->where('status', 'inactive')->count(),
            'pending' => User::where('role', 'user')->where('status', 'pending')->count(),
            'with_2fa' => User::where('role', 'user')
                ->whereNotNull('two_factor_confirmed_at')
                ->count(),
            'new_this_period' => User::where('role', 'user')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
        ];

        // การลงทะเบียนรายวัน
        $dailyRegistrations = User::where('role', 'user')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ผู้ใช้งานที่ใช้งานบ่อยที่สุด
        $activeUsers = User::where('role', 'user')
            ->withCount(['activityLogs' => function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->orderBy('activity_logs_count', 'desc')
            ->limit(10)
            ->get();

        // การกระจายตามสถานะ
        $statusDistribution = User::where('role', 'user')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return view('admin.reports.users', compact(
            'userStats',
            'dailyRegistrations',
            'activeUsers',
            'statusDistribution',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Activity reports
     */
    public function activities(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        // สถิติกิจกรรม
        $activityStats = [
            'total' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'logins' => ActivityLog::where('activity_type', 'login')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'logouts' => ActivityLog::where('activity_type', 'logout')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'creates' => ActivityLog::where('activity_type', 'create')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'updates' => ActivityLog::where('activity_type', 'update')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'deletes' => ActivityLog::where('activity_type', 'delete')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'suspicious' => ActivityLog::where('is_suspicious', true)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
        ];

        // กิจกรรมรายวัน
        $dailyActivities = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // กิจกรรมตามประเภท
        $activityTypes = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('activity_type, COUNT(*) as count')
            ->groupBy('activity_type')
            ->orderBy('count', 'desc')
            ->get();

        // กิจกรรมตามช่วงเวลา (24 ชั่วโมง)
        $hourlyActivity = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Top IP Addresses
        $topIPs = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('ip_address, COUNT(*) as count')
            ->groupBy('ip_address')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.reports.activities', compact(
            'activityStats',
            'dailyActivities',
            'activityTypes',
            'hourlyActivity',
            'topIPs',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Security reports
     */
    public function security(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        // สถิติความปลอดภัย
        $securityStats = [
            'successful_logins' => ActivityLog::where('activity_type', 'login')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'failed_logins' => ActivityLog::where('activity_type', 'login_failed')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'suspicious_activities' => ActivityLog::where('is_suspicious', true)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            '2fa_enabled' => User::where('role', 'user')
                ->whereNotNull('two_factor_confirmed_at')
                ->count(),
            '2fa_percentage' => 0, // Will be calculated below
        ];

        // Calculate 2FA percentage
        $totalUsers = User::where('role', 'user')->count();
        if ($totalUsers > 0) {
            $securityStats['2fa_percentage'] = ($securityStats['2fa_enabled'] / $totalUsers) * 100;
        }

        // Calculate security score
        $securityScore = $this->calculateSecurityScore($securityStats, $totalUsers);

        // การเข้าสู่ระบบรายวัน
        $dailyLogins = ActivityLog::whereIn('activity_logs.activity_type', ['login', 'login_failed'])
            ->whereBetween('activity_logs.created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(activity_logs.created_at) as date, SUM(CASE WHEN activity_logs.activity_type = "login" THEN 1 ELSE 0 END) as successful, SUM(CASE WHEN activity_logs.activity_type = "login_failed" THEN 1 ELSE 0 END) as failed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // การเข้าสู่ระบบล้มเหลว
        $failedLogins = ActivityLog::where('activity_logs.activity_type', 'login_failed')
            ->whereBetween('activity_logs.created_at', [$dateFrom, $dateTo])
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->selectRaw('COALESCE(users.email, "Unknown") as user_email, activity_logs.ip_address, COUNT(*) as attempts, MAX(activity_logs.created_at) as latest_attempt')
            ->groupBy('users.email', 'activity_logs.ip_address')
            ->orderBy('attempts', 'desc')
            ->limit(10)
            ->get();

        // กิจกรรมน่าสงสัย
        $suspiciousActivities = ActivityLog::where('is_suspicious', true)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // IP ที่มีความเสี่ยง
        $riskIPs = ActivityLog::whereBetween('activity_logs.created_at', [$dateFrom, $dateTo])
            ->selectRaw('activity_logs.ip_address, SUM(CASE WHEN activity_logs.activity_type = "login_failed" THEN 1 ELSE 0 END) as failed_attempts, SUM(CASE WHEN activity_logs.is_suspicious = 1 THEN 1 ELSE 0 END) as suspicious_count, MAX(activity_logs.created_at) as last_activity')
            ->groupBy('activity_logs.ip_address')
            ->having(DB::raw('failed_attempts + suspicious_count'), '>', 0)
            ->orderBy(DB::raw('failed_attempts + suspicious_count'), 'desc')
            ->limit(10)
            ->get();

        return view('admin.reports.security', compact(
            'securityStats',
            'securityScore',
            'dailyLogins',
            'failedLogins',
            'suspiciousActivities',
            'riskIPs',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Calculate security score
     */
    private function calculateSecurityScore($securityStats, $totalUsers)
    {
        $score = 0;
        
        // 2FA adoption (40% of score)
        if ($totalUsers > 0) {
            $score += ($securityStats['2fa_enabled'] / $totalUsers) * 40;
        }
        
        // Login success rate (30% of score)
        $totalLogins = $securityStats['successful_logins'] + $securityStats['failed_logins'];
        if ($totalLogins > 0) {
            $successRate = ($securityStats['successful_logins'] / $totalLogins) * 100;
            $score += ($successRate / 100) * 30;
        } else {
            $score += 30; // No failed logins is good
        }
        
        // Suspicious activity level (30% of score)
        if ($securityStats['suspicious_activities'] == 0) {
            $score += 30;
        } elseif ($securityStats['suspicious_activities'] < 5) {
            $score += 20;
        } elseif ($securityStats['suspicious_activities'] < 10) {
            $score += 10;
        }
        // else 0 points for high suspicious activity
        
        return min(100, max(0, $score));
    }

    /**
     * Export reports to CSV
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'users');
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        switch ($type) {
            case 'users':
                return $this->exportUsers($dateFrom, $dateTo);
            case 'activities':
                return $this->exportActivities($dateFrom, $dateTo);
            case 'security':
                return $this->exportSecurity($dateFrom, $dateTo);
            default:
                return redirect()->back()->with('error', 'ประเภทรายงานไม่ถูกต้อง');
        }
    }

    /**
     * Get registration chart data
     */
    private function getRegistrationChartData(int $days)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = User::where('role', 'user')
                ->whereDate('created_at', $date)
                ->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'count' => $count,
                'full_date' => $date->format('Y-m-d'),
            ];
        }
        return $data;
    }

    /**
     * Get activity chart data
     */
    private function getActivityChartData(int $days)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = ActivityLog::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'count' => $count,
                'full_date' => $date->format('Y-m-d'),
            ];
        }
        return $data;
    }

    /**
     * Get hourly activity statistics
     */
    private function getHourlyActivityStats()
    {
        return ActivityLog::whereDate('created_at', today())
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour');
    }

    /**
     * Export users data
     */
    private function exportUsers($dateFrom, $dateTo)
    {
        $users = User::where('role', 'user')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=users_report_' . date('Y-m-d') . '.csv',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Status', 'Created Date', 'Last Login']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->status,
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export activities data
     */
    private function exportActivities($dateFrom, $dateTo)
    {
        $activities = ActivityLog::with('user')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=activities_report_' . date('Y-m-d') . '.csv',
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User', 'Activity Type', 'Description', 'IP Address', 'Is Suspicious', 'Created Date']);

            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->user ? $activity->user->name : 'System',
                    $activity->activity_type,
                    $activity->description,
                    $activity->ip_address,
                    $activity->is_suspicious ? 'Yes' : 'No',
                    $activity->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export security data
     */
    private function exportSecurity($dateFrom, $dateTo)
    {
        $securityData = ActivityLog::where('is_suspicious', true)
            ->orWhere('activity_type', 'login_failed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('user')
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=security_report_' . date('Y-m-d') . '.csv',
        ];

        $callback = function() use ($securityData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User', 'Activity Type', 'Description', 'IP Address', 'Is Suspicious', 'Created Date']);

            foreach ($securityData as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->user ? $activity->user->name : 'System',
                    $activity->activity_type,
                    $activity->description,
                    $activity->ip_address,
                    $activity->is_suspicious ? 'Yes' : 'No',
                    $activity->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
