<?php
/**
 * Debug script สำหรับทดสอบการอัพเดทข้อมูลผู้ใช้
 * ใช้เพื่อตรวจสอบว่าฟิลด์ทั้งหมดอัพเดทได้หรือไม่
 */

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'laravel_auth',  // แก้ไขชื่อฐานข้อมูลตามที่ใช้จริง
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== Debug User Update ===\n\n";

// ตรวจสอบโครงสร้างตาราง users
echo "1. ตรวจสอบ columns ในตาราง users:\n";
$columns = Capsule::select("SHOW COLUMNS FROM users");
foreach ($columns as $column) {
    echo "   - {$column->Field} ({$column->Type})\n";
}

echo "\n";

// ตรวจสอบผู้ใช้คนแรก
echo "2. ข้อมูลผู้ใช้ตัวอย่าง:\n";
$user = Capsule::table('users')->first();
if ($user) {
    echo "   - ID: {$user->id}\n";
    echo "   - Name: {$user->name}\n";
    echo "   - First Name: " . ($user->first_name ?? 'NULL') . "\n";
    echo "   - Last Name: " . ($user->last_name ?? 'NULL') . "\n";
    echo "   - Prefix: " . ($user->prefix ?? 'NULL') . "\n";
    echo "   - Two Factor Enabled: " . ($user->two_factor_enabled ?? 'NULL') . "\n";
    echo "   - Allowed IP Addresses: " . ($user->allowed_ip_addresses ?? 'NULL') . "\n";
} else {
    echo "   ไม่พบข้อมูลผู้ใช้\n";
}

echo "\n";

// ทดสอบการอัพเดท
if ($user) {
    echo "3. ทดสอบการอัพเดทข้อมูล:\n";
    
    $updateData = [
        'prefix' => 'นาย',
        'first_name' => 'ทดสอบ',
        'last_name' => 'การอัพเดท',
        'name' => 'นาย ทดสอบ การอัพเดท',
        'two_factor_enabled' => true,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $result = Capsule::table('users')
        ->where('id', $user->id)
        ->update($updateData);
    
    echo "   - Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
    // ตรวจสอบข้อมูลหลังอัพเดท
    $updatedUser = Capsule::table('users')->where('id', $user->id)->first();
    echo "   - Updated Name: {$updatedUser->name}\n";
    echo "   - Updated First Name: {$updatedUser->first_name}\n";
    echo "   - Updated Last Name: {$updatedUser->last_name}\n";
    echo "   - Updated Two Factor: {$updatedUser->two_factor_enabled}\n";
}

echo "\n=== Debug Complete ===\n";
?>
