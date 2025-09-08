<?php

namespace App\Events;

use App\Models\LoginAttempt;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoginAttemptEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $loginAttempt;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(LoginAttempt $loginAttempt)
    {
        $this->loginAttempt = $loginAttempt;
        $this->timestamp = now()->toISOString();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('admin.login-attempts'), // For admin monitoring
        ];

        // Also broadcast to user's private channel if they have an account
        if ($this->loginAttempt->user_id) {
            $channels[] = new PrivateChannel('security.' . $this->loginAttempt->user_id);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->loginAttempt->id,
            'user_id' => $this->loginAttempt->user_id,
            'email' => $this->loginAttempt->email,
            'ip_address' => $this->loginAttempt->ip_address,
            'user_agent' => $this->loginAttempt->user_agent,
            'successful' => $this->loginAttempt->successful,
            'failure_reason' => $this->loginAttempt->failure_reason,
            'location' => $this->loginAttempt->location,
            'device_info' => $this->loginAttempt->device_info,
            'is_suspicious' => $this->loginAttempt->is_suspicious,
            'timestamp' => $this->timestamp,
            'created_at' => $this->loginAttempt->created_at?->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'login.attempt';
    }
}
