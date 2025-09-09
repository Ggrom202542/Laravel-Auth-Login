<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'device_name',
        'platform',
        'browser',
        'location_country',
        'location_city',
        'location_lat',
        'location_lng',
        'last_activity',
        'login_at',
        'logout_at',
        'expires_at',
        'is_current',
        'is_trusted',
        'is_active',
        'is_suspicious',
        'suspicious_reason',
        'suspicious_detected_at',
        'payload'
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'expires_at' => 'datetime',
        'suspicious_detected_at' => 'datetime',
        'is_current' => 'boolean',
        'is_trusted' => 'boolean',
        'is_active' => 'boolean',
        'is_suspicious' => 'boolean',
        'payload' => 'array'
    ];

    /**
     * Relationship: Session belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Active sessions only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Current session
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope: Trusted devices
     */
    public function scopeTrusted($query)
    {
        return $query->where('is_trusted', true);
    }

    /**
     * Scope: Recent sessions (within last 30 days)
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('last_activity', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if session is online (active within last 5 minutes)
     */
    public function isOnline(): bool
    {
        return $this->last_activity >= Carbon::now()->subMinutes(5);
    }

    /**
     * Get human readable device info
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = array_filter([
            $this->browser,
            $this->platform,
            $this->device_type
        ]);
        
        return implode(' • ', $parts) ?: 'Unknown Device';
    }

    /**
     * Get location string
     */
    public function getLocationAttribute(): string
    {
        if ($this->location_city && $this->location_country) {
            return $this->location_city . ', ' . $this->location_country;
        }
        
        return $this->location_country ?: 'Unknown Location';
    }

    /**
     * Get session duration
     */
    public function getDurationAttribute(): string
    {
        return $this->login_at->diffForHumans($this->last_activity, true);
    }

    /**
     * Terminate this session
     */
    public function terminate($reason = null, $performedBy = null): void
    {
        $this->update([
            'is_active' => false,
            'is_current' => false,
            'logout_at' => now()
        ]);

        // Log the termination
        SessionLog::create([
            'session_id' => $this->session_id,
            'user_id' => $this->user_id,
            'action' => $performedBy ? 'force_logout' : 'logout',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => $performedBy,
            'reason' => $reason,
            'performed_at' => now()
        ]);
    }

    /**
     * Update session activity
     */
    public function updateActivity(): void
    {
        $this->update([
            'last_activity' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * ดึง icon ของอุปกรณ์
     */
    public function getDeviceIcon(): string
    {
        return match(strtolower($this->device_type)) {
            'mobile' => 'bi-phone',
            'tablet' => 'bi-tablet', 
            'desktop' => 'bi-display',
            default => 'bi-laptop'
        };
    }

    /**
     * Set device as trusted/untrusted
     */
    public function setTrusted(bool $trusted = true): bool
    {
        return $this->update([
            'is_trusted' => $trusted,
            'trusted_at' => $trusted ? now() : null
        ]);
    }

    /**
     * Mark session as suspicious
     */
    public function markAsSuspicious(string $reason = null): bool
    {
        return $this->update([
            'is_suspicious' => true,
            'suspicious_reason' => $reason,
            'suspicious_detected_at' => now()
        ]);
    }

    /**
     * Clear suspicious flag
     */
    public function clearSuspicious(): bool
    {
        return $this->update([
            'is_suspicious' => false,
            'suspicious_reason' => null,
            'suspicious_detected_at' => null
        ]);
    }

    /**
     * Scope: Suspicious sessions
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }
}
