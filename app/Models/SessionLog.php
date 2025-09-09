<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'location_country',
        'location_city',
        'performed_by',
        'reason',
        'metadata',
        'performed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime'
    ];

    /**
     * Relationship: Log belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Log belongs to Performer (admin who performed action)
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope: Filter by action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Recent logs
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Get human readable action
     */
    public function getActionTextAttribute(): string
    {
        return match($this->action) {
            'login' => 'เข้าสู่ระบบ',
            'logout' => 'ออกจากระบบ',
            'force_logout' => 'ถูกบังคับออกจากระบบ',
            'activity' => 'กิจกรรมในระบบ',
            'expired' => 'เซสชันหมดอายุ',
            default => $this->action
        };
    }

    /**
     * Get action badge class for UI
     */
    public function getActionBadgeClass(): string
    {
        return match($this->action) {
            'login' => 'bg-success',
            'logout' => 'bg-secondary',
            'force_logout' => 'bg-warning',
            'suspicious_activity' => 'bg-danger',
            'device_trusted' => 'bg-info',
            'password_changed' => 'bg-primary',
            default => 'bg-light text-dark'
        };
    }

    /**
     * Get human-readable action label
     */
    public function getActionLabel(): string
    {
        return match($this->action) {
            'login' => 'เข้าสู่ระบบ',
            'logout' => 'ออกจากระบบ',
            'force_logout' => 'บังคับออกจากระบบ',
            'suspicious_activity' => 'กิจกรรมผิดปกติ',
            'device_trusted' => 'เชื่อถืออุปกรณ์',
            'password_changed' => 'เปลี่ยนรหัสผ่าน',
            'activity' => 'กิจกรรมในระบบ',
            'expired' => 'เซสชันหมดอายุ',
            default => ucfirst(str_replace('_', ' ', $this->action))
        };
    }
}
