<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordReset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reset_by',
        'reset_type',
        'reason',
        'notification_sent',
        'notification_methods',
        'notification_results',
        'password_changed_at',
        'is_used',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'notification_sent' => 'boolean',
        'notification_methods' => 'array',
        'notification_results' => 'array',
        'password_changed_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Get the user that owns the password reset
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who performed the reset
     */
    public function resetBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reset_by');
    }

    /**
     * Scope to get recent resets
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get unused resets
     */
    public function scopeUnused($query)
    {
        return $query->where('is_used', false);
    }
}
