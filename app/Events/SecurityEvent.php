<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SecurityEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $type;
    public $message;
    public $data;
    public $timestamp;
    public $severity;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $type, $message, $data = [], $severity = 'info')
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->message = $message;
        $this->data = $data;
        $this->timestamp = now()->toISOString();
        $this->severity = $severity;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('security.' . $this->userId),
            new Channel('admin.security'), // For admin monitoring
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => $this->timestamp,
            'severity' => $this->severity,
            'user_id' => $this->userId,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'security.event';
    }
}
