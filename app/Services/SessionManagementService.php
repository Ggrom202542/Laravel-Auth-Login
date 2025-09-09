<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use App\Models\SessionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log, Session};
use Jenssegers\Agent\Agent;
use Carbon\Carbon;

class SessionManagementService
{
    protected $agent;

    public function __construct()
    {
        $this->agent = new Agent();
    }

    /**
     * สร้าง session ใหม่เมื่อผู้ใช้ login
     */
    public function createSession(User $user, Request $request): UserSession
    {
        // ปิด current session เดิม
        $this->markOtherSessionsAsNotCurrent($user->id);

        // สร้าง session ใหม่
        $session = UserSession::create([
            'session_id' => Session::getId(),
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType(),
            'device_name' => $this->agent->browser(),
            'platform' => $this->agent->platform(),
            'browser' => $this->agent->browser(),
            'location_country' => $this->getLocationInfo($request->ip())['country'] ?? null,
            'location_city' => $this->getLocationInfo($request->ip())['city'] ?? null,
            'last_activity' => now(),
            'login_at' => now(),
            'expires_at' => $this->getSessionExpiry(),
            'is_current' => true,
            'is_trusted' => $this->isDeviceTrusted($user, $request),
            'is_active' => true
        ]);

        // บันทึก log
        $this->logActivity('login', $session, $request);

        return $session;
    }

    /**
     * อัปเดต session activity
     */
    public function updateSessionActivity(string $sessionId, Request $request): void
    {
        $session = UserSession::where('session_id', $sessionId)
                              ->where('is_active', true)
                              ->first();

        if ($session) {
            $session->updateActivity();
        }
    }

    /**
     * ปิด session (logout)
     */
    public function terminateSession(string $sessionId, string $reason = null, int $performedBy = null): bool
    {
        $session = UserSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            return false;
        }

