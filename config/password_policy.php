<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Password Policy Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all password policy settings for
    | the Laravel Auth Login system. These settings control password
    | strength requirements, history, and expiration policies.
    |
    */

    'enabled' => env('PASSWORD_POLICY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Password Strength Requirements
    |--------------------------------------------------------------------------
    */
    'strength' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'max_length' => env('PASSWORD_MAX_LENGTH', 128),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', true),
        'allowed_symbols' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
        'min_unique_chars' => env('PASSWORD_MIN_UNIQUE_CHARS', 4),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password History
    |--------------------------------------------------------------------------
    */
    'history' => [
        'enabled' => env('PASSWORD_HISTORY_ENABLED', true),
        'count' => env('PASSWORD_HISTORY_COUNT', 5), // จำนวนรหัสผ่านเก่าที่จดจำ
        'check_similarity' => env('PASSWORD_CHECK_SIMILARITY', true),
        'similarity_threshold' => env('PASSWORD_SIMILARITY_THRESHOLD', 80), // เปอร์เซ็นต์
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Expiration
    |--------------------------------------------------------------------------
    */
    'expiration' => [
        'enabled' => env('PASSWORD_EXPIRATION_ENABLED', false),
        'days' => env('PASSWORD_EXPIRATION_DAYS', 90),
        'warning_days' => env('PASSWORD_EXPIRATION_WARNING_DAYS', 7),
        'grace_period_days' => env('PASSWORD_EXPIRATION_GRACE_DAYS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Account Lockout Policy
    |--------------------------------------------------------------------------
    */
    'lockout' => [
        'enabled' => env('PASSWORD_LOCKOUT_ENABLED', true),
        'max_attempts' => env('PASSWORD_LOCKOUT_MAX_ATTEMPTS', 5),
        'lockout_duration' => env('PASSWORD_LOCKOUT_DURATION', 15), // นาที
        'reset_time' => env('PASSWORD_LOCKOUT_RESET_TIME', 60), // นาที
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Strength Scoring
    |--------------------------------------------------------------------------
    */
    'scoring' => [
        'very_weak' => 0,
        'weak' => 25,
        'fair' => 50,
        'good' => 75,
        'strong' => 90,
        'very_strong' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Common Passwords Blacklist
    |--------------------------------------------------------------------------
    */
    'blacklist' => [
        'enabled' => env('PASSWORD_BLACKLIST_ENABLED', true),
        'file_path' => storage_path('app/security/common_passwords.txt'),
        'custom_words' => [
            'password', 'password123', '123456', 'admin', 'user',
            'welcome', 'login', 'test', 'demo', 'guest',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Complexity Messages
    |--------------------------------------------------------------------------
    */
    'messages' => [
        'too_short' => 'รหัสผ่านต้องมีความยาวอย่างน้อย :min ตัวอักษร',
        'too_long' => 'รหัสผ่านต้องมีความยาวไม่เกิน :max ตัวอักษร',
        'missing_uppercase' => 'รหัสผ่านต้องมีตัวอักษรพิมพ์ใหญ่อย่างน้อย 1 ตัว',
        'missing_lowercase' => 'รหัสผ่านต้องมีตัวอักษรพิมพ์เล็กอย่างน้อย 1 ตัว',
        'missing_numbers' => 'รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว',
        'missing_symbols' => 'รหัสผ่านต้องมีสัญลักษณ์พิเศษอย่างน้อย 1 ตัว',
        'insufficient_unique' => 'รหัสผ่านต้องมีตัวอักษรที่แตกต่างกันอย่างน้อย :min ตัว',
        'in_history' => 'ไม่สามารถใช้รหัสผ่านที่เคยใช้ใน :count ครั้งล่าสุดได้',
        'too_similar' => 'รหัสผ่านใหม่คล้ายกับรหัสผ่านเก่ามากเกินไป',
        'in_blacklist' => 'รหัสผ่านนี้ใช้งานง่ายเกินไป กรุณาเลือกรหัสผ่านที่ปลอดภัยกว่า',
        'expired' => 'รหัสผ่านของคุณหมดอายุแล้ว กรุณาเปลี่ยนรหัสผ่านใหม่',
        'expires_soon' => 'รหัสผ่านของคุณจะหมดอายุใน :days วัน',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Strength Labels
    |--------------------------------------------------------------------------
    */
    'strength_labels' => [
        'very_weak' => 'อ่อนแอมาก',
        'weak' => 'อ่อนแอ',
        'fair' => 'ปานกลาง',
        'good' => 'ดี',
        'strong' => 'แข็งแกร่ง',
        'very_strong' => 'แข็งแกร่งมาก',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Strength Colors (for UI)
    |--------------------------------------------------------------------------
    */
    'strength_colors' => [
        'very_weak' => '#dc3545',   // Red
        'weak' => '#fd7e14',        // Orange
        'fair' => '#ffc107',        // Yellow
        'good' => '#20c997',        // Teal
        'strong' => '#28a745',      // Green
        'very_strong' => '#007bff', // Blue
    ],
];
