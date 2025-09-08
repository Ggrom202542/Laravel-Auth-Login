#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 2FA Status Check ===\n";
echo "TWO_FACTOR_ENABLED: " . (config('auth.two_factor.enabled') ? 'true' : 'false') . "\n";
echo "TWO_FACTOR_ENFORCE_ALL: " . (config('auth.two_factor.enforce_for_all_users') ? 'true' : 'false') . "\n\n";

$users = App\Models\User::all();
echo "Total users: " . $users->count() . "\n";
echo "Users with 2FA enabled: " . $users->where('google2fa_enabled', true)->count() . "\n\n";

echo "User Details:\n";
foreach ($users as $user) {
    $has2fa = $user->google2fa_enabled ? '✅' : '❌';
    $hasSecret = $user->google2fa_secret ? '🔑' : '🚫';
    echo "- {$user->username} (ID: {$user->id}) | 2FA: {$has2fa} | Secret: {$hasSecret} | Role: {$user->role}\n";
}

echo "\n=== End Check ===\n";
