<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Route, Auth, Http, Log, DB};
use App\Http\Controllers\Auth\{LoginController, RegisterController};
use App\Http\Controllers\User\DashboardController as UserDashboard;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
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
Route::group(['middleware' => ['auth'], 'prefix' => 'profile', 'as' => 'profile.'], function () {
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
Route::group(['middleware' => ['auth', 'role:user', 'log.activity'], 'prefix' => 'user', 'as' => 'user.'], function () {
    Route::get('/dashboard', [UserDashboard::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (บทบาท: admin, super_admin)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'role:admin,super_admin', 'log.activity'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
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
    // จะเพิ่มใน Phase ต่อไป
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes (บทบาท: super_admin)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'role:super_admin', 'log.activity'], 'prefix' => 'super-admin', 'as' => 'super-admin.'], function () {
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
