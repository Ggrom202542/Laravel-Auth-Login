<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationApproval;
use App\Services\ApprovalAuditService;
use App\Services\ApprovalNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected ApprovalAuditService $auditService;
    protected ApprovalNotificationService $notificationService;

    public function __construct(
        ApprovalAuditService $auditService,
        ApprovalNotificationService $notificationService
    ) {
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';
        
        // Approval statistics
        $approvalStats = $this->getApprovalStatistics($currentUser);
        
        // Recent approval activities
        $recentApprovals = $this->getRecentApprovals($currentUser, 10);
        
        // Audit statistics (สำหรับ Super Admin)
        $auditStats = $isSuperAdmin ? $this->auditService->getAuditStats(30) : null;
        
        // Notification statistics
        $notificationStats = $this->notificationService->getNotificationStats($currentUser->id);
        
        // Recent notifications
        $recentNotifications = $this->notificationService->getRecentNotifications($currentUser->id, 5);
        
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

        // ข้อมูลกราฟ
        $registrationData = $this->getRegistrationChartData();
        $approvalTrendData = $this->getApprovalTrendData();

        return view('admin.dashboard', compact(
            'userStats',
            'todayStats', 
            'recentActivities',
            'recentUsers',
            'registrationData',
            'approvalStats',
            'recentApprovals',
            'auditStats',
            'notificationStats',
            'recentNotifications',
            'approvalTrendData',
            'isSuperAdmin'
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

    /**
     * Get approval statistics based on user role
     */
    private function getApprovalStatistics($currentUser): array
    {
        $baseQuery = RegistrationApproval::query();
        
        // สำหรับ Admin: เฉพาะที่ตนเองดูแล + pending ที่ยังไม่มีคนดูแล
        if ($currentUser->role === 'admin' && !config('approval.admin.can_see_all_approvals', false)) {
            $baseQuery->where(function ($q) use ($currentUser) {
                $q->where('reviewed_by', null)
                  ->orWhere('reviewed_by', $currentUser->id);
            });
        }
        
        $escalationDays = config('approval.workflow.escalation_days', 3);
        
        return [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved_today' => (clone $baseQuery)->where('status', 'approved')->whereDate('reviewed_at', today())->count(),
            'rejected_today' => (clone $baseQuery)->where('status', 'rejected')->whereDate('reviewed_at', today())->count(),
            'my_approvals' => RegistrationApproval::where('reviewed_by', $currentUser->id)->count(),
            'escalated' => $currentUser->role === 'super_admin' ? 
                RegistrationApproval::where('status', 'pending')
                    ->where('created_at', '<=', now()->subDays($escalationDays))
                    ->count() : 0,
            'avg_processing_time' => $this->getAverageProcessingTime($currentUser),
            'approval_rate' => $this->getApprovalRate($currentUser),
        ];
    }

    /**
     * Get recent approvals
     */
    private function getRecentApprovals($currentUser, int $limit = 10)
    {
        $query = RegistrationApproval::with(['user', 'reviewer'])
            ->orderBy('reviewed_at', 'desc')
            ->whereNotNull('reviewed_at');

        // สำหรับ Admin: เฉพาะที่ตนเองดูแล
        if ($currentUser->role === 'admin' && !config('approval.admin.can_see_all_approvals', false)) {
            $query->where('reviewed_by', $currentUser->id);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get approval trend data for charts
     */
    private function getApprovalTrendData(): array
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $approved = RegistrationApproval::whereDate('reviewed_at', $date)
                ->where('status', 'approved')
                ->count();
            $rejected = RegistrationApproval::whereDate('reviewed_at', $date)
                ->where('status', 'rejected')
                ->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'approved' => $approved,
                'rejected' => $rejected,
            ];
        }
        
        return $data;
    }

    /**
     * Calculate average processing time for approvals
     */
    private function getAverageProcessingTime($currentUser): string
    {
        $query = RegistrationApproval::whereNotNull('reviewed_at');
        
        if ($currentUser->role === 'admin') {
            $query->where('reviewed_by', $currentUser->id);
        }
        
        $approvals = $query->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as avg_hours')
            ->first();
        
        $avgHours = $approvals->avg_hours ?? 0;
        
        if ($avgHours < 1) {
            return '< 1 ชั่วโมง';
        } elseif ($avgHours < 24) {
            return round($avgHours, 1) . ' ชั่วโมง';
        } else {
            return round($avgHours / 24, 1) . ' วัน';
        }
    }

    /**
     * Calculate approval rate percentage
     */
    private function getApprovalRate($currentUser): float
    {
        $query = RegistrationApproval::whereNotNull('reviewed_at');
        
        if ($currentUser->role === 'admin') {
            $query->where('reviewed_by', $currentUser->id);
        }
        
        $total = (clone $query)->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        
        return $total > 0 ? round(($approved / $total) * 100, 1) : 0;
    }
}
