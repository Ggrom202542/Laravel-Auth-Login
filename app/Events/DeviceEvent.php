<?php

namespace App\Events;

use App\Models\UserDevice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $device;
    public $action;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(UserDevice $device, string $action = 'updated')
    {
        $this->device = $device;
        $this->action = $action; // 'created', 'updated', 'trusted', 'untrusted', 'removed'
        $this->timestamp = now()->toISOString();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('security.' . $this->device->user_id),
            new Channel('admin.devices'), // For admin monitoring
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'device' => [
                'id' => $this->device->id,
                'user_id' => $this->device->user_id,
                'device_name' => $this->device->device_name,
                'device_type' => $this->device->device_type,
                'browser' => $this->device->browser,
                'platform' => $this->device->platform,
                'ip_address' => $this->device->ip_address,
                'location' => $this->device->location,
                'is_trusted' => $this->device->is_trusted,
                'last_used_at' => $this->device->last_used_at?->toISOString(),
                'created_at' => $this->device->created_at?->toISOString(),
            ],
            'action' => $this->action,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'device.' . $this->action;
    }
}
