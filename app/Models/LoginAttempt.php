<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username_attempted',
        'status',
        'failure_reason',
        'ip_address',
        'country_code',
        'country_name',
        'city',
        'region',
        'isp',
        'latitude',
        'longitude',
        'user_agent',
        'browser_name',
        'browser_version',
        'operating_system',
        'device_type',
        'device_fingerprint',
        'attempted_at',
        'time_of_day',
        'day_of_week',
        'time_since_last_attempt',
        'typing_speed',
        'mouse_patterns',
        'form_completion_time',
        'risk_score',
        'risk_factors',
        'is_suspicious',
        'alert_level',
        'security_actions',
        'admin_notified',
        'investigated_at',
        'investigated_by',
        'session_id',
        'request_headers',
        'referer'
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
        'investigated_at' => 'datetime',
        'is_suspicious' => 'boolean',
        'admin_notified' => 'boolean',
        'risk_score' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'risk_factors' => 'array',
        'security_actions' => 'array',
        'mouse_patterns' => 'array',
        'request_headers' => 'array'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    public function scopeHighRisk($query)
    {
        return $query->where('risk_score', '>=', 70);
    }

    public function scopeFromCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('attempted_at', '>=', now()->subHours($hours));
    }

    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeByDevice($query, string $fingerprint)
    {
        return $query->where('device_fingerprint', $fingerprint);
    }

    // Static Analysis Methods
    public static function analyzeUserPattern(int $userId, int $days = 30): array
    {
        $attempts = static::where('user_id', $userId)
                          ->where('attempted_at', '>=', now()->subDays($days))
                          ->orderBy('attempted_at')
                          ->get();

        return [
            'total_attempts' => $attempts->count(),
            'success_rate' => $attempts->count() > 0 
                ? round($attempts->where('status', 'success')->count() / $attempts->count() * 100, 2)
                : 0,
            'common_locations' => $attempts->groupBy('city')
                                         ->map->count()
                                         ->sortDesc()
                                         ->take(5),
            'common_devices' => $attempts->groupBy('device_type')
                                       ->map->count()
                                       ->sortDesc(),
            'time_patterns' => $attempts->groupBy('time_of_day')
                                      ->map->count()
                                      ->sortDesc(),
            'suspicious_count' => $attempts->where('is_suspicious', true)->count(),
            'avg_risk_score' => round($attempts->avg('risk_score'), 2)
        ];
    }

    public static function detectAnomalies(int $userId, array $currentAttempt): array
    {
        $anomalies = [];
        
        // ดึงข้อมูลประวัติ 30 วันล่าสุด
        $historicalData = static::analyzeUserPattern($userId, 30);
        
        // ตรวจสอบ Location Anomaly
        if (isset($currentAttempt['country_code'])) {
            $recentCountries = static::where('user_id', $userId)
                                   ->where('attempted_at', '>=', now()->subDays(7))
                                   ->where('status', 'success')
                                   ->distinct('country_code')
                                   ->pluck('country_code')
                                   ->toArray();
            
            if (!in_array($currentAttempt['country_code'], $recentCountries)) {
                $anomalies[] = 'new_country';
            }
        }
        
        // ตรวจสอบ Time Anomaly
        $currentHour = (int)$currentAttempt['time_of_day'];
        $commonHours = static::where('user_id', $userId)
                           ->where('attempted_at', '>=', now()->subDays(30))
                           ->where('status', 'success')
                           ->selectRaw('HOUR(time_of_day) as hour, count(*) as count')
                           ->groupBy('hour')
                           ->orderBy('count', 'desc')
                           ->limit(3)
                           ->pluck('hour')
                           ->toArray();
        
        if (!empty($commonHours) && !in_array($currentHour, $commonHours)) {
            $anomalies[] = 'unusual_time';
        }
        
        // ตรวจสอบ Device Anomaly
        if (isset($currentAttempt['device_fingerprint'])) {
            $knownDevices = static::where('user_id', $userId)
                                 ->where('attempted_at', '>=', now()->subDays(30))
                                 ->where('status', 'success')
                                 ->distinct('device_fingerprint')
                                 ->pluck('device_fingerprint')
                                 ->toArray();
            
            if (!in_array($currentAttempt['device_fingerprint'], $knownDevices)) {
                $anomalies[] = 'new_device';
            }
        }
        
        return $anomalies;
    }

    public static function calculateRiskScore(array $attemptData, array $anomalies = []): float
    {
        $score = 0;
        
        // Base risk factors
        if (in_array('new_country', $anomalies)) $score += 25;
        if (in_array('new_device', $anomalies)) $score += 20;
        if (in_array('unusual_time', $anomalies)) $score += 15;
        
        // IP-based risk
        $ipAttempts = static::where('ip_address', $attemptData['ip_address'] ?? '')
                           ->where('attempted_at', '>=', now()->subHours(24))
                           ->count();
        if ($ipAttempts > 10) $score += 20;
        if ($ipAttempts > 50) $score += 30;
        
        // Failed attempts in short time
        if (isset($attemptData['user_id'])) {
            $recentFailed = static::where('user_id', $attemptData['user_id'])
                                 ->where('status', 'failed')
                                 ->where('attempted_at', '>=', now()->subMinutes(30))
                                 ->count();
            $score += min($recentFailed * 5, 30);
        }
        
        // Behavioral factors
        if (isset($attemptData['typing_speed']) && $attemptData['typing_speed'] > 200) {
            $score += 10; // Very fast typing might be automated
        }
        
        if (isset($attemptData['form_completion_time']) && $attemptData['form_completion_time'] < 3) {
            $score += 15; // Too fast form completion
        }
        
        return min($score, 100); // Cap at 100
    }

    public static function getAlertLevel(float $riskScore): string
    {
        if ($riskScore >= 80) return 'critical';
        if ($riskScore >= 60) return 'high';
        if ($riskScore >= 40) return 'medium';
        return 'low';
    }

    // Instance Methods
    public function markAsInvestigated(int $investigatorId): bool
    {
        return $this->update([
            'investigated_at' => now(),
            'investigated_by' => $investigatorId
        ]);
    }

    public function addSecurityAction(string $action, array $details = []): bool
    {
        $actions = $this->security_actions ?? [];
        $actions[] = [
            'action' => $action,
            'details' => $details,
            'timestamp' => now()->toISOString()
        ];
        
        return $this->update(['security_actions' => $actions]);
    }

    public function getLocationString(): string
    {
        $parts = array_filter([$this->city, $this->region, $this->country_name]);
        return implode(', ', $parts) ?: 'Unknown Location';
    }

    public function getDeviceString(): string
    {
        $parts = array_filter([$this->browser_name, $this->operating_system, $this->device_type]);
        return implode(' on ', $parts) ?: 'Unknown Device';
    }

    public function isSuspiciousActivity(): bool
    {
        return $this->is_suspicious || $this->risk_score >= 60;
    }

    public function getTimeSinceLastAttemptHuman(): string
    {
        if (!$this->time_since_last_attempt) {
            return 'First attempt';
        }
        
        $seconds = $this->time_since_last_attempt;
        if ($seconds < 60) return "{$seconds} seconds";
        if ($seconds < 3600) return round($seconds / 60) . " minutes";
        return round($seconds / 3600) . " hours";
    }
}
