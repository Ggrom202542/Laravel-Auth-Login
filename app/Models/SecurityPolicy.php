<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SecurityPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_name',
        'description',
        'policy_type',
        'policy_rules',
        'applies_to',
        'is_active',
        'effective_from',
        'expires_at',
        'created_by'
    ];

    protected $casts = [
        'policy_rules' => 'array',
        'is_active' => 'boolean',
        'effective_from' => 'datetime',
        'expires_at' => 'datetime',
        'priority_order' => 'integer'
    ];

    /**
     * Get the user this policy applies to (if user-specific)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the creator of this policy
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active policies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for effective policies (within date range)
     */
    public function scopeEffective($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('effective_from')
                          ->orWhere('effective_from', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for policies by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('policy_type', $type);
    }

    /**
     * Scope for policies that apply to specific user
     */
    public function scopeForUser($query, $userId, $userRole = null)
    {
        return $query->where(function($q) use ($userId, $userRole) {
            $q->where('applies_to', 'all')
              ->orWhere(function($subQ) use ($userId) {
                  $subQ->where('applies_to', 'user')
                       ->where('user_id', $userId);
              });
            
            if ($userRole) {
                $q->orWhere(function($subQ) use ($userRole) {
                    $subQ->where('applies_to', 'role')
                         ->where('role', $userRole);
                });
            }
        });
    }

    /**
     * Check if policy is currently effective
     */
    public function isEffective()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->effective_from && $this->effective_from->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get policy rule by key
     */
    public function getRule($key, $default = null)
    {
        return data_get($this->policy_rules, $key, $default);
    }

    /**
     * Set policy rule
     */
    public function setRule($key, $value)
    {
        $rules = $this->policy_rules ?? [];
        data_set($rules, $key, $value);
        $this->update(['policy_rules' => $rules]);
    }

    /**
     * Check if policy has specific rule
     */
    public function hasRule($key)
    {
        return data_get($this->policy_rules, $key) !== null;
    }

    /**
     * Validate IP address against policy
     */
    public function validateIpAddress($ipAddress)
    {
        if ($this->policy_type !== 'ip_restriction') {
            return true;
        }

        $allowedIps = $this->getRule('allowed_ips', []);
        $blockedIps = $this->getRule('blocked_ips', []);

        // Check blocked IPs first
        foreach ($blockedIps as $blockedIp) {
            if ($this->ipMatches($ipAddress, $blockedIp)) {
                return false;
            }
        }

        // If no allowed IPs specified, allow all (except blocked)
        if (empty($allowedIps)) {
            return true;
        }

        // Check allowed IPs
        foreach ($allowedIps as $allowedIp) {
            if ($this->ipMatches($ipAddress, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP matches pattern (supports CIDR notation)
     */
    private function ipMatches($ip, $pattern)
    {
        // Exact match
        if ($ip === $pattern) {
            return true;
        }

        // CIDR notation
        if (strpos($pattern, '/') !== false) {
            list($subnet, $mask) = explode('/', $pattern);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ipLong = ip2long($ip);
                $subnetLong = ip2long($subnet);
                $mask = -1 << (32 - $mask);
                
                return ($ipLong & $mask) === ($subnetLong & $mask);
            }
        }

        // Wildcard matching (e.g., 192.168.1.*)
        if (strpos($pattern, '*') !== false) {
            $pattern = str_replace('*', '.*', $pattern);
            return preg_match("/^{$pattern}$/", $ip);
        }

        return false;
    }

    /**
     * Check if time is within allowed hours
     */
    public function validateTimeRestriction()
    {
        if ($this->policy_type !== 'time_restriction') {
            return true;
        }

        $allowedHours = $this->getRule('allowed_hours');
        if (!$allowedHours) {
            return true;
        }

        $currentHour = now()->hour;
        
        if (isset($allowedHours['start']) && isset($allowedHours['end'])) {
            $start = $allowedHours['start'];
            $end = $allowedHours['end'];
            
            if ($start <= $end) {
                return $currentHour >= $start && $currentHour <= $end;
            } else {
                // Overnight range (e.g., 22:00 to 06:00)
                return $currentHour >= $start || $currentHour <= $end;
            }
        }

        return true;
    }

    /**
     * Get human-readable policy description
     */
    public function getFormattedDescription()
    {
        $description = $this->description;
        
        switch ($this->policy_type) {
            case 'ip_restriction':
                $allowedIps = $this->getRule('allowed_ips', []);
                $blockedIps = $this->getRule('blocked_ips', []);
                
                if (!empty($allowedIps)) {
                    $description .= " (อนุญาต IP: " . implode(', ', $allowedIps) . ")";
                }
                if (!empty($blockedIps)) {
                    $description .= " (บล็อก IP: " . implode(', ', $blockedIps) . ")";
                }
                break;
                
            case 'session_timeout':
                $timeout = $this->getRule('timeout_minutes');
                if ($timeout) {
                    $description .= " ({$timeout} นาที)";
                }
                break;
                
            case 'two_factor_required':
                $description .= " (บังคับใช้ 2FA)";
                break;
        }

        return $description;
    }

    /**
     * Check if policy applies to user
     */
    public function appliesTo($user)
    {
        if (!$this->isEffective()) {
            return false;
        }

        switch ($this->applies_to) {
            case 'all':
                return true;
                
            case 'user':
                return $this->user_id == $user->id;
                
            case 'role':
                return $this->role == $user->role;
                
            default:
                return false;
        }
    }
}
