<?php

namespace App\Services;

use App\Models\IpRestriction;
use App\Models\User;
use Illuminate\Support\Facades\{Log, Http, Cache};
use Carbon\Carbon;

class IpManagementService
{
    // Configuration constants
    const AUTO_BLACKLIST_THRESHOLD = 10; // จำนวนความผิดพลาดที่จะ auto-blacklist
    const TEMPORARY_BLACKLIST_HOURS = 24; // ชั่วโมงที่จะบล็อก IP ชั่วคราว
    const GEOGRAPHIC_API_CACHE_HOURS = 24; // Cache geographic data

    /**
     * ตรวจสอบว่า IP ถูกบล็อกหรือไม่
     */
    public function isIpBlocked(string $ip): bool
    {
        // Skip localhost และ private IPs
        if ($this->isLocalOrPrivateIp($ip)) {
            return false;
        }

        return IpRestriction::isBlocked($ip);
    }

    /**
     * ตรวจสอบว่า IP อยู่ใน whitelist หรือไม่
     */
    public function isIpWhitelisted(string $ip): bool
    {
        // Localhost และ private IPs ถือว่า whitelisted
        if ($this->isLocalOrPrivateIp($ip)) {
            return true;
        }

        return IpRestriction::isWhitelisted($ip);
    }