        $session->terminate($reason, $performedBy);
        return true;
    }

    /**
     * ปิด sessions อื่นของผู้ใช้ (logout from other devices)
     */
    public function terminateOtherSessions(int $userId, string $currentSessionId, string $reason = null): int
    {
        $sessions = UserSession::where('user_id', $userId)
                               ->where('session_id', '!=', $currentSessionId)
                               ->where('is_active', true)
                               ->get();

        $count = 0;
        foreach ($sessions as $session) {
            $session->terminate($reason, $userId);
            $count++;
        }

        return $count;
    }

    /**
     * ปิด session ทั้งหมดของผู้ใช้ (force logout by admin)
     */
    public function terminateAllUserSessions(int $userId, string $reason = null, int $performedBy = null): int
    {
        $sessions = UserSession::where('user_id', $userId)
                               ->where('is_active', true)
                               ->get();

        $count = 0;
        foreach ($sessions as $session) {
            $session->terminate($reason, $performedBy);
            $count++;
        }

        return $count;
    }

    /**
     * ดึง active sessions ของผู้ใช้
     */
    public function getUserActiveSessions(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return UserSession::where('user_id', $userId)
                          ->active()
                          ->orderBy('last_activity', 'desc')
                          ->get();
    }

    /**
     * ดึง session ปัจจุบันของผู้ใช้
     */
    public function getCurrentSession(int $userId): ?UserSession
    {
        return UserSession::where('user_id', $userId)
                          ->where('is_current', true)
                          ->where('is_active', true)
                          ->first();
    }

    /**
     * ดึงสถิติ sessions
     */
    public function getSessionStatistics(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        // Calculate average session duration
        $avgDurationResult = UserSession::where('created_at', '>=', $startDate)
            ->whereNotNull('login_at')
            ->whereNotNull('last_activity')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, login_at, last_activity)) as avg_minutes')
            ->first();
        
        $avgDuration = ($avgDurationResult && $avgDurationResult->avg_minutes) ? $avgDurationResult->avg_minutes : 0;

        // Calculate peak concurrent users (simplified - max users active in any day)
        $peakConcurrentResult = UserSession::where('created_at', '>=', $startDate)
            ->where('is_active', true)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderByDesc('count')
            ->first();
            
        $peakConcurrent = $peakConcurrentResult ? $peakConcurrentResult->count : 0;

        return [
            'total_sessions' => UserSession::where('created_at', '>=', $startDate)->count(),
            'active_sessions' => UserSession::active()->count(),
            'online_users' => UserSession::active()
                                         ->where('last_activity', '>=', Carbon::now()->subMinutes(5))
                                         ->distinct('user_id')
                                         ->count(),
            'trusted_devices' => UserSession::active()->trusted()->count(),
            'suspicious_sessions' => UserSession::where('is_suspicious', true)
                                               ->where('created_at', '>=', $startDate)
                                               ->count(),
            'unique_users' => UserSession::where('created_at', '>=', $startDate)
                                         ->distinct('user_id')
                                         ->count(),
            'avg_session_duration' => round($avgDuration, 1),
            'peak_concurrent_users' => $peakConcurrent,
            'platforms' => UserSession::where('created_at', '>=', $startDate)
                                      ->groupBy('platform')
                                      ->selectRaw('platform, COUNT(*) as count')
                                      ->pluck('count', 'platform')
                                      ->toArray(),
            'browsers' => UserSession::where('created_at', '>=', $startDate)
                                     ->groupBy('browser')
                                     ->selectRaw('browser, COUNT(*) as count')
                                     ->pluck('count', 'browser')
                                     ->toArray()
        ];
    }

    /**
     * ล้าง sessions ที่หมดอายุ
     */
    public function cleanupExpiredSessions(): int
    {
        $expiredSessions = UserSession::where('expires_at', '<', now())
                                      ->where('is_active', true)
                                      ->get();

        $count = 0;
        foreach ($expiredSessions as $session) {
            $session->update(['is_active' => false]);
            
            // บันทึก log
            SessionLog::create([
                'session_id' => $session->session_id,
                'user_id' => $session->user_id,
                'action' => 'expired',
                'performed_at' => now()
            ]);
            
            $count++;
        }

        return $count;
    }

    /**
     * ดึงประวัติ sessions ของผู้ใช้
     */
    public function getUserSessionHistory(int $userId, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return UserSession::where('user_id', $userId)
                          ->where('created_at', '>=', Carbon::now()->subDays($days))
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    /**
     * ดึง session logs
     */
    public function getSessionLogs(int $userId = null, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        $query = SessionLog::with(['user', 'performer'])
                           ->where('performed_at', '>=', Carbon::now()->subDays($days));

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('performed_at', 'desc')->get();
    }

    /**
     * Private: ปิด current session อื่นๆ
     */
    private function markOtherSessionsAsNotCurrent(int $userId): void
    {
        UserSession::where('user_id', $userId)
                   ->where('is_current', true)
                   ->update(['is_current' => false]);
    }

    /**
     * Private: ตรวจสอบอุปกรณ์ที่เชื่อถือได้
     */
    private function isDeviceTrusted(User $user, Request $request): bool
    {
        // ตรวจสอบจาก trusted devices เดิม
        $deviceFingerprint = $this->getDeviceFingerprint($request);
        
        return UserSession::where('user_id', $user->id)
                          ->where('is_trusted', true)
                          ->where('payload->device_fingerprint', $deviceFingerprint)
                          ->exists();
    }

    /**
     * Private: สร้าง device fingerprint
     */
    private function getDeviceFingerprint(Request $request): string
    {
        return md5($request->userAgent() . $request->ip());
    }

    /**
     * Private: ประเภทอุปกรณ์
     */
    private function getDeviceType(): string
    {
        if ($this->agent->isMobile()) {
            return 'mobile';
        } elseif ($this->agent->isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Private: กำหนดเวลาหมดอายุของ session
     */
    private function getSessionExpiry(): Carbon
    {
        // Default: 30 วัน สำหรับ session
        return Carbon::now()->addDays(30);
    }

    /**
     * Private: ดึงข้อมูลตำแหน่ง
     */
    private function getLocationInfo(string $ip): array
    {
        // สำหรับ demo ใช้ข้อมูลตัวอย่าง
        // ในการใช้งานจริงอาจใช้ service เช่น ipapi.com
        return [
            'country' => 'Thailand',
            'city' => 'Bangkok'
        ];
    }

    /**
     * Private: บันทึก activity log
     */
    private function logActivity(string $action, UserSession $session, Request $request): void
    {
        SessionLog::create([
            'session_id' => $session->session_id,
            'user_id' => $session->user_id,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'location_country' => $session->location_country,
            'location_city' => $session->location_city,
            'performed_at' => now()
        ]);
    }

    /**
     * ติดตาม session สำหรับ middleware
     */
    public function trackSession(User $user, string $ipAddress, string $userAgent): void
    {
        // ค้นหา session ที่มีอยู่หรือสร้างใหม่
        $session = UserSession::where('user_id', $user->id)
                             ->where('session_id', Session::getId())
                             ->first();

        if (!$session) {
            // สร้าง session ใหม่
            $session = UserSession::create([
                'session_id' => Session::getId(),
                'user_id' => $user->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_type' => $this->getDeviceType(),
                'device_name' => $this->agent->browser(),
                'platform' => $this->agent->platform(),
                'browser' => $this->agent->browser(),
                'location_country' => $this->getLocationInfo($ipAddress)['country'] ?? null,
                'location_city' => $this->getLocationInfo($ipAddress)['city'] ?? null,
                'last_activity' => now(),
                'login_at' => now(),
                'expires_at' => $this->getSessionExpiry(),
                'is_current' => true,
                'is_trusted' => $this->isDeviceTrusted($user, request()),
                'is_active' => true
            ]);
        } else {
            // อัพเดท last_activity
            $session->update([
                'last_activity' => now(),
            ]);
        }
    }
}
