<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RegistrationApproval;

class ApprovalOverrideNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected RegistrationApproval $approval;
    protected string $overrideAction;
    protected string $overrideReason;
    protected string $overriddenBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(RegistrationApproval $approval, string $overrideAction, string $overrideReason, string $overriddenBy)
    {
        $this->approval = $approval;
        $this->overrideAction = $overrideAction;
        $this->overrideReason = $overrideReason;
        $this->overriddenBy = $overriddenBy;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $actionText = $this->overrideAction === 'approved' ? 'อนุมัติ' : 'ปฏิเสธ';
        $userName = $this->approval->user->first_name . ' ' . $this->approval->user->last_name;
        
        return (new MailMessage)
                    ->subject('🔄 Override: การตัดสินใจของคุณได้ถูกแทนที่')
                    ->greeting("สวัสดี {$notifiable->first_name}")
                    ->line("การตัดสินใจของคุณสำหรับการสมัครของ **{$userName}** ได้ถูก Super Admin แทนที่")
                    ->line("**การกระทำใหม่:** {$actionText}")
                    ->line("**ผู้แทนที่:** {$this->overriddenBy}")
                    ->line("**เหตุผล:** {$this->overrideReason}")
                    ->line('นี่เป็นการแจ้งเตือนเพื่อให้คุณทราบถึงการเปลี่ยนแปลงในระบบ')
                    ->action('ดูรายละเอียด', route('admin.approvals.show', $this->approval))
                    ->line('ขอบคุณที่ใช้ระบบของเรา');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval_override',
            'approval_id' => $this->approval->id,
            'user_name' => $this->approval->user->first_name . ' ' . $this->approval->user->last_name,
            'override_action' => $this->overrideAction,
            'override_reason' => $this->overrideReason,
            'overridden_by' => $this->overriddenBy,
            'message' => "การตัดสินใจของคุณได้ถูก Override เป็น {$this->overrideAction}",
        ];
    }
}
