<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'password_histories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'password_hash',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Indicates if the model should use timestamps.
     */
    public $timestamps = false;

    /**
     * Get the user that owns the password history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Store a new password in history
     */
    public static function storePassword($user, string $passwordHash): void
    {
        $userId = is_object($user) ? $user->id : $user;
        
        // Store the new password
        self::create([
            'user_id' => $userId,
            'password_hash' => $passwordHash,
            'created_at' => now(),
        ]);

        // Clean up old password history (keep only the configured number)
        $historyCount = config('password_policy.history.count', 5);
        
        $oldPasswords = self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->skip($historyCount)
            ->take(100) // Limit for safety
            ->pluck('id');

        if ($oldPasswords->isNotEmpty()) {
            self::whereIn('id', $oldPasswords)->delete();
        }
    }

    /**
     * Get user's password history
     */
    public static function getUserHistory(int $userId, int $limit = null): \Illuminate\Database\Eloquent\Collection
    {
        $limit = $limit ?: config('password_policy.history.count', 5);
        
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
