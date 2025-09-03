<?php

namespace App\Services;

use App\Models\ApprovalAuditLog;
use App\Models\RegistrationApproval;
use App\Services\ApprovalNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ApprovalAuditService
{
    /**
     * Log an approval action with full audit trail
     */
    public function logAction(
        RegistrationApproval $approval,
        string $action,
        ?string $reason = null,
        ?string $comments = null,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        bool $isOverride = false,
        ?int $overriddenBy = null
    ): ApprovalAuditLog {
        $metadata = $this->gatherMetadata();
        
        return ApprovalAuditLog::create([
            'registration_approval_id' => $approval->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'old_status' => $oldStatus ?? $approval->getOriginal('status'),
            'new_status' => $newStatus ?? $approval->status,
            'reason' => $reason,
            'comments' => $comments,
            'metadata' => $metadata,
            'is_override' => $isOverride,
            'overridden_by' => $overriddenBy,
            'performed_at' => now(),
        ]);
    }

    /**
     * Log approval action
     */
    public function logApproval(
        RegistrationApproval $approval, 
        ?string $reason = null,
        bool $isOverride = false,
        ?int $overriddenBy = null
    ): ApprovalAuditLog {
        $log = $this->logAction(
            approval: $approval,
            action: $isOverride ? 'override_approved' : 'approved',
            reason: $reason,
            oldStatus: 'pending',
            newStatus: 'approved',
            isOverride: $isOverride,
            overriddenBy: $overriddenBy
        );

        // Send notification if this is an override
        if ($isOverride && $overriddenBy) {
            $notificationService = app(ApprovalNotificationService::class);
            $notificationService->notifyApprovalOverride(
                $approval, 
                'approved', 
                $reason ?? 'No reason provided',
                $overriddenBy
            );
        }

        return $log;
    }

    /**
     * Log rejection action
     */
    public function logRejection(
        RegistrationApproval $approval,
        string $reason,
        bool $isOverride = false,
        ?int $overriddenBy = null
    ): ApprovalAuditLog {
        $log = $this->logAction(
            approval: $approval,
            action: $isOverride ? 'override_rejected' : 'rejected',
            reason: $reason,
            oldStatus: 'pending', 
            newStatus: 'rejected',
            isOverride: $isOverride,
            overriddenBy: $overriddenBy
        );

        // Send notification if this is an override
        if ($isOverride && $overriddenBy) {
            $notificationService = app(ApprovalNotificationService::class);
            $notificationService->notifyApprovalOverride(
                $approval, 
                'rejected', 
                $reason,
                $overriddenBy
            );
        }

        return $log;
    }

    /**
     * Log view action
     */
    public function logView(RegistrationApproval $approval): ApprovalAuditLog
    {
        return $this->logAction(
            approval: $approval,
            action: 'viewed'
        );
    }

    /**
     * Log comment action
     */
    public function logComment(
        RegistrationApproval $approval, 
        string $comment
    ): ApprovalAuditLog {
        return $this->logAction(
            approval: $approval,
            action: 'commented',
            comments: $comment
        );
    }

    /**
     * Log status change
     */
    public function logStatusChange(
        RegistrationApproval $approval,
        string $oldStatus,
        string $newStatus,
        ?string $reason = null
    ): ApprovalAuditLog {
        return $this->logAction(
            approval: $approval,
            action: 'status_changed',
            reason: $reason,
            oldStatus: $oldStatus,
            newStatus: $newStatus
        );
    }

    /**
     * Log escalation to super admin
     */
    public function logEscalation(
        RegistrationApproval $approval,
        string $reason = 'Auto-escalated due to timeout'
    ): ApprovalAuditLog {
        return $this->logAction(
            approval: $approval,
            action: 'escalated',
            reason: $reason
        );
    }

    /**
     * Log deletion
     */
    public function logDeletion(
        RegistrationApproval $approval,
        ?string $reason = null
    ): ApprovalAuditLog {
        return $this->logAction(
            approval: $approval,
            action: 'deleted',
            reason: $reason
        );
    }

    /**
     * Get audit trail for an approval
     */
    public function getAuditTrail(RegistrationApproval $approval)
    {
        return ApprovalAuditLog::with(['user', 'overriddenUser'])
            ->forApproval($approval->id)
            ->orderBy('performed_at', 'desc')
            ->get();
    }

    /**
     * Get audit statistics
     */
    public function getAuditStats(?int $days = 30): array
    {
        $since = now()->subDays($days);
        
        $stats = ApprovalAuditLog::where('performed_at', '>=', $since)
            ->selectRaw('
                action,
                user_id,
                COUNT(*) as count,
                COUNT(CASE WHEN is_override = 1 THEN 1 END) as override_count
            ')
            ->with('user:id,first_name,last_name,role')
            ->groupBy(['action', 'user_id'])
            ->get()
            ->groupBy('action');
        
        return [
            'total_actions' => ApprovalAuditLog::where('performed_at', '>=', $since)->count(),
            'total_overrides' => ApprovalAuditLog::where('performed_at', '>=', $since)->where('is_override', true)->count(),
            'actions_by_type' => $stats,
            'period_days' => $days,
            'generated_at' => now(),
        ];
    }

    /**
     * Check if user can perform override
     */
    public function canOverride(?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        $user = Auth::user();
        
        if (!$user || $user->role !== 'super_admin') {
            return false;
        }
        
        return config('approval.super_admin.can_override_admin_decisions', true);
    }

    /**
     * Check if override requires reason
     */
    public function overrideRequiresReason(): bool
    {
        return config('approval.super_admin.requires_override_reason', true);
    }

    /**
     * Gather metadata for audit logging
     */
    protected function gatherMetadata(): array
    {
        $metadata = [];
        
        if (config('approval.security.ip_logging_enabled', true)) {
            $metadata['ip_address'] = Request::ip();
        }
        
        if (config('approval.security.user_agent_logging', false)) {
            $metadata['user_agent'] = Request::userAgent();
        }
        
        $metadata['session_id'] = session()->getId();
        $metadata['timestamp'] = now()->toISOString();
        
        return $metadata;
    }

    /**
     * Get recent override activities for monitoring
     */
    public function getRecentOverrides(?int $limit = 10)
    {
        return ApprovalAuditLog::with(['user', 'overriddenUser', 'registrationApproval.user'])
            ->overrides()
            ->latest('performed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get approval timeline for display
     */
    public function getApprovalTimeline(RegistrationApproval $approval): array
    {
        $logs = $this->getAuditTrail($approval);
        
        $timeline = [];
        foreach ($logs as $log) {
            $timeline[] = [
                'timestamp' => $log->performed_at,
                'user' => $log->user?->first_name . ' ' . $log->user?->last_name,
                'role' => $log->user?->role,
                'action' => $log->action_description,
                'reason' => $log->reason,
                'comments' => $log->comments,
                'is_critical' => $log->is_critical_action,
                'is_override' => $log->is_override,
                'metadata' => $log->metadata,
            ];
        }
        
        return $timeline;
    }
}
