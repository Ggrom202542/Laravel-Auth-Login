<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordPolicy implements ValidationRule
{
    private $errors = [];
    
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!config('password_policy.enabled')) {
            return;
        }

        $this->errors = [];
        $password = $value;

        // Check all password policy rules
        $this->checkLength($password);
        $this->checkComplexity($password);
        $this->checkBlacklist($password);
        $this->checkUniqueCharacters($password);

        // If there are any errors, fail validation
        if (!empty($this->errors)) {
            $fail(implode(' ', $this->errors));
        }
    }

    /**
     * Check password length requirements
     */
    private function checkLength(string $password): void
    {
        $minLength = config('password_policy.strength.min_length');
        $maxLength = config('password_policy.strength.max_length');

        if (strlen($password) < $minLength) {
            $this->errors[] = str_replace(':min', $minLength, config('password_policy.messages.too_short'));
        }

        if (strlen($password) > $maxLength) {
            $this->errors[] = str_replace(':max', $maxLength, config('password_policy.messages.too_long'));
        }
    }

    /**
     * Check password complexity requirements
     */
    private function checkComplexity(string $password): void
    {
        // Check for uppercase letters
        if (config('password_policy.strength.require_uppercase') && !preg_match('/[A-Z]/', $password)) {
            $this->errors[] = config('password_policy.messages.missing_uppercase');
        }

        // Check for lowercase letters
        if (config('password_policy.strength.require_lowercase') && !preg_match('/[a-z]/', $password)) {
            $this->errors[] = config('password_policy.messages.missing_lowercase');
        }

        // Check for numbers
        if (config('password_policy.strength.require_numbers') && !preg_match('/\d/', $password)) {
            $this->errors[] = config('password_policy.messages.missing_numbers');
        }

        // Check for symbols
        if (config('password_policy.strength.require_symbols')) {
            $allowedSymbols = preg_quote(config('password_policy.strength.allowed_symbols'), '/');
            if (!preg_match('/[' . $allowedSymbols . ']/', $password)) {
                $this->errors[] = config('password_policy.messages.missing_symbols');
            }
        }
    }

    /**
     * Check if password is in blacklist
     */
    private function checkBlacklist(string $password): void
    {
        if (!config('password_policy.blacklist.enabled')) {
            return;
        }

        $customWords = config('password_policy.blacklist.custom_words', []);
        $lowercasePassword = strtolower($password);

        foreach ($customWords as $word) {
            if ($lowercasePassword === strtolower($word) || 
                str_contains($lowercasePassword, strtolower($word))) {
                $this->errors[] = config('password_policy.messages.in_blacklist');
                break;
            }
        }

        // Check against common passwords file (if exists)
        $filePath = config('password_policy.blacklist.file_path');
        if (file_exists($filePath)) {
            $commonPasswords = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (in_array($lowercasePassword, array_map('strtolower', $commonPasswords))) {
                $this->errors[] = config('password_policy.messages.in_blacklist');
            }
        }
    }

    /**
     * Check minimum unique characters
     */
    private function checkUniqueCharacters(string $password): void
    {
        $minUnique = config('password_policy.strength.min_unique_chars');
        $uniqueChars = count(array_unique(str_split($password)));

        if ($uniqueChars < $minUnique) {
            $this->errors[] = str_replace(':min', $minUnique, config('password_policy.messages.insufficient_unique'));
        }
    }

    /**
     * Get all validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
