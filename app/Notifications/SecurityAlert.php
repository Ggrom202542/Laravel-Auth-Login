<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SecurityAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $title;
    public $message;
    public $data;
    public $severity;
    public $actionUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($type, $title, $message, $data = [], $severity = 'info', $actionUrl = null)
    {
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->severity = $severity;
        $this->actionUrl = $actionUrl;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];
        
        // Send email for critical security alerts
        if ($this->severity === 'critical') {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Security Alert: ' . $this->title)
            ->greeting('Security Alert')
            ->line($this->message)
            ->line('Event Type: ' . ucwords(str_replace('_', ' ', $this->type)))
            ->line('Severity: ' . ucfirst($this->severity))
            ->line('Time: ' . now()->format('Y-m-d H:i:s'));

        // Add additional data
        if (!empty($this->data)) {
            $mail->line('Additional Details:');
            foreach ($this->data as $key => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $mail->line(ucwords(str_replace('_', ' ', $key)) . ': ' . $value);
                }
            }
        }

        if ($this->actionUrl) {
            $mail->action('View Security Dashboard', $this->actionUrl);
        } else {
            $mail->action('View Security Dashboard', route('user.security.index'));
        }

        return $mail->line('Please review your account security and contact support if you did not authorize this activity.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'severity' => $this->severity,
            'action_url' => $this->actionUrl,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id ?? uniqid(),
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'severity' => $this->severity,
            'action_url' => $this->actionUrl,
            'timestamp' => now()->toISOString(),
            'read_at' => null,
            'user_id' => $notifiable->id,
        ]);
    }
}
