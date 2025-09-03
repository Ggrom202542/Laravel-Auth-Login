<?php

use Illuminate\Support\Facades\DB;

// เช็คข้อมูล roles
echo "=== ROLES TABLE ===\n";
$roles = DB::table('roles')->get();
if ($roles->count() > 0) {
    foreach ($roles as $role) {
        echo "ID: {$role->id}, Name: {$role->name}, Display Name: {$role->display_name}\n";
    }
} else {
    echo "No roles found in database\n";
}

echo "\n=== USERS TABLE ===\n";
$users = DB::table('users')->get();
if ($users->count() > 0) {
    foreach ($users as $user) {
        echo "ID: {$user->id}, Username: {$user->username}, Role: " . ($user->role ?? 'null') . "\n";
    }
} else {
    echo "No users found in database\n";
}

echo "\n=== USER_ROLES TABLE ===\n";
$userRoles = DB::table('user_roles')
    ->join('users', 'user_roles.user_id', '=', 'users.id')
    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
    ->select('users.username', 'roles.name as role_name', 'user_roles.assigned_at')
    ->get();

if ($userRoles->count() > 0) {
    foreach ($userRoles as $ur) {
        echo "User: {$ur->username}, Role: {$ur->role_name}, Assigned: {$ur->assigned_at}\n";
    }
} else {
    echo "No user role assignments found\n";
}
