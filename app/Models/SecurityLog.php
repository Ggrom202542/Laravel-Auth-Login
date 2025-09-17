<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'admin_id',
        'ip_address',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * ความสัมพันธ์กับ User (ผู้ใช้ที่ถูกดำเนินการ)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ความสัมพันธ์กับ User (ผู้ดูแลระบบที่ดำเนินการ)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * ค้นหาตามประเภท
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * ค้นหาตามช่วงเวลา
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * ค้นหาตาม User ID
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * ค้นหาตาม Admin ID
     */
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }
}