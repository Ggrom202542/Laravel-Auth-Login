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

class OverrideController extends Controller
{
    protected ApprovalAuditService $auditService;

    public function __construct(ApprovalAuditService $auditService)
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
        $this->auditService = $auditService;
    }

    /**
     * Display override history listing
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';

        // Build query for override logs only
        $query = ApprovalAuditLog::with(['user', 'registrationApproval.user', 'overriddenUser'])
                                ->where('is_override', true)
                                ->orderBy('performed_at', 'desc');

        // Filter by override action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by who performed the override
        if ($request->filled('overridden_by')) {
            $query->where('user_id', $request->overridden_by);
        }

        // Filter by who was overridden
        if ($request->filled('original_reviewer')) {
            $query->where('overridden_by', $request->original_reviewer);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        // For regular admins, only show overrides where they were involved
        if (!$isSuperAdmin) {
            $query->where(function($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id)  // They performed the override
                  ->orWhere('overridden_by', $currentUser->id); // Their decision was overridden
            });
        }

        $overrideLogs = $query->paginate(20);

        // Get statistics
        $stats = $this->getOverrideStatistics($request, $isSuperAdmin);

        // Get available override actions for filter
        $overrideActions = ApprovalAuditLog::where('is_override', true)
                                          ->distinct('action')
                                          ->pluck('action')
                                          ->sort();

        // Get users who can perform overrides (super admins)
        $overrideUsers = \App\Models\User::where('role', 'super_admin')
                                        ->select('id', 'first_name', 'last_name')
                                        ->orderBy('first_name')
                                        ->get();

        // Get users whose decisions can be overridden (admins and super admins)
        $reviewers = \App\Models\User::whereIn('role', ['admin', 'super_admin'])
                                    ->select('id', 'first_name', 'last_name')
                                    ->orderBy('first_name')
                                    ->get();

        return view('admin.override.index', compact(
            'overrideLogs',
            'stats',
            'overrideActions',
            'overrideUsers',
            'reviewers',
            'isSuperAdmin'
        ));
    }

    /**
     * Display specific override details
     */
    public function show(ApprovalAuditLog $overrideLog): View
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';

        // Verify this is actually an override log
        if (!$overrideLog->is_override) {
            abort(404, 'ไม่พบรายการ Override ที่ระบุ');
        }

        // Check permissions for regular admins
        if (!$isSuperAdmin) {
            if ($overrideLog->user_id !== $currentUser->id && $overrideLog->overridden_by !== $currentUser->id) {
                abort(403, 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้');
            }
        }

        $overrideLog->load(['user', 'registrationApproval.user', 'overriddenUser']);

        // Get the complete timeline for this approval
        $timeline = ApprovalAuditLog::where('registration_approval_id', $overrideLog->registration_approval_id)
                                   ->with(['user', 'overriddenUser'])
                                   ->orderBy('performed_at', 'asc')
                                   ->get();

        // Get approval details
        $approval = $overrideLog->registrationApproval;

        // Get override impact analysis
        $impactAnalysis = $this->getOverrideImpactAnalysis($overrideLog);

        return view('admin.override.show', compact(
            'overrideLog',
            'timeline',
            'approval',
            'impactAnalysis',
            'isSuperAdmin'
        ));
    }

    /**
     * Generate override report
     */
    public function report(Request $request): View
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';

        // Only super admins can access full override reports
        if (!$isSuperAdmin) {
            abort(403, 'เฉพาะ Super Admin เท่านั้นที่สามารถเข้าถึงรายงาน Override ได้');
        }

        $days = $request->input('days', 30);

        // Get comprehensive override analysis
        $overrideAnalysis = $this->auditService->getRecentOverrides();
        
        // Get override trends
        $overrideTrends = $this->getOverrideTrends($days);
        
        // Get user override patterns
        $userPatterns = $this->getUserOverridePatterns($days);
        
        // Get override reasons analysis
        $reasonAnalysis = $this->getOverrideReasonAnalysis($days);
        
        // Get approval-override ratio
        $ratioAnalysis = $this->getApprovalOverrideRatio($days);
        
        // Get monthly comparison
        $monthlyComparison = $this->getMonthlyOverrideComparison();

        return view('admin.override.report', compact(
            'overrideAnalysis',
            'overrideTrends',
            'userPatterns',
            'reasonAnalysis',
            'ratioAnalysis',
            'monthlyComparison',
            'days'
        ));
    }

    /**
     * Export override data
     */
    public function export(Request $request): Response
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';

        // Build query for export
        $query = ApprovalAuditLog::with(['user', 'registrationApproval.user', 'overriddenUser'])
                                ->where('is_override', true)
                                ->orderBy('performed_at', 'desc');

        // Apply same filters as index
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('overridden_by')) {
            $query->where('user_id', $request->overridden_by);
        }

        if ($request->filled('original_reviewer')) {
            $query->where('overridden_by', $request->original_reviewer);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        // Permission filtering for regular admins
        if (!$isSuperAdmin) {
            $query->where(function($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id)
                  ->orWhere('overridden_by', $currentUser->id);
            });
        }

        $overrideLogs = $query->get();

        // Generate CSV content
        $csvContent = $this->generateOverrideCsvContent($overrideLogs);

        $filename = 'override_history_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get override statistics
     */
    protected function getOverrideStatistics(Request $request, bool $isSuperAdmin): array
    {
        $query = ApprovalAuditLog::where('is_override', true);

        // Apply same filters as main query
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('overridden_by')) {
            $query->where('user_id', $request->overridden_by);
        }

        if ($request->filled('original_reviewer')) {
            $query->where('overridden_by', $request->original_reviewer);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        if (!$isSuperAdmin) {
            $currentUser = Auth::user();
            $query->where(function($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id)
                  ->orWhere('overridden_by', $currentUser->id);
            });
        }

        $baseQuery = clone $query;

        return [
            'total_overrides' => $baseQuery->count(),
            'overrides_today' => (clone $query)->whereDate('performed_at', today())->count(),
            'overrides_this_week' => (clone $query)->whereBetween('performed_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'overrides_this_month' => (clone $query)->whereMonth('performed_at', now()->month)
                                                   ->whereYear('performed_at', now()->year)
                                                   ->count(),
            'unique_overriders' => (clone $query)->distinct('user_id')->count('user_id'),
            'unique_overridden' => (clone $query)->whereNotNull('overridden_by')
                                                 ->distinct('overridden_by')
                                                 ->count('overridden_by'),
            'approved_overrides' => (clone $query)->where('action', 'override_approved')->count(),
            'rejected_overrides' => (clone $query)->where('action', 'override_rejected')->count(),
        ];
    }

    /**
     * Get override trends for charts
     */
    protected function getOverrideTrends(int $days): array
    {
        $data = ApprovalAuditLog::selectRaw('DATE(performed_at) as date, COUNT(*) as count')
                               ->where('is_override', true)
                               ->where('performed_at', '>=', now()->subDays($days))
                               ->groupBy('date')
                               ->orderBy('date')
                               ->get();

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            
            $dayData = $data->where('date', $date)->first();
            $values[] = $dayData ? $dayData->count : 0;
        }

        return compact('labels', 'values');
    }

    /**
     * Get user override patterns
     */
    protected function getUserOverridePatterns(int $days): array
    {
        $overriders = ApprovalAuditLog::with('user:id,first_name,last_name,role')
                                     ->selectRaw('user_id, COUNT(*) as override_count')
                                     ->where('is_override', true)
                                     ->where('performed_at', '>=', now()->subDays($days))
                                     ->groupBy('user_id')
                                     ->orderByDesc('override_count')
                                     ->get();

        $overridden = ApprovalAuditLog::with('overriddenUser:id,first_name,last_name,role')
                                     ->selectRaw('overridden_by, COUNT(*) as overridden_count')
                                     ->where('is_override', true)
                                     ->whereNotNull('overridden_by')
                                     ->where('performed_at', '>=', now()->subDays($days))
                                     ->groupBy('overridden_by')
                                     ->orderByDesc('overridden_count')
                                     ->get();

        return [
            'top_overriders' => $overriders,
            'most_overridden' => $overridden,
        ];
    }

    /**
     * Get override reason analysis
     */
    protected function getOverrideReasonAnalysis(int $days): array
    {
        $reasons = ApprovalAuditLog::selectRaw('reason, COUNT(*) as count')
                                  ->where('is_override', true)
                                  ->whereNotNull('reason')
                                  ->where('performed_at', '>=', now()->subDays($days))
                                  ->groupBy('reason')
                                  ->orderByDesc('count')
                                  ->get();

        $noReasonCount = ApprovalAuditLog::where('is_override', true)
                                        ->whereNull('reason')
                                        ->where('performed_at', '>=', now()->subDays($days))
                                        ->count();

        return [
            'reasons_with_count' => $reasons,
            'no_reason_count' => $noReasonCount,
            'total_overrides' => $reasons->sum('count') + $noReasonCount,
        ];
    }

    /**
     * Get approval to override ratio
     */
    protected function getApprovalOverrideRatio(int $days): array
    {
        $since = now()->subDays($days);

        $totalApprovals = ApprovalAuditLog::whereIn('action', ['approved', 'rejected'])
                                         ->where('performed_at', '>=', $since)
                                         ->count();

        $totalOverrides = ApprovalAuditLog::where('is_override', true)
                                         ->where('performed_at', '>=', $since)
                                         ->count();

        $overrideRate = $totalApprovals > 0 ? ($totalOverrides / $totalApprovals) * 100 : 0;

        return [
            'total_approvals' => $totalApprovals,
            'total_overrides' => $totalOverrides,
            'override_rate_percentage' => round($overrideRate, 2),
        ];
    }

    /**
     * Get monthly override comparison
     */
    protected function getMonthlyOverrideComparison(): array
    {
        $currentMonth = ApprovalAuditLog::where('is_override', true)
                                       ->whereMonth('performed_at', now()->month)
                                       ->whereYear('performed_at', now()->year)
                                       ->count();

        $lastMonth = ApprovalAuditLog::where('is_override', true)
                                    ->whereMonth('performed_at', now()->subMonth()->month)
                                    ->whereYear('performed_at', now()->subMonth()->year)
                                    ->count();

        $percentChange = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return [
            'current_month' => $currentMonth,
            'last_month' => $lastMonth,
            'percent_change' => round($percentChange, 1),
            'trend' => $percentChange > 0 ? 'increase' : ($percentChange < 0 ? 'decrease' : 'stable'),
        ];
    }

    /**
     * Get override impact analysis
     */
    protected function getOverrideImpactAnalysis(ApprovalAuditLog $overrideLog): array
    {
        $approval = $overrideLog->registrationApproval;

        // Get time between original decision and override
        $originalDecision = ApprovalAuditLog::where('registration_approval_id', $approval->id)
                                           ->whereIn('action', ['approved', 'rejected'])
                                           ->where('is_override', false)
                                           ->where('performed_at', '<', $overrideLog->performed_at)
                                           ->orderBy('performed_at', 'desc')
                                           ->first();

        $timeToOverride = null;
        if ($originalDecision) {
            $timeToOverride = $originalDecision->performed_at->diffInHours($overrideLog->performed_at);
        }

        // Check if user was eventually activated
        $userStatus = $approval->user->status ?? 'unknown';
        $userApprovalStatus = $approval->user->approval_status ?? 'unknown';

        return [
            'time_to_override_hours' => $timeToOverride,
            'original_decision' => $originalDecision,
            'final_user_status' => $userStatus,
            'final_approval_status' => $userApprovalStatus,
            'had_reason' => !empty($overrideLog->reason),
            'metadata' => $overrideLog->metadata,
        ];
    }

    /**
     * Generate CSV content for override export
     */
    protected function generateOverrideCsvContent($overrideLogs): string
    {
        $csvData = [];
        
        // CSV Headers
        $csvData[] = [
            'ID',
            'วันที่/เวลา Override',
            'การกระทำ',
            'ผู้ Override',
            'บทบาทผู้ Override',
            'ผู้ถูก Override',
            'บทบาทผู้ถูก Override',
            'คำขอ ID',
            'ผู้สมัคร',
            'สถานะเก่า',
            'สถานะใหม่',
            'เหตุผล Override',
            'ความเห็นเพิ่มเติม',
            'IP Address',
            'Session ID',
        ];

        // Data rows
        foreach ($overrideLogs as $log) {
            $csvData[] = [
                $log->id,
                $log->performed_at->format('Y-m-d H:i:s'),
                $log->action_description,
                $log->user ? $log->user->first_name . ' ' . $log->user->last_name : 'N/A',
                $log->user ? ucfirst($log->user->role) : 'N/A',
                $log->overriddenUser ? $log->overriddenUser->first_name . ' ' . $log->overriddenUser->last_name : 'N/A',
                $log->overriddenUser ? ucfirst($log->overriddenUser->role) : 'N/A',
                $log->registration_approval_id,
                $log->registrationApproval && $log->registrationApproval->user 
                    ? $log->registrationApproval->user->first_name . ' ' . $log->registrationApproval->user->last_name 
                    : 'N/A',
                $log->old_status ?? 'N/A',
                $log->new_status ?? 'N/A',
                $log->reason ?? 'ไม่ระบุเหตุผล',
                $log->comments ?? 'N/A',
                $log->metadata['ip_address'] ?? 'N/A',
                $log->metadata['session_id'] ?? 'N/A',
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
}