<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdminSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'login_at',
        'last_activity',
        'logout_at',
        'status',
        'login_method',
        'security_flags'
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'last_activity' => 'datetime', 
        'logout_at' => 'datetime',
        'security_flags' => 'array'
    ];

    /**
     * Get the user that owns the session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for recent sessions
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if session is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Get formatted location
     */
    public function getLocationAttribute()
    {
        if (!$this->location_data) {
            return 'ไม่ทราบตำแหน่ง';
        }

        $location = [];
        if (isset($this->location_data['city'])) {
            $location[] = $this->location_data['city'];
        }
        if (isset($this->location_data['country'])) {
            $location[] = $this->location_data['country'];
        }

        return empty($location) ? 'ไม่ทราบตำแหน่ง' : implode(', ', $location);
    }

    /**
     * Get browser name from user agent
     */
    public function getBrowserAttribute()
    {
        $userAgent = $this->user_agent;
        
        if (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        } elseif (strpos($userAgent, 'Opera') !== false) {
            return 'Opera';
        } else {
            return 'Other';
        }
    }

    /**
     * Get OS from user agent
     */
    public function getOperatingSystemAttribute()
    {
        $userAgent = $this->user_agent;
        
        if (strpos($userAgent, 'Windows NT 10') !== false) {
            return 'Windows 10';
        } elseif (strpos($userAgent, 'Windows NT') !== false) {
            return 'Windows';
        } elseif (strpos($userAgent, 'Mac OS X') !== false) {
            return 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            return 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            return 'Android';
        } elseif (strpos($userAgent, 'iPhone') !== false) {
            return 'iOS';
        } else {
            return 'Other';
        }
    }

    /**
     * Terminate session
     */
    public function terminate()
    {
        $this->update([
            'status' => 'terminated',
            'last_activity' => now(),
            'logout_at' => now()
        ]);
    }

    /**
     * Update last activity
     */
    public function updateActivity()
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Check if session has security flags
     */
    public function hasSecurityFlag($flag)
    {
        return in_array($flag, $this->security_flags ?? []);
    }

    /**
     * Add security flag
     */
    public function addSecurityFlag($flag)
    {
        $flags = $this->security_flags ?? [];
        if (!in_array($flag, $flags)) {
            $flags[] = $flag;
            $this->update(['security_flags' => $flags]);
        }
    }

    /**
     * Get duration of session
     */
    public function getDurationAttribute()
    {
        $end = ($this->status === 'active') ? now() : $this->last_activity;
        return $this->login_at ? $this->login_at->diffForHumans($end, true) : 'N/A';
    }
}
