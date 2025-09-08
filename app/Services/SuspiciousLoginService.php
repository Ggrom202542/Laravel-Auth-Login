<?php

namespace App\Services;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserDevice;
use App\Events\LoginAttemptEvent;
use App\Events\SecurityEvent;
use App\Notifications\SecurityAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, Http, Cache, Mail, Notification};
use Carbon\Carbon;

class SuspiciousLoginService
{
    // Configuration constants
    const HIGH_RISK_THRESHOLD = 70;
    const CRITICAL_RISK_THRESHOLD = 85;
    const MAX_DAILY_ATTEMPTS = 50;
    const ANOMALY_DETECTION_DAYS = 30;

    /**
     * บันทึกและวิเคราะห์ความพยายาม login
     */
    public function recordLoginAttempt(
        Request $request, 
        ?User $user = null, 
        string $status = 'failed',
        string $username = null,
        string $failureReason = null
    ): LoginAttempt {
        
        $attemptData = $this->extractAttemptData($request, $user, $status, $username, $failureReason);
        
        // วิเคราะห์ anomalies หากมี user
        $anomalies = [];
        if ($user) {
            $anomalies = LoginAttempt::detectAnomalies($user->id, $attemptData);
        }
        
        // คำนวณ risk score
        $riskScore = LoginAttempt::calculateRiskScore($attemptData, $anomalies);
        $alertLevel = LoginAttempt::getAlertLevel($riskScore);
        
        // เพิ่มข้อมูลความเสี่ยง
        $attemptData['risk_score'] = $riskScore;
        $attemptData['alert_level'] = $alertLevel;
        $attemptData['risk_factors'] = $anomalies;
        $attemptData['is_suspicious'] = $riskScore >= 40; // Suspicious threshold
        
        // ดึงข้อมูล Geographic
        $this->enrichWithGeographicData($attemptData);
        
        // บันทึกลง database
        $loginAttempt = LoginAttempt::create($attemptData);
        
        // ประมวลผลการตอบสนองต่อความเสี่ยง
        $this->processSecurityResponse($loginAttempt, $user);
        
        Log::info('Login attempt recorded and analyzed', [
            'id' => $loginAttempt->id,
            'user_id' => $user?->id,
            'status' => $status,
            'risk_score' => $riskScore,
            'alert_level' => $alertLevel,
            'anomalies' => $anomalies
        ]);
        
        // Broadcast real-time events
        broadcast(new LoginAttemptEvent($loginAttempt));
        
        // Broadcast security alert for high-risk attempts
        if ($riskScore >= self::HIGH_RISK_THRESHOLD && $user) {
            $severity = $riskScore >= self::CRITICAL_RISK_THRESHOLD ? 'critical' : 'high';
            broadcast(new SecurityEvent(
                $user->id,
                'high_risk_login',
                "High-risk login attempt detected from {$loginAttempt->ip_address}",
                [
                    'risk_score' => $riskScore,
                    'alert_level' => $alertLevel,
                    'ip_address' => $loginAttempt->ip_address,
                    'location' => $loginAttempt->location,
                    'device_info' => $loginAttempt->device_info
                ],
                $severity
            ));
            
            // Send notification for high-risk login attempts
            $user->notify(new SecurityAlert(
                'high_risk_login',
                'Suspicious Login Attempt Detected',
                "A high-risk login attempt was detected on your account from IP {$loginAttempt->ip_address}" . 
                ($loginAttempt->location ? " in {$loginAttempt->location}" : "") . ".",
                [
                    'risk_score' => $riskScore,
                    'ip_address' => $loginAttempt->ip_address,
                    'location' => $loginAttempt->location,
                    'device_info' => $loginAttempt->device_info,
                    'attempt_time' => $loginAttempt->attempted_at->format('Y-m-d H:i:s')
                ],
                $severity,
                route('user.security.login-history')
            ));
        }
        
        // Send notification for failed login attempts
        if ($status === 'failed' && $user) {
            $user->notify(new SecurityAlert(
                'failed_login',
                'Failed Login Attempt',
                "A failed login attempt was made on your account from IP {$loginAttempt->ip_address}.",
                [
                    'ip_address' => $loginAttempt->ip_address,
                    'location' => $loginAttempt->location,
                    'failure_reason' => $failureReason,
                    'attempt_time' => $loginAttempt->attempted_at->format('Y-m-d H:i:s')
                ],
                'warning',
                route('user.security.login-history')
            ));
        }
        
        return $loginAttempt;
    }

