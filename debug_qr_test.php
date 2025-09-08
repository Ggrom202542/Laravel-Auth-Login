<?php
require_once 'vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

echo "🔍 Testing QR Code Generation Process\n";
echo "====================================\n\n";

try {
    // Test 1: Google2FA Secret Generation
    echo "1. Testing Google2FA Secret Generation...\n";
    $google2fa = new Google2FA();
    $secret = $google2fa->generateSecretKey();
    echo "✅ Secret Generated: {$secret}\n\n";
    
    // Test 2: QR Code URL Generation
    echo "2. Testing QR Code URL Generation...\n";
    $qrCodeUrl = $google2fa->getQRCodeUrl(
        'Laravel Auth Test',
        'test@example.com',
        $secret
    );
    echo "✅ QR Code URL: {$qrCodeUrl}\n\n";
    
    // Test 3: BaconQrCode SVG Generation
    echo "3. Testing BaconQrCode SVG Generation...\n";
    
    // Method 1: Basic SVG
    $renderer = new ImageRenderer(
        new RendererStyle(200),
        new SvgImageBackEnd()
    );
    
    $writer = new Writer($renderer);
    $qrCodeSvg = $writer->writeString($qrCodeUrl);
    
    echo "✅ SVG Generated: " . strlen($qrCodeSvg) . " characters\n";
    echo "📝 SVG Preview: " . substr($qrCodeSvg, 0, 100) . "...\n\n";
    
    // Test 4: Base64 Encoding
    echo "4. Testing Base64 Encoding...\n";
    $base64QrCode = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);
    echo "✅ Base64 Generated: " . strlen($base64QrCode) . " characters\n";
    echo "📝 Base64 Preview: " . substr($base64QrCode, 0, 50) . "...\n\n";
    
    // Test 5: Save Files for Manual Check
    echo "5. Saving Files for Manual Verification...\n";
    
    // Save SVG
    file_put_contents('debug_qr.svg', $qrCodeSvg);
    echo "✅ SVG saved to: debug_qr.svg\n";
    
    // Save HTML with embedded SVG
    $html = "<!DOCTYPE html>
<html>
<head>
    <title>QR Code Test</title>
    <style>
        .container { text-align: center; margin: 50px; }
        .qr-container { display: inline-block; padding: 20px; border: 2px solid #ccc; border-radius: 10px; margin: 20px; }
    </style>
</head>
<body>
    <div class=\"container\">
        <h1>QR Code Tests</h1>
        
        <div class=\"qr-container\">
            <h3>Method 1: Direct SVG</h3>
            {$qrCodeSvg}
        </div>
        
        <div class=\"qr-container\">
            <h3>Method 2: Base64 Data URL</h3>
            <img src=\"{$base64QrCode}\" alt=\"QR Code Base64\" style=\"max-width: 200px;\">
        </div>
        
        <div class=\"qr-container\">
            <h3>Method 3: Google Charts (Fallback)</h3>
            <img src=\"https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($qrCodeUrl) . "\" alt=\"QR Code Google Charts\">
        </div>
        
        <p><strong>Secret Key:</strong> {$secret}</p>
        <p><strong>QR URL:</strong> {$qrCodeUrl}</p>
    </div>
</body>
</html>";
    
    file_put_contents('debug_qr_test.html', $html);
    echo "✅ HTML test page saved to: debug_qr_test.html\n\n";
    
    // Test 6: Current OTP Verification
    echo "6. Testing Current OTP...\n";
    $currentOtp = $google2fa->getCurrentOtp($secret);
    echo "✅ Current OTP: {$currentOtp}\n";
    
    $isValid = $google2fa->verifyKey($secret, $currentOtp);
    echo ($isValid ? "✅" : "❌") . " OTP Verification: " . ($isValid ? "Valid" : "Invalid") . "\n\n";
    
    echo "🎉 All tests completed successfully!\n";
    echo "📁 Check these files:\n";
    echo "   - debug_qr.svg (raw SVG)\n";
    echo "   - debug_qr_test.html (HTML test page)\n\n";
    echo "🌐 Open debug_qr_test.html in browser to see all QR code methods\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    echo "\n🔧 Stack Trace:\n" . $e->getTraceAsString() . "\n";
}
