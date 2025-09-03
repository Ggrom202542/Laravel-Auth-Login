<?php

use Illuminate\Support\Facades\DB;

// อัปเดต role field ใน users table ตาม user_roles relationship
echo "=== UPDATING USER ROLE FIELDS ===\n";

// Get users with their primary roles from user_roles table
$userRoles = DB::table('user_roles')
    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
    ->join('users', 'user_roles.user_id', '=', 'users.id')
    ->select('users.id as user_id', 'users.username', 'roles.name as role_name')
    ->get();

foreach ($userRoles as $userRole) {
    echo "Updating {$userRole->username}: role field '{$userRole->role_name}'\n";
    
    DB::table('users')
        ->where('id', $userRole->user_id)
        ->update(['role' => $userRole->role_name]);
}

echo "\n=== VERIFICATION ===\n";
$users = DB::table('users')->get();
foreach ($users as $user) {
    echo "ID: {$user->id}, Username: {$user->username}, Role: {$user->role}\n";
}