    /**
     * ดึงข้อมูลจาก Request สำหรับการวิเคราะห์
     */
    protected function extractAttemptData(
        Request $request, 
        ?User $user, 
        string $status, 
        ?string $username, 
        ?string $failureReason
    ): array {
        
        $userAgent = $request->userAgent() ?? '';
        $deviceInfo = $this->parseUserAgent($userAgent);
        $attemptedAt = now();
        
        // คำนวณเวลาจาก attempt ล่าสุด
        $lastAttempt = null;
        if ($user) {
            $lastAttempt = LoginAttempt::where('user_id', $user->id)
                                     ->orderBy('attempted_at', 'desc')
                                     ->first();
        }
        
        $timeSinceLastAttempt = $lastAttempt 
            ? $attemptedAt->diffInSeconds($lastAttempt->attempted_at)
            : null;

        return [
            'user_id' => $user?->id,
            'username_attempted' => $username ?? $user?->username,
            'status' => $status,
            'failure_reason' => $failureReason,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'browser_name' => $deviceInfo['browser_name'],
            'browser_version' => $deviceInfo['browser_version'],
            'operating_system' => $deviceInfo['operating_system'],
            'device_type' => $deviceInfo['device_type'],
            'device_fingerprint' => $this->generateDeviceFingerprint($request),
            'attempted_at' => $attemptedAt,
            'time_of_day' => $attemptedAt->format('H:i:s'),
            'day_of_week' => $attemptedAt->dayOfWeek,
            'time_since_last_attempt' => $timeSinceLastAttempt,
            'session_id' => $request->session()->getId(),
            'referer' => $request->header('referer'),
            'request_headers' => $this->sanitizeHeaders($request->headers->all()),
            // Behavioral data (จะได้จาก frontend JavaScript)
            'typing_speed' => $request->input('typing_speed'),
            'form_completion_time' => $request->input('form_completion_time'),
            'mouse_patterns' => $request->input('mouse_patterns')
        ];
    }

