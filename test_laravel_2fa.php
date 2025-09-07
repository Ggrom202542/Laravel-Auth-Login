<?php
require_once 'vendor/autoload.php';

// Laravel bootstrap
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Initialize Laravel
$request = Illuminate\Http\Request::capture();
$kernel->bootstrap();

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

echo "🔍 Checking Laravel 2FA Setup\n";
echo "=============================\n\n";

try {
    // Get first user
    $user = User::first();
    if (!$user) {
        echo "❌ No users found in database\n";
        exit(1);
    }
    
    echo "📋 User Info:\n";
    echo "- Email: " . $user->email . "\n";
    echo "- Has Secret: " . ($user->google2fa_secret ? 'Yes (' . substr($user->google2fa_secret, 0, 8) . '...)' : 'No') . "\n";
    echo "- 2FA Enabled: " . ($user->google2fa_enabled ? 'Yes' : 'No') . "\n";
    echo "- 2FA Confirmed: " . ($user->google2fa_confirmed_at ? 'Yes' : 'No') . "\n\n";
    
    // Test helper methods
    echo "🔧 Testing User Helper Methods:\n";
    echo "- hasTwoFactorEnabled(): " . ($user->hasTwoFactorEnabled() ? 'Yes' : 'No') . "\n";
    echo "- hasTwoFactorSecret(): " . ($user->hasTwoFactorSecret() ? 'Yes' : 'No') . "\n";
    echo "- hasTwoFactorConfirmed(): " . ($user->hasTwoFactorConfirmed() ? 'Yes' : 'No') . "\n\n";
    
    // Test QR Code in Laravel context
    echo "🔍 Testing QR Code Generation in Laravel:\n";
    
    if (!$user->hasTwoFactorSecret()) {
        echo "⚠️  No secret key found. Generating one...\n";
        $google2fa = new Google2FA();
        $user->google2fa_secret = $google2fa->generateSecretKey();
        $user->save();
        echo "✅ Secret key generated and saved\n";
    }
    
    // Test TwoFactorController method
    $controller = new App\Http\Controllers\Auth\TwoFactorController();
    
    // Use reflection to test private method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('generateQrCode');
    $method->setAccessible(true);
    
    $google2fa = new Google2FA();
    $qrCodeUrl = $google2fa->getQRCodeUrl(
        config('app.name'),
        $user->email,
        $user->google2fa_secret
    );
    
    echo "- QR Code URL: " . substr($qrCodeUrl, 0, 50) . "...\n";
    
    $qrCode = $method->invokeArgs($controller, [$qrCodeUrl]);
    
    if ($qrCode) {
        echo "✅ QR Code generated successfully: " . strlen($qrCode) . " characters\n";
        echo "- Starts with: " . substr($qrCode, 0, 30) . "...\n";
    } else {
        echo "❌ QR Code generation failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
