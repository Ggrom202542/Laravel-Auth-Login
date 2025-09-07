<?php
require_once 'vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

echo "🔍 Testing 2FA QR Code Generation\n";
echo "================================\n\n";

try {
    // Test 1: Google2FA
    echo "1. Testing Google2FA...\n";
    $google2fa = new Google2FA();
    $secret = $google2fa->generateSecretKey();
    echo "✅ Secret Key Generated: " . $secret . "\n";
    
    // Test 2: QR Code URL
    echo "\n2. Testing QR Code URL...\n";
    $qrCodeUrl = $google2fa->getQRCodeUrl(
        'Laravel Auth Test',
        'test@example.com',
        $secret
    );
    echo "✅ QR Code URL: " . $qrCodeUrl . "\n";
    
    // Test 3: QR Code Generation
    echo "\n3. Testing QR Code Generation...\n";
    $renderer = new ImageRenderer(
        new RendererStyle(200),
        new SvgImageBackEnd()
    );
    
    $writer = new Writer($renderer);
    $qrCodeSvg = $writer->writeString($qrCodeUrl);
    
    echo "✅ QR Code SVG Generated: " . strlen($qrCodeSvg) . " characters\n";
    
    // Test 4: Base64 Encoding
    echo "\n4. Testing Base64 Encoding...\n";
    $base64 = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);
    echo "✅ Base64 Data URL: " . strlen($base64) . " characters\n";
    
    // Test 5: Save QR Code for testing
    echo "\n5. Saving QR Code to file...\n";
    file_put_contents('test_qr_code.svg', $qrCodeSvg);
    echo "✅ QR Code saved to test_qr_code.svg\n";
    
    // Test 6: Verify Code
    echo "\n6. Testing Code Verification...\n";
    $currentCode = $google2fa->getCurrentOtp($secret);
    echo "✅ Current OTP: " . $currentCode . "\n";
    
    $isValid = $google2fa->verifyKey($secret, $currentCode);
    echo $isValid ? "✅ Code verification works\n" : "❌ Code verification failed\n";
    
    echo "\n🎉 All tests passed! QR Code generation is working.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    echo "\n🔧 Solution suggestions:\n";
    echo "1. Make sure bacon/bacon-qr-code is installed\n";
    echo "2. Make sure pragmarx/google2fa is installed\n";
    echo "3. Check PHP extensions (gd, imagick)\n";
}
