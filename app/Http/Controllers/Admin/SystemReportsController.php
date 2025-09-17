<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserSession;
use App\Models\AdminSession;
use App\Models\SecurityLog;
use App\Models\LoginAttempt;
use App\Models\IpRestriction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * หน้าหลักรายงานระบบ
     */
    public function index()
    {
        // สถิติภาพรวมระบบ
        $systemStats = $this->getSystemOverview();
        
        // ข้อมูลการใช้งานรายเดือน
        $monthlyUsage = $this->getMonthlyUsageData();
        
        // สถิติความปลอดภัย
        $securityStats = $this->getSecurityStats();
        
        // Performance metrics
        $performanceStats = $this->getPerformanceStats();

        return view('admin.super-admin.reports.index', compact(
            'systemStats',
            'monthlyUsage', 
            'securityStats',
            'performanceStats'
        ));
    }

    /**
     * รายงานผู้ใช้งาน
     */
    public function users(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        // สถิติผู้ใช้
        $userStats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'admin_users' => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'locked_users' => User::where('locked_until', '>', now())->count(),
        ];

        // การลงทะเบียนรายวัน
        $registrationData = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // การแจกแจงตามบทบาท
        $roleDistribution = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get();

        // ผู้ใช้ที่ใช้งานมากที่สุด
        $topUsers = UserSession::selectRaw('user_id, COUNT(*) as session_count')
            ->with('user:id,username,email,role')
            ->where('created_at', '>=', $startDate)
            ->groupBy('user_id')
            ->orderByDesc('session_count')
            ->limit(10)
            ->get();

        return view('admin.super-admin.reports.users', compact(
            'userStats',
            'registrationData',
            'roleDistribution',
            'topUsers',
            'period'
        ));
    }

    /**
     * รายงาน Sessions
     */
    public function sessions(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        // สถิติ Sessions
        $sessionStats = [
            'total_sessions' => UserSession::count(),
            'active_sessions' => UserSession::where('is_active', true)->count(),
            'new_sessions' => UserSession::where('created_at', '>=', $startDate)->count(),
            'unique_users' => UserSession::where('created_at', '>=', $startDate)->distinct('user_id')->count('user_id'),
            'average_duration' => $this->getAverageSessionDuration($startDate),
        ];

        // Sessions รายวัน
        $dailySessions = UserSession::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // การแจกแจงตามอุปกรณ์
        $deviceDistribution = UserSession::selectRaw('device_type, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('device_type')
            ->get();

        // การแจกแจงตามแพลตฟอร์ม
        $platformDistribution = UserSession::selectRaw('platform, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return view('admin.super-admin.reports.sessions', compact(
            'sessionStats',
            'dailySessions', 
            'deviceDistribution',
            'platformDistribution',
            'period'
        ));
    }

    /**
     * รายงานความปลอดภัย
     */
    public function security(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        // สถิติความปลอดภัย
        $securityStats = [
            'total_login_attempts' => LoginAttempt::where('attempted_at', '>=', $startDate)->count(),
            'failed_attempts' => LoginAttempt::where('attempted_at', '>=', $startDate)->where('status', 'failed')->count(),
            'suspicious_attempts' => LoginAttempt::where('attempted_at', '>=', $startDate)->where('is_suspicious', true)->count(),
            'blocked_ips' => IpRestriction::where('type', 'blacklist')->where('is_active', true)->count(),
            'security_incidents' => SecurityLog::where('created_at', '>=', $startDate)->count(),
        ];

        // การพยายามเข้าสู่ระบบรายวัน
        $dailyAttempts = LoginAttempt::selectRaw('DATE(attempted_at) as date, COUNT(*) as total, SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            ->where('attempted_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // IP ที่น่าสงสัย
        $suspiciousIPs = LoginAttempt::selectRaw('ip_address, COUNT(*) as attempts, SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_attempts')
            ->where('attempted_at', '>=', $startDate)
            ->groupBy('ip_address')
            ->havingRaw('failed_attempts >= 5')
            ->orderByDesc('failed_attempts')
            ->limit(10)
            ->get();

        // Security logs รายประเภท
        $securityLogTypes = SecurityLog::selectRaw('type, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        return view('admin.super-admin.reports.security', compact(
            'securityStats',
            'dailyAttempts',
            'suspiciousIPs',
            'securityLogTypes',
            'period'
        ));
    }

    /**
     * รายงาน Performance
     */
    public function performance(Request $request)
    {
        $period = $request->get('period', '7'); // days for performance
        $startDate = now()->subDays($period);

        // Performance metrics
        $performanceStats = [
            'avg_response_time' => $this->getAverageResponseTime($startDate),
            'peak_concurrent_users' => $this->getPeakConcurrentUsers($startDate),
            'system_uptime' => $this->getSystemUptime(),
            'error_rate' => $this->getErrorRate($startDate),
            'memory_usage' => $this->getMemoryUsage(),
        ];

        // การใช้งานทรัพยากรรายชั่วโมง
        $hourlyUsage = $this->getHourlyUsageData($startDate);

        // Top slow queries (จำลอง)
        $slowQueries = $this->getSlowQueries();

        return view('admin.super-admin.reports.performance', compact(
            'performanceStats',
            'hourlyUsage',
            'slowQueries', 
            'period'
        ));
    }

    /**
     * Export รายงาน
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'users');
        $format = $request->get('format', 'csv');
        $period = $request->get('period', '30');

        switch ($type) {
            case 'users':
                return $this->exportUsersReport($format, $period);
            case 'sessions':
                return $this->exportSessionsReport($format, $period);
            case 'security':
                return $this->exportSecurityReport($format, $period);
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    // Private helper methods

    private function getSystemOverview()
    {
        return [
            'total_users' => User::count(),
            'active_sessions' => UserSession::where('is_active', true)->count(),
            'security_incidents_today' => SecurityLog::whereDate('created_at', today())->count(),
            'system_health' => 'Good', // จำลอง
            'uptime_percentage' => 99.9, // จำลอง
        ];
    }

    private function getMonthlyUsageData()
    {
        return UserSession::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as sessions')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function getSecurityStats()
    {
        $today = today();
        return [
            'failed_logins_today' => LoginAttempt::whereDate('attempted_at', $today)->where('status', 'failed')->count(),
            'blocked_ips' => IpRestriction::where('type', 'blacklist')->where('is_active', true)->count(),
            'security_alerts' => SecurityLog::whereDate('created_at', $today)->count(),
        ];
    }

    private function getPerformanceStats()
    {
        return [
            'avg_response_time' => '120ms', // จำลอง
            'peak_users' => UserSession::where('created_at', '>=', today())->distinct('user_id')->count('user_id'),
            'error_rate' => '0.1%', // จำลอง
        ];
    }

    private function getAverageSessionDuration($startDate)
    {
        $avgMinutes = UserSession::where('created_at', '>=', $startDate)
            ->whereNotNull('logout_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, login_at, logout_at)) as avg_duration')
            ->value('avg_duration');

        return $avgMinutes ? round($avgMinutes) . ' นาที' : 'N/A';
    }

    private function getAverageResponseTime($startDate)
    {
        // จำลองข้อมูล response time
        return rand(80, 200) . 'ms';
    }

    private function getPeakConcurrentUsers($startDate)
    {
        return UserSession::where('created_at', '>=', $startDate)
            ->where('is_active', true)
            ->distinct('user_id')
            ->count('user_id');
    }

    private function getSystemUptime()
    {
        // จำลองข้อมูล uptime
        return '99.9%';
    }

    private function getErrorRate($startDate)
    {
        // จำลองข้อมูล error rate
        return '0.1%';
    }

    private function getMemoryUsage()
    {
        // จำลองข้อมูล memory usage
        return round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
    }

    private function getHourlyUsageData($startDate)
    {
        return UserSession::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    private function getSlowQueries()
    {
        // จำลองข้อมูล slow queries
        return collect([
            ['query' => 'SELECT * FROM users WHERE role = "admin"', 'time' => '2.3s', 'count' => 15],
            ['query' => 'SELECT * FROM user_sessions ORDER BY created_at DESC', 'time' => '1.8s', 'count' => 8],
            ['query' => 'SELECT * FROM login_attempts WHERE is_suspicious = 1', 'time' => '1.2s', 'count' => 5],
        ]);
    }

    private function exportUsersReport($format, $period)
    {
        // Implementation for exporting users report
        return response()->json(['message' => 'Export feature will be implemented']);
    }

    private function exportSessionsReport($format, $period)
    {
        // Implementation for exporting sessions report  
        return response()->json(['message' => 'Export feature will be implemented']);
    }

    private function exportSecurityReport($format, $period)
    {
        // Implementation for exporting security report
        return response()->json(['message' => 'Export feature will be implemented']);
    }
}