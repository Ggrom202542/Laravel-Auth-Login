<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class PasswordExpirationService
{
    /**
     * Set password expiration for a user based on policy
     *
     * @param User $user
     * @param string $password
     * @return void
     */
    public function setPasswordExpiration(User $user, string $password = null): void
    {
        $config = config('password_policy.expiration');
        
        if (!$config['enabled']) {
            return;
        }

        $now = Carbon::now();
        
        // Update password changed date
        $user->password_changed_at = $now;
        
        // Set expiration date based on policy
        $expirationDays = $config['days'];
        $user->password_expires_at = $now->copy()->addDays($expirationDays);
        
        // Enable password expiration for this user
        $user->password_expiration_enabled = true;
        
        // Reset warning timestamp
        $user->password_warned_at = null;
        
        $user->save();
    }

    /**
     * Check if user's password is expired
     *
     * @param User $user
     * @return bool
     */
    public function isPasswordExpired(User $user): bool
    {
        if (!$user->password_expiration_enabled || !$user->password_expires_at) {
            return false;
        }

        return Carbon::now()->greaterThan($user->password_expires_at);
    }

    /**
     * Get days until password expires
     *
     * @param User $user
     * @return int|null
     */
    public function getDaysUntilExpiration(User $user): ?int
    {
        if (!$user->password_expiration_enabled || !$user->password_expires_at) {
            return null;
        }

        $daysLeft = Carbon::now()->diffInDays($user->password_expires_at, false);
        return $daysLeft >= 0 ? $daysLeft : 0;
    }

    /**
     * Check if password should show warning
     *
     * @param User $user
     * @return bool
     */
    public function shouldShowWarning(User $user): bool
    {
        if (!$user->password_expiration_enabled || !$user->password_expires_at) {
            return false;
        }

        $config = config('password_policy.expiration');
        $warningDays = $config['warning_days'] ?? 7;
        
        $daysUntilExpiration = $this->getDaysUntilExpiration($user);
        
        return $daysUntilExpiration !== null && $daysUntilExpiration <= $warningDays;
    }

    /**
     * Update user's password with expiration tracking
     *
     * @param User $user
     * @param string $newPassword
     * @return void
     */
    public function updatePassword(User $user, string $newPassword): void
    {
        $user->password = Hash::make($newPassword);
        $this->setPasswordExpiration($user);
    }

    /**
     * Disable password expiration for a user
     *
     * @param User $user
     * @return void
     */
    public function disablePasswordExpiration(User $user): void
    {
        $user->password_expiration_enabled = false;
        $user->password_expires_at = null;
        $user->password_warned_at = null;
        $user->save();
    }

    /**
     * Enable password expiration for a user
     *
     * @param User $user
     * @return void
     */
    public function enablePasswordExpiration(User $user): void
    {
        $user->password_expiration_enabled = true;
        $this->setPasswordExpiration($user);
    }

    /**
     * Get users with expiring passwords
     *
     * @param int $days Number of days ahead to check
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersWithExpiringPasswords(int $days = 7)
    {
        $futureDate = Carbon::now()->addDays($days);
        
        return User::where('password_expiration_enabled', true)
            ->where('password_expires_at', '<=', $futureDate)
            ->where('password_expires_at', '>', Carbon::now())
            ->get();
    }

    /**
     * Get users with expired passwords
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersWithExpiredPasswords()
    {
        return User::where('password_expiration_enabled', true)
            ->where('password_expires_at', '<', Carbon::now())
            ->get();
    }

    /**
     * Reset password expiration for multiple users
     *
     * @param array $userIds
     * @return int Number of updated users
     */
    public function resetPasswordExpirationForUsers(array $userIds): int
    {
        $config = config('password_policy.expiration');
        $expirationDays = $config['days'];
        $now = Carbon::now();

        return User::whereIn('id', $userIds)
            ->update([
                'password_expires_at' => $now->copy()->addDays($expirationDays),
                'password_warned_at' => null,
                'password_changed_at' => $now,
            ]);
    }

    /**
     * Get password expiration statistics
     *
     * @return array
     */
    public function getExpirationStatistics(): array
    {
        $totalUsers = User::where('password_expiration_enabled', true)->count();
        $expiredUsers = $this->getUsersWithExpiredPasswords()->count();
        $expiringUsers = $this->getUsersWithExpiringPasswords()->count();
        
        return [
            'total_users_with_expiration' => $totalUsers,
            'expired_passwords' => $expiredUsers,
            'expiring_soon' => $expiringUsers,
            'percentage_expired' => $totalUsers > 0 ? round(($expiredUsers / $totalUsers) * 100, 2) : 0,
        ];
    }
}
