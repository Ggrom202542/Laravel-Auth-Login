<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "UserSession count: " . \App\Models\UserSession::count() . PHP_EOL;

$session = \App\Models\UserSession::with('user')->first();
if ($session) {
    echo "First session ID: " . $session->id . PHP_EOL;
    echo "Has user: " . ($session->user ? "Yes ({$session->user->username})" : "No") . PHP_EOL;
    echo "Device name: " . ($session->device_name ?? 'No device name') . PHP_EOL;
    echo "Browser: " . ($session->browser ?? 'No browser') . PHP_EOL;
    echo "Platform: " . ($session->platform ?? 'No platform') . PHP_EOL;
    echo "Country: " . ($session->location_country ?? 'No country') . PHP_EOL;
} else {
    echo "No sessions found" . PHP_EOL;
}