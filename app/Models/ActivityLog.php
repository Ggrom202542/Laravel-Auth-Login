<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ActivityLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'payload',
        'response_status',
        'response_time',
        'location',
        'device_type',
        'browser',
        'platform',
        'is_suspicious',
        'session_id',
        'causer_type',
        'causer_id',
        'subject_type',
        'subject_id',
        'properties',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'properties' => 'array',
        'is_suspicious' => 'boolean',
        'response_time' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * ความสัมพันธ์กับผู้ใช้
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ความสัมพันธ์กับผู้ที่ทำกิจกรรม (polymorphic)
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * ความสัมพันธ์กับหัวข้อของกิจกรรม (polymorphic)
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Scope สำหรับกรองตามประเภทกิจกรรม
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope สำหรับกรองกิจกรรมที่น่าสงสัย
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope สำหรับกรองตามช่วงเวลา
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope สำหรับกรองกิจกรรมของผู้ใช้
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope สำหรับกรองตาม IP Address
     */
    public function scopeFromIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * รับรายการกิจกรรมล่าสุด
     */
    public static function getRecentActivities($limit = 50)
    {
        return static::with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * รับรายการกิจกรรมของผู้ใช้
     */
    public static function getUserActivities($userId, $limit = 20)
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * รับสถิติกิจกรรมรายวัน
     */
    public static function getDailyStats($days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return static::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * รับสถิติกิจกรรมตามประเภท
     */
    public static function getTypeStats($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return static::selectRaw('activity_type, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('activity_type')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * บันทึกกิจกรรมใหม่
     */
    public static function logActivity($data)
    {
        // ตรวจสอบข้อมูลที่จำเป็น
        $requiredFields = ['user_id', 'activity_type', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("Field {$field} is required");
            }
        }

        // เพิ่มข้อมูลเพิ่มเติมจาก request หากไม่ได้ระบุ
        if (request()) {
            $data = array_merge([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'session_id' => session()->getId(),
            ], $data);
        }

        return static::create($data);
    }

    /**
     * รับคำอธิบายกิจกรรมแบบเป็นมิตร
     */
    public function getFriendlyDescriptionAttribute()
    {
        $typeDescriptions = [
            'login' => 'เข้าสู่ระบบ',
            'logout' => 'ออกจากระบบ',
            'register' => 'สมัครสมาชิก',
            'password_change' => 'เปลี่ยนรหัสผ่าน',
            'profile_update' => 'อัปเดตโปรไฟล์',
            'email_verification' => 'ยืนยันอีเมล',
            '2fa_enabled' => 'เปิดใช้งาน 2FA',
            '2fa_disabled' => 'ปิดใช้งาน 2FA',
            'permission_change' => 'เปลี่ยนสิทธิ์',
            'role_change' => 'เปลี่ยนบทบาท',
            'session_start' => 'เริ่ม Session',
            'session_end' => 'จบ Session',
            'failed_login' => 'เข้าสู่ระบบไม่สำเร็จ',
            'suspicious_activity' => 'กิจกรรมน่าสงสัย',
            'data_export' => 'ส่งออกข้อมูล',
            'data_import' => 'นำเข้าข้อมูล',
            'file_upload' => 'อัปโหลดไฟล์',
            'file_download' => 'ดาวน์โหลดไฟล์',
            'message_sent' => 'ส่งข้อความ',
            'message_read' => 'อ่านข้อความ',
            'notification_sent' => 'ส่งการแจ้งเตือน',
            'approval_request' => 'ขออนุมัติ',
            'approval_granted' => 'อนุมัติแล้ว',
            'approval_rejected' => 'ปฏิเสธการอนุมัติ',
            'security_alert' => 'แจ้งเตือนความปลอดภัย',
            'ip_blocked' => 'บล็อก IP Address',
            'ip_unblocked' => 'ยกเลิกบล็อก IP Address',
            'device_registered' => 'ลงทะเบียนอุปกรณ์',
            'device_removed' => 'ลบอุปกรณ์',
            'settings_change' => 'เปลี่ยนการตั้งค่า',
            'backup_created' => 'สร้างข้อมูลสำรอง',
            'backup_restored' => 'กู้คืนข้อมูล',
            'system_maintenance' => 'บำรุงรักษาระบบ'
        ];

        return $typeDescriptions[$this->activity_type] ?? $this->activity_type;
    }

    /**
     * รับไอคอนสำหรับประเภทกิจกรรม
     */
    public function getActivityIconAttribute()
    {
        $icons = [
            'login' => 'bi-box-arrow-in-right text-success',
            'logout' => 'bi-box-arrow-right text-warning',
            'register' => 'bi-person-plus text-info',
            'password_change' => 'bi-key text-primary',
            'profile_update' => 'bi-person-gear text-info',
            'email_verification' => 'bi-envelope-check text-success',
            '2fa_enabled' => 'bi-shield-check text-success',
            '2fa_disabled' => 'bi-shield-x text-warning',
            'permission_change' => 'bi-shield-shaded text-warning',
            'role_change' => 'bi-person-badge text-warning',
            'session_start' => 'bi-play-circle text-success',
            'session_end' => 'bi-stop-circle text-secondary',
            'failed_login' => 'bi-x-circle text-danger',
            'suspicious_activity' => 'bi-exclamation-triangle text-danger',
            'data_export' => 'bi-download text-info',
            'data_import' => 'bi-upload text-info',
            'file_upload' => 'bi-cloud-upload text-primary',
            'file_download' => 'bi-cloud-download text-primary',
            'message_sent' => 'bi-chat-left-dots text-info',
            'message_read' => 'bi-chat-left text-secondary',
            'notification_sent' => 'bi-bell text-info',
            'approval_request' => 'bi-hourglass-split text-warning',
            'approval_granted' => 'bi-check-circle text-success',
            'approval_rejected' => 'bi-x-circle text-danger',
            'security_alert' => 'bi-shield-exclamation text-danger',
            'ip_blocked' => 'bi-slash-circle text-danger',
            'ip_unblocked' => 'bi-check-circle text-success',
            'device_registered' => 'bi-phone-plus text-success',
            'device_removed' => 'bi-phone-x text-warning',
            'settings_change' => 'bi-gear text-info',
            'backup_created' => 'bi-archive text-info',
            'backup_restored' => 'bi-arrow-clockwise text-success',
            'system_maintenance' => 'bi-tools text-warning'
        ];

        return $icons[$this->activity_type] ?? 'bi-circle text-secondary';
    }

    /**
     * รับข้อมูลเบราว์เซอร์และแพลตฟอร์ม
     */
    public function getBrowserInfoAttribute()
    {
        if ($this->browser && $this->platform) {
            return "{$this->browser} บน {$this->platform}";
        }
        
        return $this->user_agent ? $this->parseUserAgent() : 'ไม่ทราบ';
    }

    /**
     * แยกวิเคราะห์ User Agent
     */
    private function parseUserAgent()
    {
        $userAgent = $this->user_agent;
        
        // ตรวจสอบเบราว์เซอร์
        $browsers = [
            'Chrome' => '/Chrome\/([\d\.]+)/',
            'Firefox' => '/Firefox\/([\d\.]+)/',
            'Safari' => '/Safari\/([\d\.]+)/',
            'Edge' => '/Edge\/([\d\.]+)/',
            'Opera' => '/Opera\/([\d\.]+)/',
            'Internet Explorer' => '/MSIE ([\d\.]+)/'
        ];
        
        $browser = 'ไม่ทราบ';
        foreach ($browsers as $name => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $browser = $name;
                break;
            }
        }
        
        // ตรวจสอบระบบปฏิบัติการ
        $platforms = [
            'Windows' => '/Windows NT/',
            'macOS' => '/Mac OS X/',
            'Linux' => '/Linux/',
            'iOS' => '/iPhone|iPad/',
            'Android' => '/Android/'
        ];
        
        $platform = 'ไม่ทราบ';
        foreach ($platforms as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $platform = $name;
                break;
            }
        }
        
        return "{$browser} บน {$platform}";
    }

    /**
     * ตรวจสอบว่าเป็นกิจกรรมที่น่าสงสัยหรือไม่
     */
    public function markAsSuspicious($reason = null)
    {
        $this->update([
            'is_suspicious' => true,
            'properties' => array_merge($this->properties ?? [], [
                'suspicious_reason' => $reason,
                'marked_at' => now()
            ])
        ]);
    }

    /**
     * รับข้อมูลสรุปกิจกรรม
     */
    public static function getActivitySummary($userId = null, $days = 30)
    {
        $query = static::query();
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $startDate = Carbon::now()->subDays($days);
        $query->where('created_at', '>=', $startDate);
        
        return [
            'total_activities' => $query->count(),
            'suspicious_activities' => $query->where('is_suspicious', true)->count(),
            'unique_ips' => $query->distinct('ip_address')->count('ip_address'),
            'login_attempts' => $query->whereIn('activity_type', ['login', 'failed_login'])->count(),
            'failed_logins' => $query->where('activity_type', 'failed_login')->count(),
            'recent_activities' => $query->orderBy('created_at', 'desc')->limit(10)->get()
        ];
    }
}