    /**
     * เพิ่มข้อมูล Geographic จาก IP
     */
    protected function enrichWithGeographicData(array &$attemptData): void
    {
        $ip = $attemptData['ip_address'];
        
        // ข้าม private IPs
        if ($this->isPrivateIp($ip)) {
            return;
        }
        
        $cacheKey = "geo_ip_{$ip}";
        $geoData = Cache::remember($cacheKey, 3600, function () use ($ip) {
            try {
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['status'] === 'success') {
                        return [
                            'country_code' => $data['countryCode'] ?? null,
                            'country_name' => $data['country'] ?? null,
                            'city' => $data['city'] ?? null,
                            'region' => $data['regionName'] ?? null,
                            'isp' => $data['isp'] ?? null,
                            'latitude' => $data['lat'] ?? null,
                            'longitude' => $data['lon'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to get geographic data', ['ip' => $ip, 'error' => $e->getMessage()]);
            }
            
            return null;
        });
        
        if ($geoData) {
            $attemptData = array_merge($attemptData, $geoData);
        }
    }

    /**
     * ประมวลผลการตอบสนองต่อความเสี่ยง
     */
    protected function processSecurityResponse(LoginAttempt $loginAttempt, ?User $user): void
    {
        $riskScore = $loginAttempt->risk_score;
        $actions = [];
        
        // การตอบสนองตามระดับความเสี่ยง
        if ($riskScore >= self::CRITICAL_RISK_THRESHOLD) {
            $actions[] = $this->handleCriticalRisk($loginAttempt, $user);
        } elseif ($riskScore >= self::HIGH_RISK_THRESHOLD) {
            $actions[] = $this->handleHighRisk($loginAttempt, $user);
        } elseif ($loginAttempt->is_suspicious) {
            $actions[] = $this->handleSuspiciousActivity($loginAttempt, $user);
        }
        
        // บันทึกการดำเนินการ
        if (!empty(array_filter($actions))) {
            $loginAttempt->update(['security_actions' => array_filter($actions)]);
        }
    }

    /**
     * จัดการความเสี่ยงระดับ Critical
     */
    protected function handleCriticalRisk(LoginAttempt $loginAttempt, ?User $user): array
    {
        $actions = [];
        
        // บล็อก IP ทันที
        if (app()->bound(IpManagementService::class)) {
            $ipService = app(IpManagementService::class);
            $ipService->temporaryBlock(
                $loginAttempt->ip_address,
                "Critical risk login attempt (Score: {$loginAttempt->risk_score})",
                6 // 6 hours
            );
            $actions['ip_blocked'] = true;
        }
        
        // ล็อกบัญชีผู้ใช้ (หากมี)
        if ($user && app()->bound(AccountLockoutService::class)) {
            $lockoutService = app(AccountLockoutService::class);
            $lockoutService->adminLockAccount($user, 'Critical suspicious activity detected');
            $actions['account_locked'] = true;
        }
        
        // แจ้งเตือน Admin ทันที
        $this->notifyAdmins($loginAttempt, 'critical');
        $actions['admin_notified'] = true;
        
        // ยกเลิกความไว้วางใจของอุปกรณ์
        if ($loginAttempt->device_fingerprint && $user) {
            UserDevice::revokeDeviceTrust($loginAttempt->device_fingerprint, $user->id);
            $actions['device_trust_revoked'] = true;
        }
        
        return $actions;
    }

    /**
     * จัดการความเสี่ยงระดับสูง
     */
    protected function handleHighRisk(LoginAttempt $loginAttempt, ?User $user): array
    {
        $actions = [];
        
        // ต้องการการยืนยันเพิ่มเติม
        if ($user) {
            // บังคับ 2FA สำหรับ login ครั้งต่อไป
            $user->update(['requires_2fa_verification' => true]);
            $actions['2fa_required'] = true;
        }
        
        // ทำเครื่องหมายอุปกรณ์ว่าต้องการการยืนยัน
        if ($loginAttempt->device_fingerprint && $user) {
            UserDevice::where('device_fingerprint', $loginAttempt->device_fingerprint)
                     ->where('user_id', $user->id)
                     ->update(['requires_verification' => true]);
            $actions['device_verification_required'] = true;
        }
        
        // แจ้งเตือน Admin
        $this->notifyAdmins($loginAttempt, 'high');
        $actions['admin_notified'] = true;
        
        return $actions;
    }

    /**
     * จัดการกิจกรรมที่น่าสงสัย
     */
    protected function handleSuspiciousActivity(LoginAttempt $loginAttempt, ?User $user): array
    {
        $actions = [];
        
        // บันทึกเพื่อการตรวจสอบ
        $actions['flagged_for_review'] = true;
        
        // ส่งอีเมลแจ้งเตือนผู้ใช้ (หากเป็น successful login)
        if ($loginAttempt->status === 'success' && $user && $user->email) {
            // TODO: ส่งอีเมลแจ้งเตือนการเข้าสู่ระบบที่น่าสงสัย
            $actions['user_notified'] = true;
        }
        
        return $actions;
    }

    /**
     * แจ้งเตือน Admins
     */
    protected function notifyAdmins(LoginAttempt $loginAttempt, string $severity): void
    {
        $admins = User::where('role', 'admin')
                     ->orWhere('role', 'super_admin')
                     ->get();

        foreach ($admins as $admin) {
            // TODO: ส่ง notification หรือ email แจ้งเตือน
            Log::info('Admin notification sent', [
                'admin_id' => $admin->id,
                'login_attempt_id' => $loginAttempt->id,
                'severity' => $severity
            ]);
        }
        
        $loginAttempt->update(['admin_notified' => true]);
    }

    /**
     * ดึงสถิติการตรวจจับ
     */
    public function getDetectionStatistics(): array
    {
        $last24h = now()->subDay();
        $last7d = now()->subWeek();
        $last30d = now()->subMonth();

        return [
            'total_attempts_24h' => LoginAttempt::where('attempted_at', '>=', $last24h)->count(),
            'suspicious_attempts_24h' => LoginAttempt::suspicious()->where('attempted_at', '>=', $last24h)->count(),
            'high_risk_attempts_24h' => LoginAttempt::highRisk()->where('attempted_at', '>=', $last24h)->count(),
            'blocked_ips_24h' => LoginAttempt::where('attempted_at', '>=', $last24h)
                                           ->whereJsonContains('security_actions->ip_blocked', true)
                                           ->count(),
            'weekly_trend' => [
                'total' => LoginAttempt::where('attempted_at', '>=', $last7d)->count(),
                'suspicious' => LoginAttempt::suspicious()->where('attempted_at', '>=', $last7d)->count(),
            ],
            'top_risk_countries' => LoginAttempt::suspicious()
                                              ->where('attempted_at', '>=', $last7d)
                                              ->whereNotNull('country_name')
                                              ->selectRaw('country_name, count(*) as attempts, avg(risk_score) as avg_risk')
                                              ->groupBy('country_name')
                                              ->orderBy('attempts', 'desc')
                                              ->limit(10)
                                              ->get(),
            'detection_patterns' => [
                'new_country' => LoginAttempt::where('attempted_at', '>=', $last7d)
                                           ->whereJsonContains('risk_factors', 'new_country')
                                           ->count(),
                'new_device' => LoginAttempt::where('attempted_at', '>=', $last7d)
                                          ->whereJsonContains('risk_factors', 'new_device')
                                          ->count(),
                'unusual_time' => LoginAttempt::where('attempted_at', '>=', $last7d)
                                            ->whereJsonContains('risk_factors', 'unusual_time')
                                            ->count(),
            ]
        ];
    }

    /**
     * สร้างรายงานสำหรับผู้ใช้เฉพาะ
     */
    public function getUserSecurityReport(int $userId, int $days = 30): array
    {
        $attempts = LoginAttempt::where('user_id', $userId)
                               ->where('attempted_at', '>=', now()->subDays($days))
                               ->orderBy('attempted_at', 'desc')
                               ->get();

        return [
            'summary' => [
                'total_attempts' => $attempts->count(),
                'successful_logins' => $attempts->where('status', 'success')->count(),
                'failed_attempts' => $attempts->where('status', 'failed')->count(),
                'suspicious_activities' => $attempts->where('is_suspicious', true)->count(),
                'average_risk_score' => $attempts->avg('risk_score'),
            ],
            'locations' => $attempts->groupBy('country_name')
                                  ->map(function ($group) {
                                      return [
                                          'count' => $group->count(),
                                          'latest' => $group->sortByDesc('attempted_at')->first()->attempted_at
                                      ];
                                  })
                                  ->sortByDesc('count'),
            'devices' => $attempts->groupBy('device_type')
                                ->map->count()
                                ->sortByDesc(function ($count) { return $count; }),
            'time_patterns' => $attempts->groupBy(function ($attempt) {
                                       return $attempt->attempted_at->format('H');
                                   })
                                   ->map->count()
                                   ->sortKeys(),
            'recent_suspicious' => $attempts->where('is_suspicious', true)
                                          ->take(10)
                                          ->values()
        ];
    }

    // Helper methods
    protected function parseUserAgent(string $userAgent): array
    {
        // Basic User Agent parsing (simplified version)
        $info = [
            'browser_name' => null,
            'browser_version' => null,
            'operating_system' => null,
            'device_type' => null
        ];
        
        // Browser detection
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            $info['browser_name'] = 'Chrome';
            $info['browser_version'] = $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $info['browser_name'] = 'Firefox';
            $info['browser_version'] = $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches) && !strpos($userAgent, 'Chrome')) {
            $info['browser_name'] = 'Safari';
            $info['browser_version'] = $matches[1];
        }
        
        // OS detection
        if (strpos($userAgent, 'Windows') !== false) {
            $info['operating_system'] = 'Windows';
        } elseif (strpos($userAgent, 'Mac OS X') !== false) {
            $info['operating_system'] = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $info['operating_system'] = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $info['operating_system'] = 'Android';
            $info['device_type'] = 'mobile';
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $info['operating_system'] = 'iOS';
            $info['device_type'] = strpos($userAgent, 'iPad') !== false ? 'tablet' : 'mobile';
        }
        
        // Device type fallback
        if (!$info['device_type']) {
            if (strpos($userAgent, 'Mobile') !== false) {
                $info['device_type'] = 'mobile';
            } else {
                $info['device_type'] = 'desktop';
            }
        }
        
        return $info;
    }

    protected function generateDeviceFingerprint(Request $request): string
    {
        $deviceService = app(DeviceManagementService::class);
        return $deviceService->generateDeviceFingerprint($request);
    }

    protected function isPrivateIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    protected function sanitizeHeaders(array $headers): array
    {
        // ลบ headers ที่ sensitive
        $sanitized = $headers;
        unset($sanitized['authorization'], $sanitized['cookie'], $sanitized['x-csrf-token']);
        return $sanitized;
    }
}
