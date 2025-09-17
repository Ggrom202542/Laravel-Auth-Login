<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// ตรวจสอบ super admin users
$superAdmins = \App\Models\User::where('role', 'super_admin')->get();
echo "Super Admin users:\n";
foreach ($superAdmins as $admin) {
    echo "- ID: {$admin->id}, Username: {$admin->username}, Email: {$admin->email}\n";
}

// ตรวจสอบ route
echo "\nRoute test:\n";
echo "Current URL: http://localhost:8000/super-admin/security/devices\n";
echo "Route name: super-admin.security.devices\n";
echo "Route exists: " . (\Illuminate\Support\Facades\Route::has('super-admin.security.devices') ? 'Yes' : 'No') . "\n";

// ตรวจสอบ middleware
echo "\nMiddleware check:\n";
$route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('super-admin.security.devices');
if ($route) {
    echo "Route middlewares: " . implode(', ', $route->middleware()) . "\n";
}