<?php

namespace App\Services;

use App\Models\UserDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, Hash, Cache};
use Illuminate\Support\Str;
use Carbon\Carbon;

class DeviceManagementService
{
    // Configuration constants
    const DEVICE_EXPIRY_DAYS = 90; // วันที่อุปกรณ์จะหมดอายุ (ถ้าไม่ใช้งาน)
    const MAX_DEVICES_PER_USER = 10; // จำนวนอุปกรณ์สูงสุดต่อผู้ใช้
    const VERIFICATION_REQUIRED_ACTIONS = ['sensitive', 'admin']; // Actions ที่ต้องตรวจสอบ

    /**
     * สร้าง Device Fingerprint จาก Request
     */
    public function generateDeviceFingerprint(Request $request): string
    {
        $userAgent = $request->userAgent() ?? '';
        $acceptLanguage = $request->header('Accept-Language') ?? '';
        $acceptEncoding = $request->header('Accept-Encoding') ?? '';
        $ip = $request->ip();
        
        // รวมข้อมูลสำหรับสร้าง fingerprint
        $fingerprintData = [
            'user_agent' => $userAgent,
            'accept_language' => $acceptLanguage,
            'accept_encoding' => $acceptEncoding,
            'ip_prefix' => substr($ip, 0, strrpos($ip, '.')) // เอาเฉพาะ 3 octet แรก
        ];
        
        return hash('sha256', json_encode($fingerprintData));
    }

    /**
     * ดึงข้อมูลอุปกรณ์จาก Request
     */
    public function extractDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent() ?? '';
        
        // Parse User Agent (อาจใช้ library เพิ่มเติมสำหรับความแม่นยำ)
        $deviceInfo = $this->parseUserAgent($userAgent);
        
