<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Security channels
Broadcast::channel('security.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Admin channels (only for admin and super admin)
Broadcast::channel('admin.security', function ($user) {
    return in_array($user->role, ['admin', 'super_admin']);
});

Broadcast::channel('admin.login-attempts', function ($user) {
    return in_array($user->role, ['admin', 'super_admin']);
});

Broadcast::channel('admin.devices', function ($user) {
    return in_array($user->role, ['admin', 'super_admin']);
});

// Super admin only channels
Broadcast::channel('super-admin.monitoring', function ($user) {
    return $user->role === 'super_admin';
});
