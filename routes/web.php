<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Route, Auth, Http, Log, DB};
use App\Http\Controllers\Auth\{LoginController, RegisterController, TwoFactorController};
use App\Http\Controllers\User\{DashboardController as UserDashboard, SessionController as UserSessionController};
use App\Http\Controllers\Admin\{DashboardController as AdminDashboard, UserManagementController as AdminUserManagement, SessionController as AdminSessionController};
use App\Http\Controllers\SuperAdmin\{DashboardController as SuperAdminDashboard, SessionController as SuperAdminSessionController};
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;

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
    Route::get('/notifications/security/unread', [NotificationController::class, 'getUnreadSecurityNotifications'])->name('notifications.security.unread');
    
    // Test route (development only)
    Route::post('/notifications/test', [NotificationController::class, 'testNotification'])->name('notifications.test');
});

/*
|--------------------------------------------------------------------------
| Activity Log Routes (สำหรับประวัติกิจกรรม)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth']], function () {
    Route::get('/activities', [App\Http\Controllers\ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/{activity}', [App\Http\Controllers\ActivityController::class, 'show'])->name('activities.show');
    Route::get('/activities/export', [App\Http\Controllers\ActivityController::class, 'export'])->name('activities.export');
    
    // API Routes for AJAX
    Route::get('/api/activities/recent', [App\Http\Controllers\ActivityController::class, 'getRecentActivities'])->name('activities.recent');
    Route::get('/activities/chart-data', [App\Http\Controllers\ActivityController::class, 'getChartData'])->name('activities.chart-data');
    
    // Admin only routes
    Route::group(['middleware' => 'role:admin,super_admin'], function () {
        Route::post('/activities/{activity}/mark-suspicious', [App\Http\Controllers\ActivityController::class, 'markSuspicious'])->name('activities.mark-suspicious');
        Route::post('/activities/{activity}/unmark-suspicious', [App\Http\Controllers\ActivityController::class, 'unmarkSuspicious'])->name('activities.unmark-suspicious');
    });
});

/*
|--------------------------------------------------------------------------
| Message Routes (สำหรับระบบข้อความภายในองค์กร)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth']], function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('/messages/{message}/mark-read', [MessageController::class, 'markAsRead'])->name('messages.mark-read');
    Route::post('/messages/mark-all-read', [MessageController::class, 'markAllAsRead'])->name('messages.mark-all-read');
    
    // API Routes for AJAX
    Route::get('/api/messages/recent', [MessageController::class, 'getRecentMessages'])->name('messages.recent');
    Route::post('/api/messages/system', [MessageController::class, 'sendSystemMessage'])->name('messages.system');
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
    
    // User Session Management Routes
    Route::group(['prefix' => 'sessions', 'as' => 'sessions.'], function () {
        Route::get('/', [UserSessionController::class, 'index'])->name('index');
        Route::post('/logout-others', [UserSessionController::class, 'logoutOtherDevices'])->name('logout-others');
        Route::post('/terminate', [UserSessionController::class, 'terminateSession'])->name('terminate');
        Route::post('/trust', [UserSessionController::class, 'trustDevice'])->name('trust');
        Route::get('/activity', [UserSessionController::class, 'activity'])->name('activity');
    });
    
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
        
        // Force logout route
        Route::post('/{user}/force-logout', [AdminUserManagement::class, 'forceLogout'])->name('force-logout');
    });
    
    // Admin Session Management Routes
    Route::group(['prefix' => 'sessions', 'as' => 'sessions.'], function () {
        Route::get('/', [AdminSessionController::class, 'index'])->name('index');
        Route::get('/users/{user}', [AdminSessionController::class, 'show'])->name('show');
        Route::post('/users/{user}/force-logout', [AdminSessionController::class, 'forceLogout'])->name('force-logout');
        Route::post('/terminate', [AdminSessionController::class, 'terminateSession'])->name('terminate');
        Route::post('/cleanup', [AdminSessionController::class, 'cleanupExpired'])->name('cleanup');
        Route::get('/report', [AdminSessionController::class, 'report'])->name('report');
        Route::get('/export', [AdminSessionController::class, 'export'])->name('export');
    });
    
    // Additional User Management Routes
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
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

    // Reports Routes
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('index');
        Route::get('/users', [App\Http\Controllers\Admin\ReportsController::class, 'users'])->name('users');
        Route::get('/activities', [App\Http\Controllers\Admin\ReportsController::class, 'activities'])->name('activities');
        Route::get('/security', [App\Http\Controllers\Admin\ReportsController::class, 'security'])->name('security');
        Route::get('/export', [App\Http\Controllers\Admin\ReportsController::class, 'export'])->name('export');
    });

    // Audit & Monitoring Routes
    Route::group(['prefix' => 'audit', 'as' => 'audit.'], function () {
        // Audit Logs
        Route::get('/', [App\Http\Controllers\Admin\AuditController::class, 'index'])->name('index');
        Route::get('/logs/{auditLog}', [App\Http\Controllers\Admin\AuditController::class, 'show'])->name('show');
        Route::get('/statistics', [App\Http\Controllers\Admin\AuditController::class, 'statistics'])->name('statistics');
        Route::get('/export', [App\Http\Controllers\Admin\AuditController::class, 'export'])->name('export');
    });

    // Override History Routes
    Route::group(['prefix' => 'override', 'as' => 'override.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\OverrideController::class, 'index'])->name('index');
        Route::get('/history/{overrideLog}', [App\Http\Controllers\Admin\OverrideController::class, 'show'])->name('show');
        Route::get('/report', [App\Http\Controllers\Admin\OverrideController::class, 'report'])->name('report');
        Route::get('/export', [App\Http\Controllers\Admin\OverrideController::class, 'export'])->name('export');
    });

    // Approval Statistics Routes
    Route::group(['prefix' => 'statistics', 'as' => 'statistics.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('index');
        Route::get('/analytics', [App\Http\Controllers\Admin\StatisticsController::class, 'analytics'])->name('analytics');
        Route::get('/report', [App\Http\Controllers\Admin\StatisticsController::class, 'report'])->name('report');
    });
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes (บทบาท: super_admin)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'super.admin', 'log.activity'], 'prefix' => 'super-admin', 'as' => 'super-admin.'], function () {
    Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');
    
    // Super Admin Session Management Routes
    Route::group(['prefix' => 'sessions', 'as' => 'sessions.'], function () {
        Route::get('/', [SuperAdminSessionController::class, 'index'])->name('index');
        Route::get('/dashboard', [SuperAdminSessionController::class, 'dashboard'])->name('dashboard');
        Route::get('/realtime', [SuperAdminSessionController::class, 'realtime'])->name('realtime');
        Route::get('/realtime-data', [SuperAdminSessionController::class, 'realtimeData'])->name('realtime-data');
        Route::get('/settings', [SuperAdminSessionController::class, 'settings'])->name('settings');
        Route::post('/settings', [SuperAdminSessionController::class, 'updateSettings'])->name('update-settings');
        Route::get('/system-report', [SuperAdminSessionController::class, 'systemReport'])->name('system-report');
        Route::post('/bulk-actions', [SuperAdminSessionController::class, 'bulkActions'])->name('bulk-actions');
        Route::get('/advanced-export', [SuperAdminSessionController::class, 'advancedExport'])->name('advanced-export');
        Route::post('/trust-device', [SuperAdminSessionController::class, 'trustDevice'])->name('trust-device');
        Route::get('/{session}', [SuperAdminSessionController::class, 'show'])->name('show');
        Route::delete('/{session}/terminate', [SuperAdminSessionController::class, 'terminate'])->name('terminate');
    });
    
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
        
        // Session management routes - ต้องอยู่ก่อน /{id} routes
        Route::get('/sessions', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'sessions'])->name('sessions');
        
        // Test route for debugging
        Route::get('/sessions-test', function() {
            return view('admin.super-admin.users.sessions-simple', [
                'sessions' => new \Illuminate\Pagination\LengthAwarePaginator(
                    collect([
                        (object) [
                            'id' => 1,
                            'user' => (object) [
                                'name' => 'Test User',
                                'email' => 'test@example.com',
                                'role' => 'super_admin',
                                'profile_image' => null
                            ],
                            'ip_address' => '127.0.0.1',
                            'user_agent' => 'Test Browser',
                            'status' => 'active',
                            'created_at' => now(),
                            'last_activity' => now()->format('Y-m-d H:i:s'),
                        ]
                    ]),
                    1,
                    20,
                    1,
                    ['path' => request()->url(), 'pageName' => 'page']
                )
            ]);
        })->name('sessions-test');
        
        // Dynamic routes with {id} parameter - ต้องอยู่หลัง specific routes
        Route::get('/{id}', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'destroy'])->name('destroy');
        
        // AJAX routes for advanced user management
        Route::post('/{id}/reset-password', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{id}/toggle-status', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/promote-role', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'promoteRole'])->name('promote-role');
        Route::post('/{id}/terminate-sessions', [App\Http\Controllers\Admin\SuperAdminUserController::class, 'terminateSessions'])->name('terminate-sessions');
        
    });

    // Role & Permission Management Routes (Super Admin Only)
    Route::group(['prefix' => 'roles', 'as' => 'roles.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\RolePermissionController::class, 'index'])->name('index');
        Route::get('/permissions', [App\Http\Controllers\Admin\RolePermissionController::class, 'permissions'])->name('permissions');
        Route::get('/history', [App\Http\Controllers\Admin\RolePermissionController::class, 'roleHistory'])->name('history');
        Route::put('/{user}/update-role', [App\Http\Controllers\Admin\RolePermissionController::class, 'updateRole'])->name('update-role');
        Route::post('/bulk-update', [App\Http\Controllers\Admin\RolePermissionController::class, 'bulkUpdate'])->name('bulk-update');
        Route::get('/api/statistics', [App\Http\Controllers\Admin\RolePermissionController::class, 'roleStatistics'])->name('api.statistics');
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
    
    // System Reports Routes (Super Admin Only)
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\SystemReportsController::class, 'index'])->name('index');
        Route::get('/users', [App\Http\Controllers\Admin\SystemReportsController::class, 'users'])->name('users');
        Route::get('/sessions', [App\Http\Controllers\Admin\SystemReportsController::class, 'sessions'])->name('sessions');
        Route::get('/security', [App\Http\Controllers\Admin\SystemReportsController::class, 'security'])->name('security');
        Route::get('/performance', [App\Http\Controllers\Admin\SystemReportsController::class, 'performance'])->name('performance');
        Route::get('/export', [App\Http\Controllers\Admin\SystemReportsController::class, 'export'])->name('export');
    });
    
    // System Settings Routes (Super Admin Only)
    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::get('/', [App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('index');
        Route::get('/general', [App\Http\Controllers\Admin\SystemSettingsController::class, 'general'])->name('general');
        Route::put('/general', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateGeneral'])->name('update-general');
        Route::get('/security', [App\Http\Controllers\Admin\SystemSettingsController::class, 'security'])->name('security');
        Route::put('/security', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateSecurity'])->name('update-security');
        Route::get('/email', [App\Http\Controllers\Admin\SystemSettingsController::class, 'email'])->name('email');
        Route::put('/email', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateEmail'])->name('update-email');
        Route::post('/email/test', [App\Http\Controllers\Admin\SystemSettingsController::class, 'testEmail'])->name('test-email');
        Route::get('/notifications', [App\Http\Controllers\Admin\SystemSettingsController::class, 'notifications'])->name('notifications');
        Route::put('/notifications', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateNotifications'])->name('update-notifications');
        Route::get('/backup', [App\Http\Controllers\Admin\SystemSettingsController::class, 'backup'])->name('backup');
        Route::post('/backup/create', [App\Http\Controllers\Admin\SystemSettingsController::class, 'createBackup'])->name('create-backup');
        Route::post('/cache/clear', [App\Http\Controllers\Admin\SystemSettingsController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimize', [App\Http\Controllers\Admin\SystemSettingsController::class, 'optimize'])->name('optimize');
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

/*
|--------------------------------------------------------------------------
| Super Admin Device Management AJAX Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->prefix('api/admin')->group(function () {
    // Revoke all suspicious devices
    Route::post('/devices/revoke-suspicious', [\App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'revokeAllSuspiciousDevices'])
        ->name('api.admin.devices.revoke-suspicious');
    
    // Force logout all devices
    Route::post('/devices/force-logout-all', [\App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'forceLogoutAllDevices'])
        ->name('api.admin.devices.force-logout-all');
    
    // Cleanup old devices
    Route::post('/devices/cleanup-old', [\App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'cleanupOldDevices'])
        ->name('api.admin.devices.cleanup-old');
        
    // Individual device management
    Route::post('/devices/{deviceId}/trust', [\App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'trustDevice'])
        ->name('api.admin.devices.trust');
        
    Route::post('/devices/{deviceId}/suspect', [\App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'suspectDevice'])
        ->name('api.admin.devices.suspect');
        
    Route::post('/devices/{deviceId}/block', [\App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'blockDevice'])
        ->name('api.admin.devices.block');

    // Block IP Address
    Route::post('/block-ip', [\App\Http\Controllers\Admin\SuperAdminSecurityController::class, 'blockIP'])
        ->name('api.admin.block-ip');
});
