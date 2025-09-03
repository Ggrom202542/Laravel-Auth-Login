<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_approval_id',
        'user_id',
        'action',
        'old_status',
        'new_status', 
        'reason',
        'comments',
        'metadata',
        'is_override',
        'overridden_by',
        'performed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_override' => 'boolean',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the registration approval that this log belongs to.
     */
    public function registrationApproval(): BelongsTo
    {
        return $this->belongsTo(RegistrationApproval::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who was overridden (if applicable).
     */
    public function overriddenUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    /**
     * Scope to get override actions only.
     */
    public function scopeOverrides($query)
    {
        return $query->where('is_override', true);
    }

    /**
     * Scope to get actions by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get actions for a specific approval.
     */
    public function scopeForApproval($query, $approvalId)
    {
        return $query->where('registration_approval_id', $approvalId);
    }

    /**
     * Get formatted action description.
     */
    public function getActionDescriptionAttribute(): string
    {
        $descriptions = [
            'created' => 'สร้างคำขอลงทะเบียน',
            'viewed' => 'ดูรายละเอียด',
            'approved' => 'อนุมัติ',
            'rejected' => 'ปฏิเสธ',
            'override_approved' => 'Override: อนุมัติ (แทนที่การตัดสินใจเดิม)',
            'override_rejected' => 'Override: ปฏิเสธ (แทนที่การตัดสินใจเดิม)',
            'commented' => 'เพิ่มความเห็น',
            'status_changed' => 'เปลี่ยนสถานะ',
            'escalated' => 'ส่งต่อให้ระดับสูงกว่า',
            'deleted' => 'ลบคำขอ',
        ];

        return $descriptions[$this->action] ?? $this->action;
    }

    /**
     * Get the user's role at the time of action.
     */
    public function getUserRoleAttribute(): string
    {
        return $this->user?->role ?? 'unknown';
    }

    /**
     * Check if this is a critical action that should be highlighted.
     */
    public function getIsCriticalActionAttribute(): bool
    {
        return in_array($this->action, [
            'approved', 
            'rejected', 
            'override_approved', 
            'override_rejected',
            'deleted'
        ]);
    }

    /**
     * Get IP address from metadata.
     */
    public function getIpAddressAttribute(): ?string
    {
        return $this->metadata['ip_address'] ?? null;
    }

    /**
     * Get user agent from metadata.
     */
    public function getUserAgentAttribute(): ?string
    {
        return $this->metadata['user_agent'] ?? null;
    }
}
