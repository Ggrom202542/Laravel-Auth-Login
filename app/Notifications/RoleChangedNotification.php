<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoleChangedNotification extends Notification
{
    use Queueable;

    protected $oldRole;
    protected $newRole;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $oldRole, string $newRole, string $reason)
    {
        $this->oldRole = $oldRole;
        $this->newRole = $newRole;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $roleNames = [
            'user' => 'ผู้ใช้งาน',
            'admin' => 'ผู้ดูแลระบบ',
            'super_admin' => 'ผู้ดูแลระบบสูงสุด'
        ];

        return (new MailMessage)
            ->subject('แจ้งเตือน: บทบาทของคุณได้รับการเปลี่ยนแปลง')
            ->greeting('สวัสดี ' . $notifiable->first_name . ' ' . $notifiable->last_name)
            ->line('บทบาทของคุณในระบบได้รับการเปลี่ยนแปลงแล้ว')
            ->line('จาก: ' . ($roleNames[$this->oldRole] ?? $this->oldRole))
            ->line('เป็น: ' . ($roleNames[$this->newRole] ?? $this->newRole))
            ->line('เหตุผล: ' . $this->reason)
            ->line('หากคุณมีคำถามเกี่ยวกับการเปลี่ยนแปลงนี้ กรุณาติดต่อผู้ดูแลระบบ')
            ->action('เข้าสู่ระบบ', url('/dashboard'))
            ->line('ขอบคุณที่ใช้บริการของเรา');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $roleNames = [
            'user' => 'ผู้ใช้งาน',
            'admin' => 'ผู้ดูแลระบบ',
            'super_admin' => 'ผู้ดูแลระบบสูงสุด'
        ];

        return [
            'type' => 'role_changed',
            'message' => 'บทบาทของคุณเปลี่ยนจาก ' . ($roleNames[$this->oldRole] ?? $this->oldRole) . ' เป็น ' . ($roleNames[$this->newRole] ?? $this->newRole),
            'old_role' => $this->oldRole,
            'new_role' => $this->newRole,
            'reason' => $this->reason,
            'title' => 'เปลี่ยนแปลงบทบาท'
        ];
    }
}
