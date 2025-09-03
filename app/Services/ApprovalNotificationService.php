<?php

namespace App\Services;

use App\Models\RegistrationApproval;
use App\Models\User;
use App\Notifications\ApprovalOverrideNotification;
use App\Notifications\ApprovalEscalationNotification;
use App\Notifications\NewRegistrationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class ApprovalNotificationService
{
    /**
     * Send notification about new registration to admins/super admins
     */
    public function notifyNewRegistration(RegistrationApproval $approval): void
    {
        if (!config('approval.notifications.notify_super_admin_on_new_registration', false)) {
            return;
        }

        try {
            $recipients = $this->getNotificationRecipients(['admin', 'super_admin']);
            
            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new NewRegistrationNotification($approval));
                Log::info("New registration notification sent for user {$approval->user_id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send new registration notification: " . $e->getMessage());
        }
    }

    /**
     * Send override notification to the admin whose decision was overridden
     */
    public function notifyApprovalOverride(
        RegistrationApproval $approval, 
        string $overrideAction, 
        string $overrideReason,
        int $originalReviewerId
    ): void {
        if (!config('approval.notifications.notify_admin_on_super_admin_override', true)) {
            return;
        }

        try {
            $originalReviewer = User::find($originalReviewerId);
            $superAdmin = auth()->user();
            
            if ($originalReviewer && $originalReviewer->id !== $superAdmin->id) {
                $overriddenBy = $superAdmin->first_name . ' ' . $superAdmin->last_name;
                
                $originalReviewer->notify(new ApprovalOverrideNotification(
                    $approval,
                    $overrideAction,
                    $overrideReason,
                    $overriddenBy
                ));
                
                Log::info("Override notification sent to user {$originalReviewer->id} for approval {$approval->id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send override notification: " . $e->getMessage());
        }
    }

    /**
     * Send escalation notification to super admins about pending approvals
     */
    public function notifyApprovalEscalation(RegistrationApproval $approval, int $daysPending): void
    {
        if (!config('approval.notifications.notify_super_admin_on_pending_escalation', true)) {
            return;
        }

        try {
            $superAdmins = $this->getNotificationRecipients(['super_admin']);
            
            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new ApprovalEscalationNotification($approval, $daysPending));
                Log::info("Escalation notification sent for approval {$approval->id} pending {$daysPending} days");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send escalation notification: " . $e->getMessage());
        }
    }

    /**
     * Check for escalated approvals and send notifications
     */
    public function checkAndNotifyEscalations(): int
    {
        $escalationDays = config('approval.workflow.escalation_days', 3);
        $escalationDate = now()->subDays($escalationDays);
        
        $escalatedApprovals = RegistrationApproval::where('status', 'pending')
            ->where('created_at', '<=', $escalationDate)
            ->whereDoesntHave('auditLogs', function($query) {
                $query->where('action', 'escalated');
            })
            ->get();

        $notificationCount = 0;
        foreach ($escalatedApprovals as $approval) {
            $daysPending = (int) $approval->created_at->diffInDays(now());
            
            $this->notifyApprovalEscalation($approval, $daysPending);
            
            // Mark as escalated in audit log
            app(ApprovalAuditService::class)->logEscalation($approval);
            
            $notificationCount++;
        }

        return $notificationCount;
    }

    /**
     * Send daily summary to admins and super admins
     */
    public function sendDailySummary(): void
    {
        if (!config('approval.notifications.daily_summary_enabled', true)) {
            return;
        }

        try {
            $stats = $this->getDailySummaryStats();
            $recipients = $this->getNotificationRecipients(['admin', 'super_admin']);
            
            // Send summary notification (you can create a separate notification class for this)
            Log::info("Daily summary: " . json_encode($stats));
            
        } catch (\Exception $e) {
            Log::error("Failed to send daily summary: " . $e->getMessage());
        }
    }

    /**
     * Get users who should receive notifications based on roles
     */
    protected function getNotificationRecipients(array $roles): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereIn('role', $roles)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->get();
    }

    /**
     * Get daily summary statistics
     */
    protected function getDailySummaryStats(): array
    {
        $today = now()->startOfDay();
        
        return [
            'new_registrations' => RegistrationApproval::whereDate('created_at', $today)->count(),
            'approvals_processed' => RegistrationApproval::whereDate('reviewed_at', $today)->count(),
            'pending_count' => RegistrationApproval::where('status', 'pending')->count(),
            'escalated_count' => RegistrationApproval::where('status', 'pending')
                ->where('created_at', '<=', now()->subDays(config('approval.workflow.escalation_days', 3)))
                ->count(),
        ];
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId): int
    {
        $user = User::find($userId);
        if (!$user) {
            return 0;
        }

        $unreadCount = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();
        
        return $unreadCount;
    }

    /**
     * Get recent notifications for a user
     */
    public function getRecentNotifications(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $user = User::find($userId);
        if (!$user) {
            return collect();
        }

        return $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notification statistics for a user
     */
    public function getNotificationStats(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['total' => 0, 'unread' => 0];
        }

        return [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
        ];
    }
}
