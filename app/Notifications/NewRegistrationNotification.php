<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RegistrationApproval;

class NewRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected RegistrationApproval $approval;

    /**
     * Create a new notification instance.
     */
    public function __construct(RegistrationApproval $approval)
    {
        $this->approval = $approval;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // ปิด mail ชั่วคราว
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $userName = $this->approval->user->first_name . ' ' . $this->approval->user->last_name;
        
        return (new MailMessage)
                    ->subject('📝 มีการสมัครสมาชิกใหม่')
                    ->greeting("สวัสดี {$notifiable->first_name}")
                    ->line("มีการสมัครสมาชิกใหม่ที่รอการอนุมัติ")
                    ->line("**ผู้สมัคร:** {$userName}")
                    ->line("**อีเมล:** {$this->approval->user->email}")
                    ->line("**วันที่สมัคร:** {$this->approval->created_at->format('d/m/Y H:i:s')}")
                    ->action('ดูรายละเอียดและอนุมัติ', route('admin.approvals.show', $this->approval))
                    ->line('กรุณาพิจารณาดำเนินการอนุมัติหรือปฏิเสธตามความเหมาะสม');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_registration',
            'approval_id' => $this->approval->id,
            'user_name' => $this->approval->user->first_name . ' ' . $this->approval->user->last_name,
            'user_email' => $this->approval->user->email,
            'created_at' => $this->approval->created_at,
            'message' => 'มีการสมัครสมาชิกใหม่รอการอนุมัติ',
        ];
    }
}
