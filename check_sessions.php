<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Session Data Check ===\n";
echo "Total Sessions: " . \App\Models\UserSession::count() . "\n";
echo "Active Sessions: " . \App\Models\UserSession::where('is_active', true)->count() . "\n";
echo "Total Users: " . \App\Models\User::count() . "\n";

$users = \App\Models\User::get(['id', 'username', 'role']);
echo "\nUsers in system:\n";
foreach ($users as $user) {
    echo "- {$user->username} ({$user->role})\n";
}

echo "\nSessions by user:\n";
$sessions = \App\Models\UserSession::with('user')->get(['user_id', 'is_active', 'ip_address', 'created_at']);
foreach ($sessions as $session) {
    $user = $session->user;
    $status = $session->is_active ? 'Active' : 'Inactive';
    echo "- {$user->username}: {$status} from {$session->ip_address} at {$session->created_at}\n";
}

if (\App\Models\UserSession::count() == 0) {
    echo "\n⚠️  No sessions found! This might be why you see no data.\n";
    echo "Sessions are created when users login through the web interface.\n";
}
