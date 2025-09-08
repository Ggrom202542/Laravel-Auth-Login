<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 2FA & Recovery Codes Debug ===\n\n";

// Config check
echo "Config Check:\n";
echo "TWO_FACTOR_ENABLED: " . (config('auth.two_factor.enabled') ? 'true' : 'false') . "\n";
echo "TWO_FACTOR_ENFORCE_ALL: " . (config('auth.two_factor.enforce_for_all_users') ? 'true' : 'false') . "\n\n";

// Users check
$users = App\Models\User::all();
echo "Users Overview:\n";
echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "--- User: {$user->username} (ID: {$user->id}) ---\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "2FA Enabled: " . ($user->google2fa_enabled ? 'YES' : 'NO') . "\n";
    echo "Has Secret: " . ($user->google2fa_secret ? 'YES' : 'NO') . "\n";
    echo "Has Recovery Codes: " . ($user->hasRecoveryCodes() ? 'YES' : 'NO') . "\n";
    
    if ($user->hasRecoveryCodes()) {
        echo "Recovery Codes Count: " . count($user->recovery_codes) . "\n";
        echo "Recovery Codes:\n";
        foreach ($user->recovery_codes as $i => $code) {
            echo "  " . ($i + 1) . ". {$code}\n";
        }
        echo "Generated At: " . ($user->recovery_codes_generated_at ? $user->recovery_codes_generated_at->format('Y-m-d H:i:s') : 'Unknown') . "\n";
    }
    echo "\n";
}

echo "=== End Debug ===\n";
