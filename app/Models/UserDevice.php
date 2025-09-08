<?php

namespace App\Models;

use App\Events\DeviceEvent;
use App\Events\SecurityEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'device_name',
        'device_type',
        'browser_name',
        'browser_version',
        'operating_system',
        'platform',
        'screen_resolution',
        'timezone',
        'language',
        'user_agent',
        'ip_address',
        'location',
        'is_trusted',
        'is_active',
        'first_seen_at',
        'last_seen_at',
        'last_login_at',
        'trusted_at',
        'login_count',
        'requires_verification',
        'verified_at',
        'verification_method',
        'notes',
        'expires_at'
    ];

    protected $casts = [
        'is_trusted' => 'boolean',
        'is_active' => 'boolean',
        'requires_verification' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_login_at' => 'datetime',
        'trusted_at' => 'datetime',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'login_count' => 'integer'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeTrusted($query)
    {
        return $query->where('is_trusted', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('requires_verification', true)
                    ->whereNull('verified_at');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<', now());
    }

    // Static Methods for Device Management
    public static function findOrCreateDevice(array $deviceData): self
    {
        $fingerprint = $deviceData['device_fingerprint'];
        
        $device = static::where('device_fingerprint', $fingerprint)->first();
        
        if (!$device) {
            $device = static::create(array_merge($deviceData, [
                'first_seen_at' => now(),
                'last_seen_at' => now()
            ]));
        } else {
            // Update last seen and increment login count
            $device->update([
                'last_seen_at' => now(),
                'last_login_at' => now(),
                'login_count' => $device->login_count + 1,
                'ip_address' => $deviceData['ip_address'] ?? $device->ip_address
            ]);
        }
        
        return $device;
    }

    public static function trustDevice(string $fingerprint, int $userId): bool
    {
        return static::where('device_fingerprint', $fingerprint)
                    ->where('user_id', $userId)
                    ->update([
                        'is_trusted' => true,
                        'trusted_at' => now(),
                        'requires_verification' => false,
                        'verified_at' => now()
                    ]);
    }

    public static function revokeDeviceTrust(string $fingerprint, int $userId): bool
    {
        return static::where('device_fingerprint', $fingerprint)
                    ->where('user_id', $userId)
                    ->update([
                        'is_trusted' => false,
                        'trusted_at' => null,
                        'requires_verification' => true,
                        'verified_at' => null
                    ]);
    }

    public static function deactivateDevice(string $fingerprint, int $userId): bool
    {
        return static::where('device_fingerprint', $fingerprint)
                    ->where('user_id', $userId)
                    ->update(['is_active' => false]);
    }

    public static function cleanupExpiredDevices(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }

    // Instance Methods
    public function markAsTrusted(string $method = 'manual'): bool
    {
        return $this->update([
            'is_trusted' => true,
            'trusted_at' => now(),
            'requires_verification' => false,
            'verified_at' => now(),
            'verification_method' => $method
        ]);
    }

    public function revokeTrust(): bool
    {
        return $this->update([
            'is_trusted' => false,
            'trusted_at' => null,
            'requires_verification' => true,
            'verified_at' => null,
            'verification_method' => null
        ]);
    }

    public function updateActivity(array $data = []): bool
    {
        return $this->update(array_merge([
            'last_seen_at' => now(),
            'login_count' => $this->login_count + 1
        ], $data));
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function needsVerification(): bool
    {
        return $this->requires_verification && !$this->verified_at;
    }

    public function getDisplayName(): string
    {
        if ($this->device_name) {
            return $this->device_name;
        }

        $parts = array_filter([
            $this->browser_name,
            $this->operating_system,
            $this->device_type
        ]);

        return implode(' on ', $parts) ?: 'Unknown Device';
    }

    public function getSecurityLevel(): string
    {
        if ($this->is_trusted) {
            return 'trusted';
        }

        if ($this->verified_at) {
            return 'verified';
        }

        if ($this->needsVerification()) {
            return 'unverified';
        }

        return 'unknown';
    }

    public function getDaysActive(): int
    {
        if (!$this->first_seen_at) {
            return 0;
        }

        return $this->first_seen_at->diffInDays(now());
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($device) {
            if (!$device->first_seen_at) {
                $device->first_seen_at = now();
            }
            if (!$device->last_seen_at) {
                $device->last_seen_at = now();
            }
        });

        static::created(function ($device) {
            broadcast(new DeviceEvent($device, 'created'));
            broadcast(new SecurityEvent(
                $device->user_id,
                'device_registered',
                "New device registered: {$device->device_name}",
                [
                    'device_name' => $device->device_name,
                    'device_type' => $device->device_type,
                    'ip_address' => $device->ip_address,
                    'location' => $device->location,
                ],
                'info'
            ));
        });

        static::updated(function ($device) {
            $changes = $device->getChanges();
            
            // Broadcast when device trust status changes
            if (isset($changes['is_trusted'])) {
                $action = $device->is_trusted ? 'trusted' : 'untrusted';
                broadcast(new DeviceEvent($device, $action));
                broadcast(new SecurityEvent(
                    $device->user_id,
                    'device_' . $action,
                    "Device {$action}: {$device->device_name}",
                    [
                        'device_name' => $device->device_name,
                        'device_type' => $device->device_type,
                        'is_trusted' => $device->is_trusted,
                    ],
                    $device->is_trusted ? 'info' : 'warning'
                ));
            } else {
                broadcast(new DeviceEvent($device, 'updated'));
            }
        });

        static::deleting(function ($device) {
            broadcast(new DeviceEvent($device, 'removed'));
            broadcast(new SecurityEvent(
                $device->user_id,
                'device_removed',
                "Device removed: {$device->device_name}",
                [
                    'device_name' => $device->device_name,
                    'device_type' => $device->device_type,
                ],
                'warning'
            ));
        });
    }
}
