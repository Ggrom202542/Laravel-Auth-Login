<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AccountLockoutService
{
    // Configuration constants
    const MAX_FAILED_ATTEMPTS = 5;
    const LOCKOUT_DURATION_MINUTES = 30;
    const RESET_FAILED_ATTEMPTS_AFTER_MINUTES = 60;

    /**
     * ตรวจสอบว่าบัญชีถูกล็อกหรือไม่
     */
    public function isAccountLocked(User $user): bool
    {
        if (!$user->locked_at) {
            return false;
        }

        // ตรวจสอบว่าเวลาล็อกหมดอายุหรือยัง
        $lockExpiry = Carbon::parse($user->locked_at)->addMinutes(self::LOCKOUT_DURATION_MINUTES);
        
        if (Carbon::now()->greaterThan($lockExpiry)) {
            // Auto-unlock หากเวลาหมดอายุแล้ว
            $this->unlockAccount($user);
            return false;
        }

        return true;
    }

    /**
     * บันทึกความพยายาม login ที่ล้มเหลว
     */
    public function recordFailedAttempt(User $user, string $ip = null): void
    {
        $user->increment('failed_login_attempts');
        $user->update([
            'last_failed_login_at' => Carbon::now(),
            'last_login_ip' => $ip ?: request()->ip()
        ]);

        // ตรวจสอบว่าควรล็อกบัญชีหรือไม่
        if ($user->failed_login_attempts >= self::MAX_FAILED_ATTEMPTS) {
            $this->lockAccount($user);
        }

        Log::info('Failed login attempt recorded', [
            'user_id' => $user->id,
            'email' => $user->email,
            'failed_attempts' => $user->failed_login_attempts,
            'ip' => $ip ?: request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * ล็อกบัญชีผู้ใช้
     */
    public function lockAccount(User $user): void
    {
        $unlockToken = Str::random(60);
        
        $user->update([
            'locked_at' => Carbon::now(),
            'unlock_token' => $unlockToken
        ]);

        Log::warning('Account locked due to too many failed attempts', [
            'user_id' => $user->id,
            'email' => $user->email,
            'failed_attempts' => $user->failed_login_attempts,
            'locked_at' => $user->locked_at,
            'ip' => request()->ip()
        ]);

        // TODO: ส่งอีเมลแจ้งเตือนให้ผู้ใช้
        // $this->sendAccountLockedNotification($user, $unlockToken);
    }

    /**
     * ปลดล็อกบัญชีผู้ใช้
     */
    public function unlockAccount(User $user): void
    {
        $user->update([
            'locked_at' => null,
            'unlock_token' => null,
            'failed_login_attempts' => 0,
            'last_failed_login_at' => null
        ]);

        Log::info('Account unlocked', [
            'user_id' => $user->id,
            'email' => $user->email,
            'unlocked_by' => auth()->check() ? auth()->user()->email : 'system'
        ]);
    }

    /**
     * รีเซ็ตจำนวนความพยายาม login ที่ล้มเหลว
     */
    public function resetFailedAttempts(User $user): void
    {
        if ($user->failed_login_attempts > 0) {
            $user->update([
                'failed_login_attempts' => 0,
                'last_failed_login_at' => null
            ]);

            Log::info('Failed login attempts reset', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }
    }

    /**
     * บันทึก login ที่สำเร็จ
     */
    public function recordSuccessfulLogin(User $user, string $ip = null): void
    {
        // รีเซ็ตข้อมูลที่เกี่ยวข้องกับ failed attempts
        $this->resetFailedAttempts($user);

        $user->update([
            'last_login_at' => Carbon::now(),
            'last_login_ip' => $ip ?: request()->ip()
        ]);

        Log::info('Successful login recorded', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $ip ?: request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * ดึงข้อมูลสถานะการล็อก
     */
    public function getLockoutStatus(User $user): array
    {
        if (!$this->isAccountLocked($user)) {
            return [
                'is_locked' => false,
                'failed_attempts' => $user->failed_login_attempts,
                'max_attempts' => self::MAX_FAILED_ATTEMPTS,
                'remaining_attempts' => self::MAX_FAILED_ATTEMPTS - $user->failed_login_attempts
            ];
        }

        $lockExpiry = Carbon::parse($user->locked_at)->addMinutes(self::LOCKOUT_DURATION_MINUTES);
        $remainingMinutes = Carbon::now()->diffInMinutes($lockExpiry, false);

        return [
            'is_locked' => true,
            'locked_at' => $user->locked_at,
            'unlock_time' => $lockExpiry,
            'remaining_minutes' => max(0, $remainingMinutes),
            'failed_attempts' => $user->failed_login_attempts
        ];
    }

    /**
     * ดึงรายการบัญชีที่ถูกล็อก (สำหรับ Admin)
     */
    public function getLockedAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereNotNull('locked_at')
            ->orderBy('locked_at', 'desc')
            ->get();
    }

    /**
     * ล็อกบัญชีโดย Admin (Manual lock)
     */
    public function adminLockAccount(User $user, string $reason = null): void
    {
        $this->lockAccount($user);

        Log::warning('Account manually locked by admin', [
            'user_id' => $user->id,
            'email' => $user->email,
            'locked_by' => auth()->user()->email,
            'reason' => $reason
        ]);
    }

    /**
     * ปลดล็อกบัญชีโดย Admin (Manual unlock)
     */
    public function adminUnlockAccount(User $user, string $reason = null): void
    {
        $this->unlockAccount($user);

        Log::info('Account manually unlocked by admin', [
            'user_id' => $user->id,
            'email' => $user->email,
            'unlocked_by' => auth()->user()->email,
            'reason' => $reason
        ]);
    }

    /**
     * ตรวจสอบและล้างบัญชีที่ล็อกหมดอายุแล้ว (สำหรับ Scheduled Command)
     */
    public function cleanupExpiredLocks(): int
    {
        $expiredLockTime = Carbon::now()->subMinutes(self::LOCKOUT_DURATION_MINUTES);
        
        $expiredUsers = User::whereNotNull('locked_at')
            ->where('locked_at', '<=', $expiredLockTime)
            ->get();

        foreach ($expiredUsers as $user) {
            $this->unlockAccount($user);
        }

        if ($expiredUsers->count() > 0) {
            Log::info('Expired account locks cleaned up', [
                'count' => $expiredUsers->count()
            ]);
        }

        return $expiredUsers->count();
    }
}
