<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalAuditLog;
use App\Models\RegistrationApproval;
use App\Services\ApprovalAuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuditController extends Controller
{
    protected ApprovalAuditService $auditService;

    public function __construct(ApprovalAuditService $auditService)
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
        $this->auditService = $auditService;
    }

    /**
     * Display audit logs listing
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';
        
        // Build query with filters
        $query = ApprovalAuditLog::with(['user', 'registrationApproval.user', 'overriddenUser'])
                                ->orderBy('performed_at', 'desc');

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        // Filter by override status
        if ($request->filled('is_override')) {
            $query->where('is_override', $request->boolean('is_override'));
        }

        // For regular admins, only show logs for approvals they can see
        if (!$isSuperAdmin) {
            $query->whereHas('registrationApproval', function($q) use ($currentUser) {
                $q->where('reviewed_by', $currentUser->id)
                  ->orWhere('user_id', $currentUser->id);
            });
        }

        $auditLogs = $query->paginate(20);

        // Get statistics for the current filter
        $stats = $this->getFilteredStatistics($request);

        // Get available actions for filter dropdown
        $actions = ApprovalAuditLog::distinct('action')->pluck('action')->sort();

        // Get available users for filter dropdown (admins and super admins only)
        $users = \App\Models\User::whereIn('role', ['admin', 'super_admin'])
                                ->select('id', 'first_name', 'last_name', 'role')
                                ->orderBy('first_name')
                                ->get();

        return view('admin.audit.index', compact(
            'auditLogs', 
            'stats', 
            'actions', 
            'users', 
            'isSuperAdmin'
        ));
    }

    /**
     * Display specific audit log details
     */
    public function show(ApprovalAuditLog $auditLog): View
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';

        // Check permissions for regular admins
        if (!$isSuperAdmin) {
            $approval = $auditLog->registrationApproval;
            if ($approval->reviewed_by !== $currentUser->id && $approval->user_id !== $currentUser->id) {
                abort(403, 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้');
            }
        }

        $auditLog->load(['user', 'registrationApproval.user', 'overriddenUser']);

        // Get related audit logs for context
        $relatedLogs = ApprovalAuditLog::where('registration_approval_id', $auditLog->registration_approval_id)
                                     ->where('id', '!=', $auditLog->id)
                                     ->with(['user', 'overriddenUser'])
                                     ->orderBy('performed_at', 'desc')
                                     ->limit(10)
                                     ->get();

        return view('admin.audit.show', compact('auditLog', 'relatedLogs', 'isSuperAdmin'));
    }

    /**
     * Get audit statistics dashboard
     */
    public function statistics(Request $request): View
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';

        // Only super admins can access full statistics
        if (!$isSuperAdmin) {
            abort(403, 'เฉพาะ Super Admin เท่านั้นที่สามารถเข้าถึงสถิติทั้งหมดได้');
        }

        $days = $request->input('days', 30);
        $rawStats = $this->auditService->getAuditStats($days);

        // Transform stats for the view
        $stats = [
            'total_logs' => $rawStats['total_actions'] ?? 0,
            'total_overrides' => $rawStats['total_overrides'] ?? 0,
            'total_approvals' => $this->getActionCount('approved', $days),
            'total_rejections' => $this->getActionCount('rejected', $days),
            'unique_users' => $this->getUniqueUsersCount($days),
            'avg_daily_actions' => $this->getAvgDailyActions($days),
            'approval_rate' => $this->getApprovalRate($days),
            'peak_activity_hour' => $this->getPeakActivityHour($days)
        ];

        // Get trend data for charts
        $trendData = $this->getAuditTrendData($days);
        
        // Get action distribution
        $actionDistribution = $this->getActionDistribution($days);
        
        // Get user activity rankings
        $userActivity = $this->getUserActivityRankings($days);
        
        // Get override analysis
        $overrideAnalysis = $this->getOverrideAnalysis($days);

        return view('admin.audit.statistics', compact(
            'stats',
            'trendData',
            'actionDistribution', 
            'userActivity',
            'overrideAnalysis',
            'days'
        ));
    }

    /**
     * Export audit logs
     */
    public function export(Request $request): Response
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';

        // Build the same query as index
        $query = ApprovalAuditLog::with(['user', 'registrationApproval.user', 'overriddenUser'])
                                ->orderBy('performed_at', 'desc');

        // Apply same filters as index method
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        if ($request->filled('is_override')) {
            $query->where('is_override', $request->boolean('is_override'));
        }

        // Permission filtering for regular admins
        if (!$isSuperAdmin) {
            $query->whereHas('registrationApproval', function($q) use ($currentUser) {
                $q->where('reviewed_by', $currentUser->id)
                  ->orWhere('user_id', $currentUser->id);
            });
        }

        $auditLogs = $query->get();

        // Generate CSV content
        $csvContent = $this->generateCsvContent($auditLogs);

        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get filtered statistics based on current request
     */
    protected function getFilteredStatistics(Request $request): array
    {
        $query = ApprovalAuditLog::query();

        // Apply same filters as main query
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        if ($request->filled('is_override')) {
            $query->where('is_override', $request->boolean('is_override'));
        }

        return [
            'total_logs' => $query->count(),
            'total_overrides' => $query->where('is_override', true)->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'unique_approvals' => $query->distinct('registration_approval_id')->count('registration_approval_id'),
            'actions_today' => $query->whereDate('performed_at', today())->count(),
            'actions_this_week' => $query->whereBetween('performed_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];
    }

    /**
     * Get audit trend data for charts
     */
    protected function getAuditTrendData(int $days): array
    {
        $data = ApprovalAuditLog::selectRaw('DATE(performed_at) as date, COUNT(*) as count')
                               ->where('performed_at', '>=', now()->subDays($days))
                               ->groupBy('date')
                               ->orderBy('date')
                               ->get();

        $labels = [];
        $values = [];

        // Fill in missing dates with zero values
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            
            $dayData = $data->where('date', $date)->first();
            $values[] = $dayData ? $dayData->count : 0;
        }

        return compact('labels', 'values');
    }

    /**
     * Get action distribution data
     */
    protected function getActionDistribution(int $days): array
    {
        return ApprovalAuditLog::selectRaw('action, COUNT(*) as count')
                              ->where('performed_at', '>=', now()->subDays($days))
                              ->groupBy('action')
                              ->orderByDesc('count')
                              ->get()
                              ->pluck('count', 'action')
                              ->toArray();
    }

    /**
     * Get user activity rankings
     */
    protected function getUserActivityRankings(int $days): array
    {
        return ApprovalAuditLog::with('user:id,first_name,last_name,role')
                              ->selectRaw('user_id, COUNT(*) as total_actions, COUNT(CASE WHEN is_override = 1 THEN 1 END) as override_count')
                              ->where('performed_at', '>=', now()->subDays($days))
                              ->groupBy('user_id')
                              ->orderByDesc('total_actions')
                              ->limit(10)
                              ->get()
                              ->toArray();
    }

    /**
     * Get override analysis
     */
    protected function getOverrideAnalysis(int $days): array
    {
        $overrides = ApprovalAuditLog::with(['user:id,first_name,last_name,role', 'overriddenUser:id,first_name,last_name,role'])
                                    ->where('is_override', true)
                                    ->where('performed_at', '>=', now()->subDays($days))
                                    ->get();

        return [
            'total_overrides' => $overrides->count(),
            'override_by_action' => $overrides->groupBy('action')->map->count(),
            'override_by_user' => $overrides->groupBy('user_id')->map->count(),
            'recent_overrides' => $overrides->take(5)->toArray(),
        ];
    }

    /**
     * Generate CSV content for export
     */
    protected function generateCsvContent($auditLogs): string
    {
        $csvData = [];
        
        // CSV Headers
        $csvData[] = [
            'ID',
            'วันที่/เวลา',
            'การกระทำ',
            'ผู้ทำ',
            'บทบาท',
            'คำขอ ID',
            'ผู้สมัคร',
            'สถานะเก่า',
            'สถานะใหม่',
            'เหตุผล',
            'Override',
            'ผู้ถูก Override',
            'IP Address',
        ];

        // Data rows
        foreach ($auditLogs as $log) {
            $csvData[] = [
                $log->id,
                $log->performed_at->format('Y-m-d H:i:s'),
                $log->action_description,
                $log->user ? $log->user->first_name . ' ' . $log->user->last_name : 'N/A',
                $log->user ? ucfirst($log->user->role) : 'N/A',
                $log->registration_approval_id,
                $log->registrationApproval && $log->registrationApproval->user 
                    ? $log->registrationApproval->user->first_name . ' ' . $log->registrationApproval->user->last_name 
                    : 'N/A',
                $log->old_status ?? 'N/A',
                $log->new_status ?? 'N/A',
                $log->reason ?? 'N/A',
                $log->is_override ? 'Yes' : 'No',
                $log->overriddenUser ? $log->overriddenUser->first_name . ' ' . $log->overriddenUser->last_name : 'N/A',
                $log->metadata['ip_address'] ?? 'N/A',
            ];
        }

        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Get count of specific action type
     */
    private function getActionCount(string $action, int $days): int
    {
        $since = Carbon::now()->subDays($days);
        
        return ApprovalAuditLog::where('performed_at', '>=', $since)
                             ->where('action', 'like', '%' . $action . '%')
                             ->count();
    }

    /**
     * Get unique users count
     */
    private function getUniqueUsersCount(int $days): int
    {
        $since = Carbon::now()->subDays($days);
        
        return ApprovalAuditLog::where('performed_at', '>=', $since)
                             ->distinct('user_id')
                             ->count('user_id');
    }

    /**
     * Get average daily actions
     */
    private function getAvgDailyActions(int $days): float
    {
        $since = Carbon::now()->subDays($days);
        
        $totalActions = ApprovalAuditLog::where('performed_at', '>=', $since)->count();
        
        return $days > 0 ? round($totalActions / $days, 1) : 0;
    }

    /**
     * Get approval rate percentage
     */
    private function getApprovalRate(int $days): int
    {
        $since = Carbon::now()->subDays($days);
        
        $totalActions = ApprovalAuditLog::where('performed_at', '>=', $since)
                                      ->whereIn('action', ['approved', 'rejected'])
                                      ->count();
        
        $approvals = ApprovalAuditLog::where('performed_at', '>=', $since)
                                   ->where('action', 'approved')
                                   ->count();
        
        return $totalActions > 0 ? round(($approvals / $totalActions) * 100) : 0;
    }

    /**
     * Get peak activity hour
     */
    private function getPeakActivityHour(int $days): string
    {
        $since = Carbon::now()->subDays($days);
        
        $hourlyActivity = ApprovalAuditLog::where('performed_at', '>=', $since)
                                        ->selectRaw('HOUR(performed_at) as hour, COUNT(*) as count')
                                        ->groupBy('hour')
                                        ->orderBy('count', 'desc')
                                        ->first();
        
        return $hourlyActivity ? sprintf('%02d:00', $hourlyActivity->hour) : 'N/A';
    }
}