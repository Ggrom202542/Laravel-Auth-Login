<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Route, Auth, Http, Log, DB};
use App\Http\Controllers\Auth\{LoginController, RegisterController, TwoFactorController};
use App\Http\Controllers\User\DashboardController as UserDashboard;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserManagementController as AdminUserManagement;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Guest Routes (ไม่ต้องเข้าสู่ระบบ)
|--------------------------------------------------------------------------
*/

// Welcome page - accessible to everyone
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Simple debug route
Route::get('/test', function () {
    return 'Test route working!';
});

Route::get('/debug-session', function (Request $request) {
    return response()->json([
        'session_id' => $request->session()->getId(),
        'auth_check' => auth()->check(),
        'user_id' => auth()->id(),
        '2fa_user_id' => $request->session()->get('2fa:user:id'),
        '2fa_timestamp' => $request->session()->get('2fa:login:timestamp'),
        'session_keys' => array_keys($request->session()->all())
    ]);
});

Route::group(['middleware' => 'guest'], function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('register/pending', [RegisterController::class, 'showPendingApproval'])->name('register.pending');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Password Change Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth'], 'prefix' => 'password', 'as' => 'password.'], function () {
    Route::get('/change', [App\Http\Controllers\Auth\ChangePasswordController::class, 'showChangeForm'])->name('change');
    Route::post('/update', [App\Http\Controllers\Auth\ChangePasswordController::class, 'updatePassword'])->name('update');
    Route::get('/status', [App\Http\Controllers\PasswordStatusController::class, 'show'])->name('status');
});

/*
|--------------------------------------------------------------------------
| Two-Factor Authentication Routes
|--------------------------------------------------------------------------
*/

// Two-Factor Challenge routes (ใช้ custom middleware สำหรับ 2FA)
Route::group(['prefix' => '2fa', 'as' => '2fa.', 'middleware' => ['web', '2fa.challenge']], function () {
    Route::get('/challenge', [TwoFactorController::class, 'challenge'])->name('challenge');
    Route::post('/verify', [TwoFactorController::class, 'verify'])->name('verify');
    Route::get('/recovery', [TwoFactorController::class, 'recoveryForm'])->name('recovery');
    Route::post('/recovery/verify', [TwoFactorController::class, 'verifyRecovery'])->name('recovery.verify');
});

