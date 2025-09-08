<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class IpRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'type',
        'reason',
        'description',
        'country_code',
        'country_name',
        'city',
        'region',
        'isp',
        'organization',
        'latitude',
        'longitude',
        'failed_login_attempts',
        'suspicious_activities',
        'last_activity_at',
        'first_seen_at',
        'status',
        'created_by',
        'expires_at',
        'auto_generated'
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'first_seen_at' => 'datetime',
        'expires_at' => 'datetime',
        'auto_generated' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    /**
     * Relationship กับ User ที่สร้าง restriction นี้
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope สำหรับ active restrictions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope สำหรับ blacklist
     */
    public function scopeBlacklist($query)
    {
        return $query->where('type', 'blacklist');
    }

    /**
     * Scope สำหรับ whitelist
     */
    public function scopeWhitelist($query)
    {
        return $query->where('type', 'whitelist');
    }

    /**
     * ตรวจสอบว่า IP นี้ถูกบล็อกหรือไม่
     */
    public static function isBlocked(string $ip): bool
    {
        // ตรวจสอบ whitelist ก่อน (มีความสำคัญสูงสุด)
        $whitelisted = self::active()
            ->whitelist()
            ->where('ip_address', $ip)
            ->exists();

        if ($whitelisted) {
            return false;
        }

        // ตรวจสอบ blacklist
        return self::active()
            ->blacklist()
            ->where('ip_address', $ip)
            ->exists();
    }

    /**
     * ตรวจสอบว่า IP นี้อยู่ใน whitelist หรือไม่
     */
    public static function isWhitelisted(string $ip): bool
    {
        return self::active()
            ->whitelist()
            ->where('ip_address', $ip)
            ->exists();
    }

    /**
     * เพิ่ม IP ลงใน blacklist
     */
    public static function addToBlacklist(
        string $ip, 
        string $reason = null, 
        string $description = null,
        ?int $createdBy = null,
        ?Carbon $expiresAt = null
    ): self {
        return self::updateOrCreate(
            ['ip_address' => $ip],
            [
                'type' => 'blacklist',
                'reason' => $reason,
                'description' => $description,
                'status' => 'active',
                'created_by' => $createdBy ?: auth()->id(),
                'expires_at' => $expiresAt,
                'first_seen_at' => now(),
                'last_activity_at' => now()
            ]
        );
    }

    /**
     * เพิ่ม IP ลงใน whitelist
     */
    public static function addToWhitelist(
        string $ip,
        string $reason = null,
        string $description = null,
        ?int $createdBy = null
    ): self {
        return self::updateOrCreate(
            ['ip_address' => $ip],
            [
                'type' => 'whitelist',
                'reason' => $reason,
                'description' => $description,
                'status' => 'active',
                'created_by' => $createdBy ?: auth()->id(),
                'expires_at' => null, // Whitelist ไม่หมดอายุ
                'first_seen_at' => now(),
                'last_activity_at' => now()
            ]
        );
    }

    /**
     * ลบ IP restriction
     */
    public static function removeRestriction(string $ip): bool
    {
        return self::where('ip_address', $ip)->delete();
    }

    /**
     * บันทึกกิจกรรมของ IP
     */
    public function recordActivity(string $activityType = 'login_attempt'): void
    {
        $this->increment('failed_login_attempts');
        $this->update(['last_activity_at' => now()]);

        if ($activityType === 'suspicious') {
            $this->increment('suspicious_activities');
        }
    }

    /**
     * ดึงข้อมูลทางภูมิศาสตร์ของ IP (สำหรับอนาคต)
     */
    public function updateGeographicInfo(array $geoData): void
    {
        $this->update([
            'country_code' => $geoData['country_code'] ?? null,
            'country_name' => $geoData['country_name'] ?? null,
            'city' => $geoData['city'] ?? null,
            'region' => $geoData['region'] ?? null,
            'isp' => $geoData['isp'] ?? null,
            'organization' => $geoData['organization'] ?? null,
            'latitude' => $geoData['latitude'] ?? null,
            'longitude' => $geoData['longitude'] ?? null,
        ]);
    }

    /**
     * ตรวจสอบว่า restriction หมดอายุหรือยัง
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * ดึงรายการ IP ที่หมดอายุแล้ว
     */
    public static function getExpiredRestrictions()
    {
        return self::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('status', 'active');
    }

    /**
     * ล้าง restrictions ที่หมดอายุแล้ว
     */
    public static function cleanupExpired(): int
    {
        return self::getExpiredRestrictions()->update(['status' => 'inactive']);
    }
}
