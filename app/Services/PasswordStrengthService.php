<?php

namespace App\Services;

class PasswordStrengthService
{
    /**
     * Analyze password strength
     */
    public function analyze(string $password): array
    {
        $score = 0;
        $feedback = [];
        
        // Length analysis
        $length = strlen($password);
        $minLength = config('password_policy.strength.min_length', 8);
        
        if ($length >= $minLength) {
            $score += 20;
        } else {
            $score += min($length * 2, 19);
            $feedback[] = "รหัสผ่านควรมีความยาวอย่างน้อย {$minLength} ตัวอักษร";
        }

        // Character variety checks
        $checks = [
            'uppercase' => [
                'pattern' => '/[A-Z]/',
                'score' => 15,
                'message' => 'ควรมีตัวอักษรพิมพ์ใหญ่'
            ],
            'lowercase' => [
                'pattern' => '/[a-z]/',
                'score' => 15,
                'message' => 'ควรมีตัวอักษรพิมพ์เล็ก'
            ],
            'numbers' => [
                'pattern' => '/\d/',
                'score' => 15,
                'message' => 'ควรมีตัวเลข'
            ],
            'symbols' => [
                'pattern' => '/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\?]/',
                'score' => 20,
                'message' => 'ควรมีสัญลักษณ์พิเศษ'
            ]
        ];

        $metRequirements = [];
        foreach ($checks as $type => $check) {
            if (preg_match($check['pattern'], $password)) {
                $score += $check['score'];
                $metRequirements[] = $type;
            } else {
                $feedback[] = $check['message'];
            }
        }

        // Unique characters
        $uniqueChars = count(array_unique(str_split($password)));
        $minUnique = config('password_policy.strength.min_unique_chars', 4);
        
        if ($uniqueChars >= $minUnique) {
            $score += 10;
        } else {
            $feedback[] = "ควรมีตัวอักษรที่แตกต่างกันอย่างน้อย {$minUnique} ตัว";
        }

        // Length bonuses
        if ($length >= 12) $score += 5;
        if ($length >= 16) $score += 5;

        // Common patterns penalty
        if ($this->hasCommonPatterns($password)) {
            $score -= 15;
            $feedback[] = 'หลีกเลี่ยงรูปแบบที่ใช้กันทั่วไป';
        }

        // Ensure score is within bounds
        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'level' => $this->getStrengthLevel($score),
            'requirements_met' => $metRequirements,
            'feedback' => $feedback,
            'length' => $length,
            'unique_chars' => $uniqueChars,
        ];
    }

    /**
     * Check for common patterns
     */
    private function hasCommonPatterns(string $password): bool
    {
        $patterns = [
            '/123456/',
            '/password/i',
            '/qwerty/i',
            '/abc123/i',
            '/(.)\1{2,}/', // Repeated characters
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $password)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get strength level information
     */
    private function getStrengthLevel(int $score): array
    {
        $levels = [
            ['min' => 90, 'label' => 'แข็งแกร่งมาก', 'class' => 'very-strong', 'color' => '#007bff'],
            ['min' => 75, 'label' => 'แข็งแกร่ง', 'class' => 'strong', 'color' => '#28a745'],
            ['min' => 50, 'label' => 'ดี', 'class' => 'good', 'color' => '#20c997'],
            ['min' => 25, 'label' => 'ปานกลาง', 'class' => 'fair', 'color' => '#ffc107'],
            ['min' => 1, 'label' => 'อ่อนแอ', 'class' => 'weak', 'color' => '#fd7e14'],
            ['min' => 0, 'label' => 'อ่อนแอมาก', 'class' => 'very-weak', 'color' => '#dc3545'],
        ];

        foreach ($levels as $level) {
            if ($score >= $level['min']) {
                return $level;
            }
        }

        return $levels[count($levels) - 1]; // Default to weakest
    }
}
