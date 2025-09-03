<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RegistrationApproval;

class ApprovalEscalationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected RegistrationApproval $approval;
    protected int $daysPending;

    /**
     * Create a new notification instance.
     */
    public function __construct(RegistrationApproval $approval, int $daysPending)
    {
        $this->approval = $approval;
        $this->daysPending = $daysPending;
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
        $userName = $this->approval->user->first_name . ' ' . $this->approval->user->last_name;
        
        return (new MailMessage)
                    ->subject('⚠️ Escalation: การสมัครสมาชิกค้างนาน')
                    ->greeting("สวัสดี Super Admin")
                    ->line("มีการสมัครสมาชิกที่ค้างการอนุมัติเป็นเวลา **{$this->daysPending} วัน**")
                    ->line("**ผู้สมัคร:** {$userName}")
                    ->line("**อีเมล:** {$this->approval->user->email}")
                    ->line("**วันที่สมัคร:** {$this->approval->created_at->format('d/m/Y H:i:s')}")
                    ->line('กรุณาพิจารณาดำเนินการอนุมัติหรือปฏิเสธ')
                    ->action('ดูรายละเอียดและอนุมัติ', route('admin.approvals.show', $this->approval))
                    ->line('การแจ้งเตือนนี้ส่งโดยอัตโนมัติเมื่อมีการสมัครค้างนานเกินกำหนด');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval_escalation',
            'approval_id' => $this->approval->id,
            'user_name' => $this->approval->user->first_name . ' ' . $this->approval->user->last_name,
            'days_pending' => $this->daysPending,
            'created_at' => $this->approval->created_at,
            'message' => "การสมัครสมาชิกค้างการอนุมัติ {$this->daysPending} วัน",
        ];
    }
}