    /**
     * บันทึกความพยายาม login ที่ล้มเหลวของ IP
     */
    public function recordFailedAttempt(string $ip, User $user = null): void
    {
        if ($this->isLocalOrPrivateIp($ip)) {
            return;
        }

        // หา IP restriction record หรือสร้างใหม่
        $ipRecord = IpRestriction::firstOrCreate(
            ['ip_address' => $ip],
            [
                'type' => 'blacklist', // เริ่มต้นเป็น blacklist แต่ inactive
                'status' => 'inactive',
                'auto_generated' => true,
                'first_seen_at' => now(),
                'reason' => 'Auto-tracked due to failed login attempts'
            ]
        );

        $ipRecord->recordActivity('failed_login');

        // ตรวจสอบว่าควร auto-blacklist หรือไม่
        if ($ipRecord->failed_login_attempts >= self::AUTO_BLACKLIST_THRESHOLD 
            && $ipRecord->status !== 'active') {
            $this->autoBlacklistIp($ip, $ipRecord);
        }

        Log::info('IP failed login attempt recorded', [
            'ip' => $ip,
            'user_id' => $user?->id,
            'total_attempts' => $ipRecord->failed_login_attempts,
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Auto-blacklist IP เมื่อมีความผิดพลาดมากเกินไป
     */
    protected function autoBlacklistIp(string $ip, IpRestriction $ipRecord): void
    {
        $expiresAt = now()->addHours(self::TEMPORARY_BLACKLIST_HOURS);
        
        $ipRecord->update([
            'status' => 'active',
            'reason' => 'Auto-blacklisted due to excessive failed login attempts',
            'description' => "Automatically blocked after {$ipRecord->failed_login_attempts} failed attempts",
            'expires_at' => $expiresAt,
            'auto_generated' => true
        ]);

        Log::warning('IP auto-blacklisted', [
            'ip' => $ip,
            'failed_attempts' => $ipRecord->failed_login_attempts,
            'expires_at' => $expiresAt,
            'reason' => 'excessive_failed_attempts'
        ]);

        // ดึงข้อมูลทางภูมิศาสตร์
        $this->updateGeographicInfo($ipRecord);
    }

    /**
     * เพิ่ม IP ลงใน blacklist ด้วยตนเอง
     */
    public function addToBlacklist(
        string $ip, 
        string $reason, 
        string $description = null,
        ?Carbon $expiresAt = null
    ): IpRestriction {
        $ipRecord = IpRestriction::addToBlacklist(
            $ip, 
            $reason, 
            $description, 
            auth()->id(), 
            $expiresAt
        );

        // ดึงข้อมูลทางภูมิศาสตร์
        $this->updateGeographicInfo($ipRecord);

        Log::info('IP manually added to blacklist', [
            'ip' => $ip,
            'reason' => $reason,
            'added_by' => auth()->user()->email,
            'expires_at' => $expiresAt
        ]);

        return $ipRecord;
    }

    /**
     * เพิ่ม IP ลงใน whitelist
     */
    public function addToWhitelist(
        string $ip, 
        string $reason, 
        string $description = null
    ): IpRestriction {
        $ipRecord = IpRestriction::addToWhitelist(
            $ip, 
            $reason, 
            $description, 
            auth()->id()
        );

        // ดึงข้อมูลทางภูมิศาสตร์
        $this->updateGeographicInfo($ipRecord);

        Log::info('IP added to whitelist', [
            'ip' => $ip,
            'reason' => $reason,
            'added_by' => auth()->user()->email
        ]);

        return $ipRecord;
    }

    /**
     * ลบ IP restriction
     */
    public function removeRestriction(string $ip): bool
    {
        $removed = IpRestriction::removeRestriction($ip);

        if ($removed) {
            Log::info('IP restriction removed', [
                'ip' => $ip,
                'removed_by' => auth()->user()->email
            ]);
        }

        return $removed;
    }

    /**
     * ดึงรายการ IP restrictions
     */
    public function getRestrictions(string $type = null, int $limit = 50): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = IpRestriction::with('creator')
            ->orderBy('last_activity_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->paginate($limit);
    }

    /**
     * ดึงสถิติ IP restrictions
     */
    public function getStatistics(): array
    {
        return [
            'total_blacklisted' => IpRestriction::blacklist()->active()->count(),
            'total_whitelisted' => IpRestriction::whitelist()->active()->count(),
            'auto_generated' => IpRestriction::where('auto_generated', true)->active()->count(),
            'manual_added' => IpRestriction::where('auto_generated', false)->active()->count(),
            'expiring_soon' => IpRestriction::active()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addHours(24))
                ->count(),
            'total_blocked_attempts' => IpRestriction::sum('failed_login_attempts'),
            'most_active_ips' => IpRestriction::orderBy('failed_login_attempts', 'desc')
                ->limit(5)
                ->get(['ip_address', 'failed_login_attempts', 'country_name']),
            'countries' => IpRestriction::select('country_name')
                ->selectRaw('count(*) as count')
                ->whereNotNull('country_name')
                ->groupBy('country_name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    /**
     * ล้าง IP restrictions ที่หมดอายุแล้ว
     */
    public function cleanupExpiredRestrictions(): int
    {
        $count = IpRestriction::cleanupExpired();
        
        if ($count > 0) {
            Log::info('Expired IP restrictions cleaned up', ['count' => $count]);
        }

        return $count;
    }

    /**
     * ตรวจสอบว่าเป็น local หรือ private IP หรือไม่
     */
    protected function isLocalOrPrivateIp(string $ip): bool
    {
        // IPv4 private ranges และ localhost
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }

        // IPv6 localhost
        if ($ip === '::1') {
            return true;
        }

        return false;
    }

    /**
     * อัปเดตข้อมูลทางภูมิศาสตร์ของ IP
     */
    public function updateGeographicInfo(IpRestriction $ipRecord): void
    {
        $ip = $ipRecord->ip_address;

        // Skip ถ้าเป็น private IP
        if ($this->isLocalOrPrivateIp($ip)) {
            return;
        }

        // ใช้ cache เพื่อไม่ให้เรียก API บ่อยเกินไป
        $cacheKey = "ip_geo_{$ip}";
        $geoData = Cache::get($cacheKey);

        if (!$geoData) {
            try {
                // ใช้ ip-api.com (free service)
                $response = Http::timeout(10)->get("http://ip-api.com/json/{$ip}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'success') {
                        $geoData = [
                            'country_code' => $data['countryCode'] ?? null,
                            'country_name' => $data['country'] ?? null,
                            'city' => $data['city'] ?? null,
                            'region' => $data['regionName'] ?? null,
                            'isp' => $data['isp'] ?? null,
                            'organization' => $data['org'] ?? null,
                            'latitude' => $data['lat'] ?? null,
                            'longitude' => $data['lon'] ?? null,
                        ];

                        // Cache ข้อมูลเป็นเวลา 24 ชั่วโมง
                        Cache::put($cacheKey, $geoData, now()->addHours(self::GEOGRAPHIC_API_CACHE_HOURS));
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch geographic info for IP', [
                    'ip' => $ip,
                    'error' => $e->getMessage()
                ]);
                return;
            }
        }

        if ($geoData) {
            $ipRecord->updateGeographicInfo($geoData);
        }
    }

    /**
     * บล็อก IP ชั่วคราวเนื่องจากกิจกรรมที่น่าสงสัย
     */
    public function temporaryBlock(string $ip, string $reason, int $hours = 1): IpRestriction
    {
        return $this->addToBlacklist(
            $ip,
            "Temporary block: {$reason}",
            "Automatically blocked for {$hours} hours due to suspicious activity",
            now()->addHours($hours)
        );
    }

    /**
     * ดึงรายงาน IP activity
     */
    public function getActivityReport(int $days = 7): array
    {
        $startDate = now()->subDays($days);

        return [
            'period' => "{$days} days",
            'new_restrictions' => IpRestriction::where('created_at', '>=', $startDate)->count(),
            'failed_attempts' => IpRestriction::where('last_activity_at', '>=', $startDate)
                ->sum('failed_login_attempts'),
            'top_offending_ips' => IpRestriction::where('last_activity_at', '>=', $startDate)
                ->orderBy('failed_login_attempts', 'desc')
                ->limit(10)
                ->get(['ip_address', 'failed_login_attempts', 'country_name', 'last_activity_at']),
            'countries_analysis' => IpRestriction::where('last_activity_at', '>=', $startDate)
                ->whereNotNull('country_name')
                ->selectRaw('country_name, count(*) as ip_count, sum(failed_login_attempts) as total_attempts')
                ->groupBy('country_name')
                ->orderBy('total_attempts', 'desc')
                ->limit(10)
                ->get(),
            'daily_activity' => IpRestriction::where('last_activity_at', '>=', $startDate)
                ->selectRaw('DATE(last_activity_at) as date, count(*) as active_ips, sum(failed_login_attempts) as attempts')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];
    }
}
