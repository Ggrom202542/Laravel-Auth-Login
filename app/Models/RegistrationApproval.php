<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'additional_data',
        'approval_token',
        'token_expires_at',
        'registration_ip',
        'user_agent',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'additional_data' => 'array',
    ];

    /**
     * User ที่สมัครสมาชิก
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin/Super Admin ที่ทำการอนุมัติ/ปฏิเสธ
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if the approval token is expired
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at < now();
    }

    /**
     * Get pending approvals
     */
    public static function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get approved approvals
     */
    public static function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get rejected approvals
     */
    public static function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get approvals within date range
     */
    public static function scopeWithinDays($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Status badge color helper
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Status text helper
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'รออนุมัติ',
            'approved' => 'อนุมัติแล้ว',
            'rejected' => 'ปฏิเสธ',
            'expired' => 'หมดอายุ',
            default => 'ไม่ทราบ'
        };
    }

    /**
     * Get time remaining until token expires
     */
    public function getTimeRemainingAttribute(): ?string
    {
        if (!$this->token_expires_at || $this->isTokenExpired()) {
            return null;
        }

        return $this->token_expires_at->diffForHumans();
    }
}
