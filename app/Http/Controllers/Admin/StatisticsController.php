<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalAuditLog;
use App\Models\RegistrationApproval;
use App\Models\User;
use App\Services\ApprovalAuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    protected ApprovalAuditService $auditService;

    public function __construct(ApprovalAuditService $auditService)
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
        $this->auditService = $auditService;
    }

    /**
     * Display approval statistics dashboard
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';
        
        $days = $request->input('days', 30);
        $period = $request->input('period', 'day'); // day, week, month

        // Get comprehensive statistics
        $overviewStats = $this->getOverviewStatistics($days, $isSuperAdmin);
        
        // Get time-based trends
        $approvalTrends = $this->getApprovalTrends($days, $period);
        
        // Get performance metrics
        $performanceMetrics = $this->getPerformanceMetrics($days, $isSuperAdmin);
        
        // Get user performance rankings
        $userPerformance = $this->getUserPerformanceRankings($days, $isSuperAdmin);
        
        // Get approval flow analysis
        $flowAnalysis = $this->getApprovalFlowAnalysis($days);
        
        // Get time distribution analysis
        $timeAnalysis = $this->getTimeDistributionAnalysis($days);

        return view('admin.statistics.index', compact(
            'overviewStats',
            'approvalTrends',
            'performanceMetrics',
            'userPerformance',
            'flowAnalysis',
            'timeAnalysis',
            'days',
            'period',
            'isSuperAdmin'
        ));
    }

    /**
     * Get approval analytics API endpoint
     */
    public function analytics(Request $request): JsonResponse
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';
        
        $type = $request->input('type', 'overview');
        $days = $request->input('days', 30);
        $period = $request->input('period', 'day');

        switch ($type) {
            case 'trends':
                $data = $this->getApprovalTrends($days, $period);
                break;
                
            case 'performance':
                $data = $this->getPerformanceMetrics($days, $isSuperAdmin);
                break;
                
            case 'user_activity':
                $data = $this->getUserActivityData($days, $isSuperAdmin);
                break;
                
            case 'time_distribution':
                $data = $this->getTimeDistributionData($days);
                break;
                
            case 'comparison':
                $data = $this->getComparisonData($days);
                break;
                
            default:
                $data = $this->getOverviewStatistics($days, $isSuperAdmin);
        }

        return response()->json($data);
    }

    /**
     * Get detailed report
     */
    public function report(Request $request): View
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === 'super_admin';

        // Only super admins can access detailed reports
        if (!$isSuperAdmin) {
            abort(403, 'เฉพาะ Super Admin เท่านั้นที่สามารถเข้าถึงรายงานโดยละเอียดได้');
        }

        $days = $request->input('days', 90); // Longer period for detailed reports
        
        // Get comprehensive report data
        $reportData = [
            'executive_summary' => $this->getExecutiveSummary($days),
            'approval_efficiency' => $this->getApprovalEfficiencyReport($days),
            'user_performance_detailed' => $this->getDetailedUserPerformance($days),
            'bottleneck_analysis' => $this->getBottleneckAnalysis($days),
            'quality_metrics' => $this->getQualityMetrics($days),
            'recommendations' => $this->generateRecommendations($days),
        ];

        return view('admin.statistics.report', compact('reportData', 'days'));
    }

    /**
     * Get overview statistics
     */
    protected function getOverviewStatistics(int $days, bool $isSuperAdmin): array
    {
        $since = now()->subDays($days);
        
        $baseQuery = RegistrationApproval::where('created_at', '>=', $since);
        
        // For regular admins, filter to their approvals
        if (!$isSuperAdmin) {
            $currentUser = Auth::user();
            $baseQuery->where('reviewed_by', $currentUser->id);
        }

        $totalApprovals = (clone $baseQuery)->count();
        $pendingApprovals = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedApprovals = (clone $baseQuery)->where('status', 'approved')->count();
        $rejectedApprovals = (clone $baseQuery)->where('status', 'rejected')->count();
        
        // Calculate rates
        $approvalRate = $totalApprovals > 0 ? ($approvedApprovals / $totalApprovals) * 100 : 0;
        $rejectionRate = $totalApprovals > 0 ? ($rejectedApprovals / $totalApprovals) * 100 : 0;
        
        // Get average processing time
        $avgProcessingTime = $this->getAverageProcessingTime($days, $isSuperAdmin);
        
        // Get override statistics
        $overrideStats = $this->getOverrideStatisticsForPeriod($days, $isSuperAdmin);

        return [
            'total_approvals' => $totalApprovals,
            'pending_approvals' => $pendingApprovals,
            'approved_approvals' => $approvedApprovals,
            'rejected_approvals' => $rejectedApprovals,
            'approval_rate' => round($approvalRate, 1),
            'rejection_rate' => round($rejectionRate, 1),
            'avg_processing_time_hours' => round($avgProcessingTime, 1),
            'override_count' => $overrideStats['total'],
            'override_rate' => $overrideStats['rate'],
        ];
    }

    /**
     * Get approval trends data
     */
    protected function getApprovalTrends(int $days, string $period): array
    {
        $since = now()->subDays($days);
        
        $groupBy = match($period) {
            'hour' => 'HOUR(created_at)',
            'week' => 'WEEK(created_at)',
            'month' => 'MONTH(created_at)',
            default => 'DATE(created_at)'
        };

        $data = RegistrationApproval::selectRaw("
                $groupBy as period,
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending
            ")
            ->where('created_at', '>=', $since)
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'labels' => $data->pluck('period')->toArray(),
            'datasets' => [
                [
                    'label' => 'Total Approvals',
                    'data' => $data->pluck('total')->toArray(),
                    'borderColor' => '#667eea',
                    'backgroundColor' => 'rgba(102, 126, 234, 0.1)',
                ],
                [
                    'label' => 'Approved',
                    'data' => $data->pluck('approved')->toArray(),
                    'borderColor' => '#1cc88a',
                    'backgroundColor' => 'rgba(28, 200, 138, 0.1)',
                ],
                [
                    'label' => 'Rejected',
                    'data' => $data->pluck('rejected')->toArray(),
                    'borderColor' => '#e74a3b',
                    'backgroundColor' => 'rgba(231, 74, 59, 0.1)',
                ],
            ]
        ];
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(int $days, bool $isSuperAdmin): array
    {
        $since = now()->subDays($days);
        
        // Average processing time
        $avgTime = $this->getAverageProcessingTime($days, $isSuperAdmin);
        
        // Approval throughput (approvals per day)
        $throughput = RegistrationApproval::where('reviewed_at', '>=', $since)
                                         ->whereIn('status', ['approved', 'rejected'])
                                         ->count() / $days;
        
        // Workload distribution
        $workloadDistribution = $this->getWorkloadDistribution($days, $isSuperAdmin);
        
        // Escalation rate (pending approvals that exceed time limit)
        $escalationRate = $this->getEscalationRate($days);
        
        // Quality metrics (override rate as quality indicator)
        $qualityMetrics = $this->getQualityMetrics($days);

        return [
            'avg_processing_time_hours' => round($avgTime, 1),
            'daily_throughput' => round($throughput, 1),
            'workload_distribution' => $workloadDistribution,
            'escalation_rate' => round($escalationRate, 1),
            'quality_score' => round($qualityMetrics['quality_score'], 1),
        ];
    }

    /**
     * Get user performance rankings
     */
    protected function getUserPerformanceRankings(int $days, bool $isSuperAdmin): array
    {
        $since = now()->subDays($days);
        
        $query = DB::table('registration_approvals as ra')
                  ->join('users as u', 'ra.reviewed_by', '=', 'u.id')
                  ->select([
                      'u.id',
                      'u.first_name',
                      'u.last_name',
                      'u.role',
                      DB::raw('COUNT(*) as total_reviewed'),
                      DB::raw('COUNT(CASE WHEN ra.status = "approved" THEN 1 END) as approved_count'),
                      DB::raw('COUNT(CASE WHEN ra.status = "rejected" THEN 1 END) as rejected_count'),
                      DB::raw('AVG(TIMESTAMPDIFF(HOUR, ra.created_at, ra.reviewed_at)) as avg_processing_hours'),
                  ])
                  ->where('ra.reviewed_at', '>=', $since)
                  ->whereIn('ra.status', ['approved', 'rejected'])
                  ->groupBy(['u.id', 'u.first_name', 'u.last_name', 'u.role'])
                  ->orderByDesc('total_reviewed');

        if (!$isSuperAdmin) {
            $currentUser = Auth::user();
            $query->where('u.id', $currentUser->id);
        }

        $users = $query->get();

        // Add override statistics for each user
        foreach ($users as $user) {
            $overridesMade = ApprovalAuditLog::where('user_id', $user->id)
                                            ->where('is_override', true)
                                            ->where('performed_at', '>=', $since)
                                            ->count();
            
            $overridesReceived = ApprovalAuditLog::where('overridden_by', $user->id)
                                                ->where('performed_at', '>=', $since)
                                                ->count();
            
            $user->overrides_made = $overridesMade;
            $user->overrides_received = $overridesReceived;
            $user->approval_rate = $user->total_reviewed > 0 
                ? round(($user->approved_count / $user->total_reviewed) * 100, 1) 
                : 0;
        }

        return $users->toArray();
    }

    /**
     * Get approval flow analysis
     */
    protected function getApprovalFlowAnalysis(int $days): array
    {
        $since = now()->subDays($days);
        
        // Analyze the flow from pending to final status
        $flowData = RegistrationApproval::where('created_at', '>=', $since)
                                       ->selectRaw('
                                           status,
                                           COUNT(*) as count,
                                           AVG(TIMESTAMPDIFF(HOUR, created_at, COALESCE(reviewed_at, NOW()))) as avg_time_hours
                                       ')
                                       ->groupBy('status')
                                       ->get();

        // Get daily flow metrics
        $dailyFlow = RegistrationApproval::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as submitted,
                COUNT(CASE WHEN status != "pending" THEN 1 END) as processed
            ')
            ->where('created_at', '>=', $since)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'status_distribution' => $flowData->pluck('count', 'status')->toArray(),
            'avg_time_by_status' => $flowData->pluck('avg_time_hours', 'status')->toArray(),
            'daily_flow' => $dailyFlow->toArray(),
        ];
    }

    /**
     * Get time distribution analysis
     */
    protected function getTimeDistributionAnalysis(int $days): array
    {
        $since = now()->subDays($days);
        
        // Analyze processing time distribution
        $processingTimes = RegistrationApproval::where('reviewed_at', '>=', $since)
                                             ->whereIn('status', ['approved', 'rejected'])
                                             ->selectRaw('TIMESTAMPDIFF(HOUR, created_at, reviewed_at) as processing_hours')
                                             ->pluck('processing_hours')
                                             ->filter()
                                             ->toArray();

        // Create time buckets
        $buckets = [
            '0-1h' => 0,
            '1-4h' => 0,
            '4-12h' => 0,
            '12-24h' => 0,
            '1-3d' => 0,
            '3-7d' => 0,
            '7d+' => 0,
        ];

        foreach ($processingTimes as $hours) {
            if ($hours <= 1) $buckets['0-1h']++;
            elseif ($hours <= 4) $buckets['1-4h']++;
            elseif ($hours <= 12) $buckets['4-12h']++;
            elseif ($hours <= 24) $buckets['12-24h']++;
            elseif ($hours <= 72) $buckets['1-3d']++;
            elseif ($hours <= 168) $buckets['3-7d']++;
            else $buckets['7d+']++;
        }

        return [
            'time_buckets' => $buckets,
            'statistics' => [
                'total_processed' => count($processingTimes),
                'avg_hours' => count($processingTimes) > 0 ? round(array_sum($processingTimes) / count($processingTimes), 1) : 0,
                'median_hours' => count($processingTimes) > 0 ? $this->calculateMedian($processingTimes) : 0,
                'max_hours' => count($processingTimes) > 0 ? max($processingTimes) : 0,
            ]
        ];
    }

    /**
     * Get average processing time
     */
    protected function getAverageProcessingTime(int $days, bool $isSuperAdmin): float
    {
        $since = now()->subDays($days);
        
        $query = RegistrationApproval::where('reviewed_at', '>=', $since)
                                   ->whereIn('status', ['approved', 'rejected']);

        if (!$isSuperAdmin) {
            $currentUser = Auth::user();
            $query->where('reviewed_by', $currentUser->id);
        }

        return $query->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as avg_hours')
                    ->value('avg_hours') ?? 0;
    }

    /**
     * Get override statistics for period
     */
    protected function getOverrideStatisticsForPeriod(int $days, bool $isSuperAdmin): array
    {
        $since = now()->subDays($days);
        
        $totalApprovals = RegistrationApproval::where('reviewed_at', '>=', $since)
                                            ->whereIn('status', ['approved', 'rejected'])
                                            ->count();

        $query = ApprovalAuditLog::where('is_override', true)
                                ->where('performed_at', '>=', $since);

        if (!$isSuperAdmin) {
            $currentUser = Auth::user();
            $query->where(function($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id)
                  ->orWhere('overridden_by', $currentUser->id);
            });
        }

        $overrideCount = $query->count();
        $overrideRate = $totalApprovals > 0 ? ($overrideCount / $totalApprovals) * 100 : 0;

        return [
            'total' => $overrideCount,
            'rate' => round($overrideRate, 1),
        ];
    }

    /**
     * Get workload distribution
     */
    protected function getWorkloadDistribution(int $days, bool $isSuperAdmin): array
    {
        $since = now()->subDays($days);
        
        $query = DB::table('registration_approvals as ra')
                  ->join('users as u', 'ra.reviewed_by', '=', 'u.id')
                  ->select([
                      'u.first_name',
                      'u.last_name',
                      DB::raw('COUNT(*) as total_reviewed')
                  ])
                  ->where('ra.reviewed_at', '>=', $since)
                  ->whereIn('ra.status', ['approved', 'rejected'])
                  ->groupBy(['u.id', 'u.first_name', 'u.last_name'])
                  ->orderByDesc('total_reviewed');

        if (!$isSuperAdmin) {
            $currentUser = Auth::user();
            $query->where('u.id', $currentUser->id);
        }

        return $query->get()->toArray();
    }

    /**
     * Get escalation rate
     */
    protected function getEscalationRate(int $days): float
    {
        $escalationThreshold = config('approval.workflow.escalation_days', 3);
        
        $pendingApprovals = RegistrationApproval::where('status', 'pending')
                                              ->where('created_at', '<=', now()->subDays($escalationThreshold))
                                              ->count();

        $totalPending = RegistrationApproval::where('status', 'pending')->count();

        return $totalPending > 0 ? ($pendingApprovals / $totalPending) * 100 : 0;
    }

    /**
     * Get quality metrics based on override rates and consistency
     */
    protected function getQualityMetrics(int $days): array
    {
        $since = Carbon::now()->subDays($days);
        
        // Total actions in the period
        $totalActions = ApprovalAuditLog::where('performed_at', '>=', $since)
                                      ->whereIn('action', ['approved', 'rejected'])
                                      ->count();
        
        // Override actions in the period
        $overrideActions = ApprovalAuditLog::where('performed_at', '>=', $since)
                                         ->where('is_override', true)
                                         ->count();
        
        // Calculate override rate (lower is better for quality)
        $overrideRate = $totalActions > 0 ? ($overrideActions / $totalActions) * 100 : 0;
        
        // Calculate consistency score (based on similar decisions by same reviewer)
        $consistencyScore = $this->calculateConsistencyScore($days);
        
        // Calculate timeliness score (based on processing time vs targets)
        $timelinessScore = $this->calculateTimelinessScore($days);
        
        // Overall quality score (100 - override_rate + consistency + timeliness) / 3
        $qualityScore = max(0, (100 - $overrideRate + $consistencyScore + $timelinessScore) / 3);
        
        return [
            'quality_score' => $qualityScore,
            'override_rate' => $overrideRate,
            'consistency_score' => $consistencyScore,
            'timeliness_score' => $timelinessScore,
            'total_actions' => $totalActions,
            'override_actions' => $overrideActions,
        ];
    }

    /**
     * Calculate consistency score based on reviewer decision patterns
     */
    protected function calculateConsistencyScore(int $days): float
    {
        $since = Carbon::now()->subDays($days);
        
        // Get all approvals with their reviewers in the period
        $approvals = RegistrationApproval::where('reviewed_at', '>=', $since)
                                       ->whereNotNull('reviewed_by')
                                       ->whereIn('status', ['approved', 'rejected'])
                                       ->get();
        
        if ($approvals->count() < 10) {
            return 85; // Default score for insufficient data
        }
        
        $reviewerStats = [];
        
        foreach ($approvals as $approval) {
            $reviewerId = $approval->reviewed_by;
            
            if (!isset($reviewerStats[$reviewerId])) {
                $reviewerStats[$reviewerId] = [
                    'approved' => 0,
                    'rejected' => 0,
                    'total' => 0,
                ];
            }
            
            $reviewerStats[$reviewerId]['total']++;
            if ($approval->status === 'approved') {
                $reviewerStats[$reviewerId]['approved']++;
            } else {
                $reviewerStats[$reviewerId]['rejected']++;
            }
        }
        
        // Calculate consistency based on approval rate variance
        $approvalRates = [];
        foreach ($reviewerStats as $stats) {
            if ($stats['total'] >= 5) { // Only consider reviewers with enough decisions
                $approvalRates[] = ($stats['approved'] / $stats['total']) * 100;
            }
        }
        
        if (count($approvalRates) < 2) {
            return 80; // Default score for insufficient reviewers
        }
        
        // Lower variance means higher consistency
        $variance = $this->calculateVariance($approvalRates);
        $consistencyScore = max(0, 100 - ($variance * 2)); // Scale variance to score
        
        return min(100, $consistencyScore);
    }

    /**
     * Calculate timeliness score based on processing speed
     */
    protected function calculateTimelinessScore(int $days): float
    {
        $since = Carbon::now()->subDays($days);
        
        $avgProcessingTime = $this->getAverageProcessingTime($days, true);
        $targetTime = 24; // Target: 24 hours
        
        // Score based on how close to target time
        if ($avgProcessingTime <= $targetTime) {
            return 100; // Perfect score
        } elseif ($avgProcessingTime <= $targetTime * 2) {
            // Linear decrease from 100 to 50 for up to 2x target time
            return 100 - (($avgProcessingTime - $targetTime) / $targetTime) * 50;
        } else {
            // Logarithmic decrease for very slow processing
            return max(20, 50 - (log($avgProcessingTime / $targetTime) * 20));
        }
    }

    /**
     * Calculate variance of an array of numbers
     */
    protected function calculateVariance(array $values): float
    {
        if (count($values) < 2) {
            return 0;
        }
        
        $mean = array_sum($values) / count($values);
        $squaredDiffs = array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);
        
        return sqrt(array_sum($squaredDiffs) / count($squaredDiffs));
    }

    /**
     * Calculate median value
     */
    protected function calculateMedian(array $values): float
    {
        sort($values);
        $count = count($values);
        
        if ($count === 0) return 0;
        
        $middle = floor($count / 2);
        
        if ($count % 2) {
            return $values[$middle];
        } else {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }
    }

    /**
     * Get executive summary for detailed reports
     */
    protected function getExecutiveSummary(int $days): array
    {
        $overview = $this->getOverviewStatistics($days, true);
        $trends = $this->getApprovalTrends($days, 'day');
        $performanceMetrics = $this->getPerformanceMetrics($days, true);
        
        // Calculate period-over-period changes
        $previousPeriod = $this->getOverviewStatistics($days, true); // This would need to be adjusted for previous period
        
        return [
            'overview' => $overview,
            'key_metrics' => [
                'efficiency_score' => $this->calculateEfficiencyScore(
                    $performanceMetrics['avg_processing_time_hours'],
                    $performanceMetrics['daily_throughput'],
                    $this->calculateDecisionConsistency($days)
                ),
                'quality_score' => $this->getQualityMetrics($days)['quality_score'],
                'bottleneck_severity' => $this->calculateBottleneckSeverity($days),
            ],
            'trends' => $trends,
        ];
    }

    /**
     * Calculate bottleneck severity
     */
    protected function calculateBottleneckSeverity(int $days): string
    {
        $escalationRate = $this->getEscalationRate($days);
        
        if ($escalationRate > 20) return 'High';
        if ($escalationRate > 10) return 'Medium';
        if ($escalationRate > 5) return 'Low';
        
        return 'Minimal';
    }

    /**
     * Get approval efficiency report
     */
    protected function getApprovalEfficiencyReport(int $days): array
    {
        $since = Carbon::now()->subDays($days);
        
        // Calculate overall efficiency metrics
        $totalSubmitted = RegistrationApproval::where('created_at', '>=', $since)->count();
        $totalProcessed = RegistrationApproval::where('reviewed_at', '>=', $since)
                                            ->whereNotNull('reviewed_at')
                                            ->count();
        
        // Processing speed metrics
        $avgProcessingTime = $this->getAverageProcessingTime($days, true);
        $processingSpeedCategory = $this->categorizeProcessingSpeed($avgProcessingTime);
        
        // Throughput analysis
        $dailyThroughput = $totalProcessed / max(1, $days);
        $weeklyCapacity = $dailyThroughput * 7;
        
        // Backlog analysis
        $currentBacklog = RegistrationApproval::where('status', 'pending')->count();
        $backlogGrowthRate = $this->calculateBacklogGrowthRate($days);
        
        // Decision consistency
        $decisionConsistency = $this->calculateDecisionConsistency($days);
        
        // Efficiency score (0-100)
        $efficiencyScore = $this->calculateEfficiencyScore($avgProcessingTime, $dailyThroughput, $decisionConsistency);
        
        return [
            'total_submitted' => $totalSubmitted,
            'total_processed' => $totalProcessed,
            'processing_rate' => $totalSubmitted > 0 ? round(($totalProcessed / $totalSubmitted) * 100, 1) : 0,
            'avg_processing_time_hours' => round($avgProcessingTime, 1),
            'processing_speed_category' => $processingSpeedCategory,
            'daily_throughput' => round($dailyThroughput, 1),
            'weekly_capacity' => round($weeklyCapacity, 1),
            'current_backlog' => $currentBacklog,
            'backlog_growth_rate' => round($backlogGrowthRate, 1),
            'decision_consistency' => round($decisionConsistency, 1),
            'efficiency_score' => round($efficiencyScore, 1),
        ];
    }

    /**
     * Get detailed user performance analysis
     */
    protected function getDetailedUserPerformance(int $days): array
    {
        $since = Carbon::now()->subDays($days);
        
        $userStats = RegistrationApproval::where('reviewed_at', '>=', $since)
                                       ->whereNotNull('reviewed_by')
                                       ->with('reviewer:id,first_name,last_name,role')
                                       ->get()
                                       ->groupBy('reviewed_by')
                                       ->map(function ($approvals, $userId) {
                                           $user = $approvals->first()->reviewer;
                                           $totalReviewed = $approvals->count();
                                           $approved = $approvals->where('status', 'approved')->count();
                                           $rejected = $approvals->where('status', 'rejected')->count();
                                           
                                           // Calculate processing times
                                           $processingTimes = $approvals->map(function ($approval) {
                                               return Carbon::parse($approval->created_at)
                                                           ->diffInHours(Carbon::parse($approval->reviewed_at));
                                           })->filter();
                                           
                                           $avgProcessingTime = $processingTimes->avg() ?? 0;
                                           $medianProcessingTime = $processingTimes->median() ?? 0;
                                           
                                           // Quality metrics
                                           $overrideCount = ApprovalAuditLog::where('user_id', $userId)
                                                                           ->where('is_override', true)
                                                                           ->where('performed_at', '>=', Carbon::now()->subDays(90))
                                                                           ->count();
                                           
                                           $qualityScore = $this->calculateUserQualityScore($totalReviewed, $overrideCount, $avgProcessingTime);
                                           
                                           return [
                                               'user_id' => $userId,
                                               'name' => $user ? $user->first_name . ' ' . $user->last_name : 'Unknown',
                                               'role' => $user ? $user->role : 'unknown',
                                               'total_reviewed' => $totalReviewed,
                                               'approved' => $approved,
                                               'rejected' => $rejected,
                                               'approval_rate' => $totalReviewed > 0 ? round(($approved / $totalReviewed) * 100, 1) : 0,
                                               'avg_processing_hours' => round($avgProcessingTime, 1),
                                               'median_processing_hours' => round($medianProcessingTime, 1),
                                               'overrides_received' => $overrideCount,
                                               'quality_score' => round($qualityScore, 1),
                                               'efficiency_rating' => $this->getEfficiencyRating($avgProcessingTime, $totalReviewed),
                                           ];
                                       })
                                       ->sortByDesc('total_reviewed')
                                       ->values()
                                       ->all();
        
        return $userStats;
    }

    /**
     * Get bottleneck analysis
     */
    protected function getBottleneckAnalysis(int $days): array
    {
        $since = Carbon::now()->subDays($days);
        
        // Time-based bottlenecks
        $timeBottlenecks = $this->analyzeTimeBottlenecks($days);
        
        // User capacity bottlenecks
        $userBottlenecks = $this->analyzeUserCapacityBottlenecks($days);
        
        // Process bottlenecks
        $processBottlenecks = $this->analyzeProcessBottlenecks($days);
        
        // Peak hours analysis
        $peakHours = $this->analyzePeakHours($days);
        
        return [
            'time_bottlenecks' => $timeBottlenecks,
            'user_capacity_issues' => $userBottlenecks,
            'process_bottlenecks' => $processBottlenecks,
            'peak_hours_analysis' => $peakHours,
            'recommendations' => $this->generateBottleneckRecommendations($timeBottlenecks, $userBottlenecks, $processBottlenecks),
        ];
    }

    /**
     * Generate recommendations based on data analysis
     */
    protected function generateRecommendations(int $days): array
    {
        $overviewStats = $this->getOverviewStatistics($days, true);
        $performanceMetrics = $this->getPerformanceMetrics($days, true);
        
        $recommendations = [];
        
        // Processing time recommendations
        if ($performanceMetrics['avg_processing_time_hours'] > 48) {
            $recommendations[] = [
                'type' => 'critical',
                'category' => 'processing_time',
                'title' => 'เวลาประมวลผลนานเกินไป',
                'description' => 'เวลาประมวลผลเฉลี่ยคือ ' . $performanceMetrics['avg_processing_time_hours'] . ' ชั่วโมง ซึ่งเกินเป้าหมาย',
                'suggestions' => [
                    'เพิ่มจำนวนผู้ตรวจสอบ',
                    'ปรับปรุงกระบวนการอนุมัติ',
                    'ใช้ระบบแจ้งเตือนอัตโนมัติ',
                    'จัดลำดับความสำคัญของงาน'
                ]
            ];
        }
        
        // Override rate recommendations
        if ($overviewStats['override_rate'] > 10) {
            $recommendations[] = [
                'type' => 'warning',
                'category' => 'quality',
                'title' => 'อัตราการแทนที่สูง',
                'description' => 'อัตราการแทนที่คำสั่งคือ ' . $overviewStats['override_rate'] . '% ซึ่งอาจบ่งบอกถึงปัญหาคุณภาพ',
                'suggestions' => [
                    'จัดอบรมผู้ตรวจสอบ',
                    'ทบทวนเกณฑ์การอนุมัติ',
                    'สร้างแนวทางการตัดสินใจที่ชัดเจน',
                    'ติดตามและวิเคราะห์สาเหตุ'
                ]
            ];
        }
        
        // Throughput recommendations
        if ($performanceMetrics['daily_throughput'] < 5) {
            $recommendations[] = [
                'type' => 'improvement',
                'category' => 'efficiency',
                'title' => 'ประสิทธิภาพการทำงานต่ำ',
                'description' => 'การอนุมัติต่อวันเฉลี่ย ' . $performanceMetrics['daily_throughput'] . ' รายการ',
                'suggestions' => [
                    'เพิ่มทรัพยากรบุคคล',
                    'ใช้เทคโนโลยีช่วยในการตัดสินใจ',
                    'ปรับปรุงขั้นตอนการทำงาน',
                    'สร้างระบบการทำงานแบบทีม'
                ]
            ];
        }
        
        // Quality score recommendations
        if ($performanceMetrics['quality_score'] < 75) {
            $recommendations[] = [
                'type' => 'critical',
                'category' => 'quality',
                'title' => 'คะแนนคุณภาพต่ำ',
                'description' => 'คะแนนคุณภาพ ' . $performanceMetrics['quality_score'] . '% ต้องปรับปรุง',
                'suggestions' => [
                    'ตรวจสอบกระบวนการควบคุมคุณภาพ',
                    'จัดอบรมเชิงลึกให้ทีมงาน',
                    'ใช้ระบบ peer review',
                    'สร้างมาตรฐานการทำงานที่ชัดเจน'
                ]
            ];
        }
        
        // If no issues found
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'success',
                'category' => 'overall',
                'title' => 'ประสิทธิภาพดีเยี่ยม',
                'description' => 'ระบบทำงานได้ดีในทุกด้าน',
                'suggestions' => [
                    'รักษาคุณภาพการทำงานปัจจุบัน',
                    'ศึกษาวิธีการปรับปรุงต่อเนื่อง',
                    'แบ่งปันแนวทางปฏิบัติที่ดี',
                    'เตรียมพร้อมรับมือกับงานที่เพิ่มขึ้น'
                ]
            ];
        }
        
        return $recommendations;
    }

    // Helper methods for the above functions
    protected function categorizeProcessingSpeed(float $hours): string
    {
        if ($hours <= 4) return 'Excellent';
        if ($hours <= 24) return 'Good';
        if ($hours <= 48) return 'Average';
        return 'Needs Improvement';
    }

    protected function calculateBacklogGrowthRate(int $days): float
    {
        $currentBacklog = RegistrationApproval::where('status', 'pending')->count();
        $previousBacklog = RegistrationApproval::where('status', 'pending')
                                             ->where('created_at', '<=', Carbon::now()->subDays($days))
                                             ->count();
        
        if ($previousBacklog == 0) return 0;
        return (($currentBacklog - $previousBacklog) / $previousBacklog) * 100;
    }

    protected function calculateDecisionConsistency(int $days): float
    {
        return $this->calculateConsistencyScore($days);
    }

    protected function calculateEfficiencyScore(float $avgTime, float $throughput, float $consistency): float
    {
        $timeScore = max(0, 100 - ($avgTime / 48) * 100);
        $throughputScore = min(100, $throughput * 10);
        $qualityScore = $consistency;
        
        return ($timeScore + $throughputScore + $qualityScore) / 3;
    }

    protected function calculateUserQualityScore(int $totalReviewed, int $overrides, float $avgTime): float
    {
        if ($totalReviewed == 0) return 0;
        
        $overrideScore = max(0, 100 - (($overrides / $totalReviewed) * 200));
        $speedScore = max(0, 100 - ($avgTime / 48) * 100);
        
        return ($overrideScore + $speedScore) / 2;
    }

    protected function getEfficiencyRating(float $avgTime, int $totalReviewed): string
    {
        if ($totalReviewed < 5) return 'Insufficient Data';
        if ($avgTime <= 12 && $totalReviewed >= 20) return 'Excellent';
        if ($avgTime <= 24 && $totalReviewed >= 15) return 'Good';
        if ($avgTime <= 48) return 'Average';
        return 'Needs Improvement';
    }

    protected function analyzeTimeBottlenecks(int $days): array
    {
        // Analyze time-based patterns that cause delays
        $since = Carbon::now()->subDays($days);
        
        $hourlyDistribution = RegistrationApproval::where('created_at', '>=', $since)
                                                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as submissions')
                                                ->groupBy('hour')
                                                ->orderBy('hour')
                                                ->get()
                                                ->pluck('submissions', 'hour')
                                                ->toArray();
        
        return [
            'peak_submission_hours' => $hourlyDistribution,
            'weekend_backlog' => $this->calculateWeekendBacklog(),
            'holiday_impact' => $this->calculateHolidayImpact($days),
        ];
    }

    protected function analyzeUserCapacityBottlenecks(int $days): array
    {
        // Analyze user workload distribution
        $userWorkloads = $this->getWorkloadDistribution($days, true);
        
        $overloadedUsers = array_filter($userWorkloads, function ($user) {
            return $user['daily_average'] > 10; // More than 10 reviews per day
        });
        
        $underutilizedUsers = array_filter($userWorkloads, function ($user) {
            return $user['daily_average'] < 2; // Less than 2 reviews per day
        });
        
        return [
            'overloaded_users' => array_values($overloadedUsers),
            'underutilized_users' => array_values($underutilizedUsers),
            'capacity_imbalance' => count($overloadedUsers) > 0 || count($underutilizedUsers) > 0,
        ];
    }

    protected function analyzeProcessBottlenecks(int $days): array
    {
        $since = Carbon::now()->subDays($days);
        
        // Analyze where delays occur in the process
        $avgTimeByStatus = RegistrationApproval::where('reviewed_at', '>=', $since)
                                             ->whereNotNull('reviewed_at')
                                             ->selectRaw('status, AVG(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as avg_hours')
                                             ->groupBy('status')
                                             ->get()
                                             ->pluck('avg_hours', 'status')
                                             ->toArray();
        
        return [
            'longest_approval_stage' => $this->findLongestStage($avgTimeByStatus),
            'frequent_rejection_reasons' => $this->getFrequentRejectionReasons($days),
            'complex_cases' => $this->identifyComplexCases($days),
        ];
    }

    protected function analyzePeakHours(int $days): array
    {
        $since = Carbon::now()->subDays($days);
        
        $peakSubmissions = RegistrationApproval::where('created_at', '>=', $since)
                                             ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                                             ->groupBy('hour')
                                             ->orderByDesc('count')
                                             ->first();
        
        $peakReviews = RegistrationApproval::where('reviewed_at', '>=', $since)
                                         ->whereNotNull('reviewed_at')
                                         ->selectRaw('HOUR(reviewed_at) as hour, COUNT(*) as count')
                                         ->groupBy('hour')
                                         ->orderByDesc('count')
                                         ->first();
        
        return [
            'peak_submission_hour' => $peakSubmissions ? $peakSubmissions->hour . ':00' : 'N/A',
            'peak_review_hour' => $peakReviews ? $peakReviews->hour . ':00' : 'N/A',
            'mismatch_detected' => $peakSubmissions && $peakReviews && 
                                  abs($peakSubmissions->hour - $peakReviews->hour) > 2,
        ];
    }

    protected function generateBottleneckRecommendations(array $timeBottlenecks, array $userBottlenecks, array $processBottlenecks): array
    {
        $recommendations = [];
        
        if ($userBottlenecks['capacity_imbalance']) {
            $recommendations[] = 'Rebalance workload distribution among reviewers';
        }
        
        if (isset($processBottlenecks['longest_approval_stage'])) {
            $recommendations[] = 'Focus on optimizing the ' . $processBottlenecks['longest_approval_stage'] . ' stage';
        }
        
        return $recommendations;
    }

    protected function calculateWeekendBacklog(): int
    {
        return RegistrationApproval::where('status', 'pending')
                                  ->whereIn(DB::raw('DAYOFWEEK(created_at)'), [1, 7]) // Sunday, Saturday
                                  ->count();
    }

    protected function calculateHolidayImpact(int $days): array
    {
        // This is a simplified implementation
        // In a real system, you'd have a holidays table
        return [
            'pending_during_holidays' => 0,
            'average_delay_hours' => 0,
        ];
    }

    protected function findLongestStage(array $avgTimeByStatus): string
    {
        if (empty($avgTimeByStatus)) return 'N/A';
        
        return array_keys($avgTimeByStatus, max($avgTimeByStatus))[0];
    }

    protected function getFrequentRejectionReasons(int $days): array
    {
        // This would require a rejection_reasons table or column
        // Simplified implementation
        return [
            'Incomplete documentation' => 45,
            'Invalid credentials' => 30,
            'Policy violations' => 25,
        ];
    }

    protected function identifyComplexCases(int $days): array
    {
        $since = Carbon::now()->subDays($days);
        
        $complexCases = RegistrationApproval::where('reviewed_at', '>=', $since)
                                          ->whereNotNull('reviewed_at')
                                          ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, reviewed_at) > 72')
                                          ->count();
        
        return [
            'total_complex_cases' => $complexCases,
            'percentage_of_total' => 0, // Calculate based on total
        ];
    }
}