        return [
            'device_fingerprint' => $this->generateDeviceFingerprint($request),
            'user_agent' => $userAgent,
            'ip_address' => $request->ip(),
            'device_type' => $deviceInfo['device_type'] ?? null,
            'browser_name' => $deviceInfo['browser_name'] ?? null,
            'browser_version' => $deviceInfo['browser_version'] ?? null,
            'operating_system' => $deviceInfo['operating_system'] ?? null,
            'platform' => $deviceInfo['platform'] ?? null,
            'timezone' => $request->header('X-Timezone') ?? null,
            'language' => $request->getPreferredLanguage(),
            'screen_resolution' => $request->header('X-Screen-Resolution') ?? null
        ];
    }

    /**
     * บันทึกหรืออัปเดตอุปกรณ์สำหรับผู้ใช้
     */
    public function registerDevice(User $user, Request $request): UserDevice
    {
        $deviceInfo = $this->extractDeviceInfo($request);
        $deviceInfo['user_id'] = $user->id;
        
        // ตรวจสอบจำนวนอุปกรณ์สูงสุด
        $this->enforceDeviceLimit($user);
        
        $device = UserDevice::findOrCreateDevice($deviceInfo);
        
        Log::info('Device registered/updated', [
            'user_id' => $user->id,
            'device_fingerprint' => $device->device_fingerprint,
            'device_type' => $device->device_type,
            'is_new' => $device->wasRecentlyCreated
        ]);
        
        return $device;
    }

    /**
     * ตรวจสอบว่าอุปกรณ์เป็นที่ไว้วางใจหรือไม่
     */
    public function isDeviceTrusted(string $fingerprint, int $userId): bool
    {
        $device = UserDevice::where('device_fingerprint', $fingerprint)
                           ->where('user_id', $userId)
                           ->where('is_active', true)
                           ->first();
        
        if (!$device) {
            return false;
        }
        
        // ตรวจสอบว่าหมดอายุหรือไม่
        if ($device->isExpired()) {
            return false;
        }
        
        return $device->is_trusted;
    }

    /**
     * ตรวจสอบว่าอุปกรณ์ต้องการการยืนยันหรือไม่
     */
    public function deviceNeedsVerification(string $fingerprint, int $userId): bool
    {
        $device = UserDevice::where('device_fingerprint', $fingerprint)
                           ->where('user_id', $userId)
                           ->where('is_active', true)
                           ->first();
        
        if (!$device) {
            return true; // อุปกรณ์ใหม่ต้องยืนยัน
        }
        
        return $device->needsVerification();
    }

    /**
     * ทำเครื่องหมายอุปกรณ์เป็นที่ไว้วางใจ
     */
    public function trustDevice(string $fingerprint, int $userId, string $method = 'manual'): bool
    {
        $device = UserDevice::where('device_fingerprint', $fingerprint)
                           ->where('user_id', $userId)
                           ->first();
        
        if (!$device) {
            return false;
        }
        
        $result = $device->markAsTrusted($method);
        
        if ($result) {
            Log::info('Device marked as trusted', [
                'user_id' => $userId,
                'device_fingerprint' => $fingerprint,
                'method' => $method
            ]);
        }
        
        return $result;
    }

    /**
     * ยกเลิกความไว้วางใจอุปกรณ์
     */
    public function revokeDeviceTrust(string $fingerprint, int $userId): bool
    {
        $result = UserDevice::revokeDeviceTrust($fingerprint, $userId);
        
        if ($result) {
            Log::info('Device trust revoked', [
                'user_id' => $userId,
                'device_fingerprint' => $fingerprint
            ]);
        }
        
        return $result;
    }

    /**
     * ปิดใช้งานอุปกรณ์
     */
    public function deactivateDevice(string $fingerprint, int $userId): bool
    {
        $result = UserDevice::deactivateDevice($fingerprint, $userId);
        
        if ($result) {
            Log::info('Device deactivated', [
                'user_id' => $userId,
                'device_fingerprint' => $fingerprint
            ]);
        }
        
        return $result;
    }

    /**
     * ดึงรายการอุปกรณ์ของผู้ใช้
     */
    public function getUserDevices(int $userId, bool $activeOnly = true): \Illuminate\Database\Eloquent\Collection
    {
        $query = UserDevice::where('user_id', $userId)
                          ->orderBy('last_seen_at', 'desc');
        
        if ($activeOnly) {
            $query->active();
        }
        
        return $query->get();
    }

    /**
     * ดึงสถิติอุปกรณ์
     */
    public function getDeviceStatistics(): array
    {
        return [
            'total_devices' => UserDevice::count(),
            'active_devices' => UserDevice::active()->count(),
            'trusted_devices' => UserDevice::trusted()->count(),
            'unverified_devices' => UserDevice::unverified()->count(),
            'expired_devices' => UserDevice::expired()->count(),
            'recent_devices' => UserDevice::where('created_at', '>=', now()->subDays(7))->count(),
            'device_types' => UserDevice::select('device_type')
                                      ->selectRaw('count(*) as count')
                                      ->whereNotNull('device_type')
                                      ->groupBy('device_type')
                                      ->orderBy('count', 'desc')
                                      ->get(),
            'browser_stats' => UserDevice::select('browser_name')
                                        ->selectRaw('count(*) as count')
                                        ->whereNotNull('browser_name')
                                        ->groupBy('browser_name')
                                        ->orderBy('count', 'desc')
                                        ->limit(10)
                                        ->get(),
            'os_stats' => UserDevice::select('operating_system')
                                   ->selectRaw('count(*) as count')
                                   ->whereNotNull('operating_system')
                                   ->groupBy('operating_system')
                                   ->orderBy('count', 'desc')
                                   ->limit(10)
                                   ->get()
        ];
    }

    /**
     * ล้างอุปกรณ์ที่หมดอายุ
     */
    public function cleanupExpiredDevices(): int
    {
        $count = UserDevice::cleanupExpiredDevices();
        
        if ($count > 0) {
            Log::info('Expired devices cleaned up', ['count' => $count]);
        }
        
        return $count;
    }

    /**
     * ตรวจสอบและจำกัดจำนวนอุปกรณ์ต่อผู้ใช้
     */
    protected function enforceDeviceLimit(User $user): void
    {
        $deviceCount = UserDevice::where('user_id', $user->id)
                                ->active()
                                ->count();
        
        if ($deviceCount >= self::MAX_DEVICES_PER_USER) {
            // ลบอุปกรณ์เก่าที่สุดที่ไม่ trusted
            UserDevice::where('user_id', $user->id)
                     ->where('is_trusted', false)
                     ->orderBy('last_seen_at')
                     ->first()
                     ?->update(['is_active' => false]);
        }
    }

    /**
     * Parse User Agent string (basic implementation)
     */
    protected function parseUserAgent(string $userAgent): array
    {
        $info = [
            'browser_name' => null,
            'browser_version' => null,
            'operating_system' => null,
            'platform' => null,
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
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
            $info['browser_name'] = 'Edge';
            $info['browser_version'] = $matches[1];
        }
        
        // OS detection
        if (strpos($userAgent, 'Windows NT') !== false) {
            $info['operating_system'] = 'Windows';
            $info['platform'] = 'desktop';
        } elseif (strpos($userAgent, 'Mac OS X') !== false) {
            $info['operating_system'] = 'macOS';
            $info['platform'] = 'desktop';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $info['operating_system'] = 'Linux';
            $info['platform'] = 'desktop';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $info['operating_system'] = 'Android';
            $info['platform'] = 'mobile';
            $info['device_type'] = 'mobile';
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $info['operating_system'] = 'iOS';
            $info['platform'] = 'mobile';
            $info['device_type'] = strpos($userAgent, 'iPad') !== false ? 'tablet' : 'mobile';
        }
        
        // Device type fallback
        if (!$info['device_type']) {
            if (strpos($userAgent, 'Mobile') !== false) {
                $info['device_type'] = 'mobile';
            } elseif (strpos($userAgent, 'Tablet') !== false) {
                $info['device_type'] = 'tablet';
            } else {
                $info['device_type'] = 'desktop';
            }
        }
        
        return $info;
    }

    /**
     * สร้างรายงานการใช้งานอุปกรณ์
     */
    public function getDeviceReport(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'period' => "{$days} days",
            'new_devices' => UserDevice::where('created_at', '>=', $startDate)->count(),
            'active_users' => UserDevice::where('last_seen_at', '>=', $startDate)
                                       ->distinct('user_id')
                                       ->count(),
            'trusted_percentage' => UserDevice::active()->count() > 0 
                ? round(UserDevice::trusted()->count() / UserDevice::active()->count() * 100, 2)
                : 0,
            'device_adoption' => UserDevice::where('created_at', '>=', $startDate)
                                          ->selectRaw('DATE(created_at) as date, count(*) as new_devices')
                                          ->groupBy('date')
                                          ->orderBy('date')
                                          ->get(),
            'most_used_devices' => UserDevice::where('last_seen_at', '>=', $startDate)
                                            ->orderBy('login_count', 'desc')
                                            ->limit(10)
                                            ->with('user:id,username')
                                            ->get(['id', 'user_id', 'device_fingerprint', 'device_type', 'browser_name', 'operating_system', 'login_count', 'last_seen_at']),
            'security_summary' => [
                'verified_devices' => UserDevice::whereNotNull('verified_at')->count(),
                'pending_verification' => UserDevice::unverified()->count(),
                'suspicious_devices' => UserDevice::where('login_count', '>', 50)
                                                 ->where('is_trusted', false)
                                                 ->count()
            ]
        ];
    }
}