// Two-Factor Setup routes (ต้อง auth middleware)
Route::group(['middleware' => ['auth'], 'prefix' => '2fa', 'as' => '2fa.'], function () {
    Route::get('/setup', [TwoFactorController::class, 'setup'])->name('setup');
    Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
    Route::post('/confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
    Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
    Route::post('/recovery/generate', [TwoFactorController::class, 'generateRecoveryCodes'])->name('recovery.generate');
});

/*
|--------------------------------------------------------------------------
| Notification Routes (สำหรับผู้ใช้ที่เข้าสู่ระบบแล้ว)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth']], function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    
    // Test route (development only)
    Route::post('/notifications/test', [NotificationController::class, 'testNotification'])->name('notifications.test');
});

// Redirect legacy /home route to dashboard
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware('auth')->name('home');

// Main dashboard route with role-based redirection
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    switch ($user->role) {
        case 'super_admin':
            return redirect()->route('super-admin.dashboard');
        case 'admin':
            return redirect()->route('admin.dashboard');
        case 'user':
            return redirect()->route('user.dashboard');
        default:
            return redirect()->route('user.dashboard');
    }
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Registration Approval Status Routes
|--------------------------------------------------------------------------
*/
Route::get('/register/pending', [App\Http\Controllers\ApprovalStatusController::class, 'pending'])->name('register.pending');
Route::get('/approval-status/{token}', [App\Http\Controllers\ApprovalStatusController::class, 'show'])->name('approval.status');

/*
|--------------------------------------------------------------------------
| Profile Routes (สำหรับผู้ใช้ที่เข้าสู่ระบบแล้ว)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'password.expiration'], 'prefix' => 'profile', 'as' => 'profile.'], function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
    Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar'])->name('upload-avatar');
    Route::delete('/delete-avatar', [ProfileController::class, 'deleteAvatar'])->name('delete-avatar');
    
    // Settings Routes
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings/update', [ProfileController::class, 'updateSettings'])->name('update-settings');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
});

/*
|--------------------------------------------------------------------------
| User Routes (บทบาท: user)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'role:user', 'log.activity', 'password.expiration'], 'prefix' => 'user', 'as' => 'user.'], function () {
    Route::get('/dashboard', [UserDashboard::class, 'index'])->name('dashboard');
    
    // User Security Routes
    Route::group(['prefix' => 'security', 'as' => 'security.'], function () {
        Route::get('/', [App\Http\Controllers\User\SecurityController::class, 'index'])->name('index');
        Route::get('/devices', [App\Http\Controllers\User\SecurityController::class, 'devices'])->name('devices');
        Route::get('/login-history', [App\Http\Controllers\User\SecurityController::class, 'loginHistory'])->name('login-history');
        Route::get('/alerts', [App\Http\Controllers\User\SecurityController::class, 'securityAlerts'])->name('alerts');
        Route::get('/export', [App\Http\Controllers\User\SecurityController::class, 'exportSecurityData'])->name('export');
        Route::post('/update-settings', [App\Http\Controllers\User\SecurityController::class, 'updateSecuritySettings'])->name('update-settings');
        
        // API endpoints
        Route::get('/api/stats', [App\Http\Controllers\User\SecurityController::class, 'getSecurityStats'])->name('api.stats');
        
        // Device Management
        Route::post('/devices/{device}/trust', [App\Http\Controllers\User\SecurityController::class, 'trustDevice'])->name('devices.trust');
        Route::delete('/devices/{device}', [App\Http\Controllers\User\SecurityController::class, 'removeDevice'])->name('devices.remove');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes (บทบาท: admin, super_admin)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'role:admin,super_admin', 'log.activity', 'password.expiration'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    
    // Registration Approval Routes
    Route::group(['prefix' => 'approvals', 'as' => 'approvals.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'index'])->name('index');
        Route::get('/{approval}', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'show'])->name('show');
        Route::post('/{approval}/approve', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'approve'])->name('approve');
        Route::post('/{approval}/reject', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'reject'])->name('reject');
        Route::post('/bulk-action', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'bulkAction'])->name('bulk-action');
        Route::delete('/{approval}', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'destroy'])->name('destroy');
    });
    
    // User Management Routes สำหรับ Admin
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        // Main CRUD routes
        Route::get('/', [AdminUserManagement::class, 'index'])->name('index');
        Route::get('/{user}', [AdminUserManagement::class, 'show'])->name('show');
        Route::get('/{user}/edit', [AdminUserManagement::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUserManagement::class, 'update'])->name('update');
        
        // AJAX routes for status management
        Route::patch('/{user}/toggle-status', [AdminUserManagement::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{user}/reset-password', [AdminUserManagement::class, 'resetPassword'])->name('reset-password');
        
        // Statistics API route
        Route::get('/api/statistics', [AdminUserManagement::class, 'getUserStatistics'])->name('statistics');
    });

    // Security Management Routes
    Route::group(['prefix' => 'security', 'as' => 'security.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\SecurityController::class, 'index'])->name('index');
        Route::post('/users/{user}/unlock', [App\Http\Controllers\Admin\SecurityController::class, 'unlockAccount'])->name('unlock');
        Route::post('/users/{user}/lock', [App\Http\Controllers\Admin\SecurityController::class, 'lockAccount'])->name('lock');
        Route::post('/users/{user}/reset-attempts', [App\Http\Controllers\Admin\SecurityController::class, 'resetFailedAttempts'])->name('reset-attempts');
        Route::get('/users/{user}/details', [App\Http\Controllers\Admin\SecurityController::class, 'userSecurityDetails'])->name('user-details');
        Route::put('/users/{user}/security', [App\Http\Controllers\Admin\SecurityController::class, 'updateUserSecurity'])->name('update-user-security');
        Route::get('/report', [App\Http\Controllers\Admin\SecurityController::class, 'securityReport'])->name('report');
        Route::post('/cleanup-expired', [App\Http\Controllers\Admin\SecurityController::class, 'cleanupExpiredLocks'])->name('cleanup-expired');
        
        // Device Management Routes
        Route::get('/devices', [App\Http\Controllers\Admin\SecurityController::class, 'devices'])->name('devices');
        Route::delete('/devices/remove', [App\Http\Controllers\Admin\SecurityController::class, 'removeDevice'])->name('devices.remove');
        
        // Suspicious Login Detection Routes
        Route::get('/suspicious-logins', [App\Http\Controllers\Admin\SecurityController::class, 'suspiciousLogins'])->name('suspicious-logins');
        Route::get('/login-attempt/{loginAttempt}/details', [App\Http\Controllers\Admin\SecurityController::class, 'loginAttemptDetails'])->name('login-attempt.details');
        Route::post('/login-attempt/{loginAttempt}/investigate', [App\Http\Controllers\Admin\SecurityController::class, 'markAsInvestigated'])->name('login-attempt.investigate');
        
        // IP Management Routes
        Route::group(['prefix' => 'ip', 'as' => 'ip.'], function () {
            Route::get('/', [App\Http\Controllers\Admin\SecurityController::class, 'ipManagement'])->name('index');
            Route::post('/store', [App\Http\Controllers\Admin\SecurityController::class, 'storeIpRestriction'])->name('store');
            Route::get('/{id}/show', [App\Http\Controllers\Admin\SecurityController::class, 'showIpDetails'])->name('show');
            Route::post('/{ip}/allow', [App\Http\Controllers\Admin\SecurityController::class, 'allowIp'])->name('allow');
            Route::post('/{ip}/block', [App\Http\Controllers\Admin\SecurityController::class, 'blockIp'])->name('block');
            Route::delete('/{ip}/destroy', [App\Http\Controllers\Admin\SecurityController::class, 'destroyIp'])->name('destroy');
            Route::get('/export', [App\Http\Controllers\Admin\SecurityController::class, 'exportIpRules'])->name('export');
            Route::post('/blacklist', [App\Http\Controllers\Admin\SecurityController::class, 'addToBlacklist'])->name('blacklist');
            Route::post('/whitelist', [App\Http\Controllers\Admin\SecurityController::class, 'addToWhitelist'])->name('whitelist');
            Route::delete('/remove', [App\Http\Controllers\Admin\SecurityController::class, 'removeIpRestriction'])->name('remove');
            Route::get('/{ip}/details', [App\Http\Controllers\Admin\SecurityController::class, 'ipDetails'])->name('details');
            Route::get('/report', [App\Http\Controllers\Admin\SecurityController::class, 'ipReport'])->name('report');
            Route::post('/cleanup-expired', [App\Http\Controllers\Admin\SecurityController::class, 'cleanupExpiredIpRestrictions'])->name('cleanup-expired');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes (บทบาท: super_admin)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'super.admin', 'log.activity'], 'prefix' => 'super-admin', 'as' => 'super-admin.'], function () {
    Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');
    
    // Registration Approval Routes (Super Admin สามารถเข้าถึงได้เหมือน Admin)
    Route::group(['prefix' => 'approvals', 'as' => 'approvals.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'index'])->name('index');
        Route::get('/{approval}', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'show'])->name('show');
        Route::post('/{approval}/approve', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'approve'])->name('approve');
        Route::post('/{approval}/reject', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'reject'])->name('reject');
        Route::post('/bulk-action', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'bulkAction'])->name('bulk-action');
        Route::delete('/{approval}', [App\Http\Controllers\Admin\RegistrationApprovalController::class, 'destroy'])->name('destroy');
    });
    
    // Super Admin User Management Routes
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        // Main CRUD routes
        Route::get('/', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'destroy'])->name('destroy');
        
        // AJAX routes for advanced user management
        Route::post('/{id}/reset-password', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{id}/toggle-status', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/promote-role', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'promoteRole'])->name('promote-role');
        Route::post('/{id}/terminate-sessions', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'terminateSessions'])->name('terminate-sessions');
        
        // Session management routes
        Route::get('/sessions', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'sessions'])->name('sessions');
    });
    
    // Advanced Security Management Routes (Super Admin Only)
    Route::group(['prefix' => 'security', 'as' => 'security.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'index'])->name('index');
        Route::get('/devices', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'deviceManagement'])->name('devices');
        Route::get('/ip-management', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'ipManagement'])->name('ip-management');
        Route::get('/suspicious-activity', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'suspiciousActivity'])->name('suspicious-activity');
        Route::get('/policies', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'securityPolicies'])->name('policies');
        
        // Security Actions
        Route::post('/policies', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'updateSecurityPolicies'])->name('policies.update');
        Route::post('/users/{user}/force-logout', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'forceLogoutUser'])->name('force-logout');
        Route::post('/users/{user}/suspend', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'suspendUser'])->name('suspend-user');
        
        // API endpoints for AJAX calls
        Route::get('/stats', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'getSecurityStats'])->name('stats');
        Route::post('/system-scan', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'runSystemScan'])->name('system-scan');
        Route::post('/cleanup-expired', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'cleanupExpired'])->name('cleanup-expired');
        Route::post('/force-logout-all', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'forceLogoutAll'])->name('force-logout-all');
        Route::post('/block-ip', [App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'blockIP'])->name('block-ip');
    });
    
    // System Management Routes สำหรับ Super Admin
    // จะเพิ่มใน Phase ต่อไป
});

/*
|--------------------------------------------------------------------------
| Development Routes (เฉพาะ development)
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    // เคลียร์ log
    Route::get('/clear-log', function () {
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
        }
        return 'Log cleared!';
    })->middleware('auth');
}

/*
|--------------------------------------------------------------------------
| AJAX API Routes (ใช้ session authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/api/password/status', [App\Http\Controllers\PasswordStatusController::class, 'getStatus'])
        ->name('api.password.status');
});
