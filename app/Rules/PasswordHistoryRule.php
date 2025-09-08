<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordHistory;

class PasswordHistoryRule implements ValidationRule
{
    private $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!config('password_policy.history.enabled') || !$this->userId) {
            return;
        }

        $password = $value;
        $historyCount = config('password_policy.history.count');

        // Get user's password history
        $passwordHistories = PasswordHistory::where('user_id', $this->userId)
            ->orderBy('created_at', 'desc')
            ->limit($historyCount)
            ->get();

        // Check against current password
        $user = User::find($this->userId);
        if ($user && Hash::check($password, $user->password)) {
            $fail(str_replace(':count', $historyCount, config('password_policy.messages.in_history')));
            return;
        }

        // Check against password history
        foreach ($passwordHistories as $history) {
            if (Hash::check($password, $history->password_hash)) {
                $fail(str_replace(':count', $historyCount, config('password_policy.messages.in_history')));
                return;
            }
        }

        // Check similarity if enabled
        if (config('password_policy.history.check_similarity')) {
            $this->checkSimilarity($password, $user, $passwordHistories, $fail);
        }
    }

    /**
     * Check password similarity against previous passwords
     */
    private function checkSimilarity(string $newPassword, $user, $passwordHistories, Closure $fail): void
    {
        $threshold = config('password_policy.history.similarity_threshold');
        
        // Check against current password (if user exists and has password)
        if ($user && $user->password) {
            // For similarity check, we'll use a simple approach
            // In production, you might want to use more sophisticated algorithms
            $currentPasswordPlain = $this->getStoredPlainPassword($user->id, $user->updated_at);
            if ($currentPasswordPlain && $this->calculateSimilarity($newPassword, $currentPasswordPlain) >= $threshold) {
                $fail(config('password_policy.messages.too_similar'));
                return;
            }
        }

        // Note: For real password similarity checking, you would need to store
        // reversible encrypted passwords or use other techniques. For security,
        // we'll implement a basic check here.
    }

    /**
     * Calculate similarity percentage between two strings
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);
        
        // Use Levenshtein distance for similarity
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen == 0) return 100;
        
        $distance = levenshtein($str1, $str2);
        return (($maxLen - $distance) / $maxLen) * 100;
    }

    /**
     * Get stored plain password (placeholder - implement based on your security model)
     */
    private function getStoredPlainPassword($userId, $updatedAt): ?string
    {
        // This is a placeholder method. In a real implementation, you would
        // either store encrypted passwords that can be decrypted for comparison,
        // or use other similarity checking methods that don't require plain text.
        return null;
    }
}
