<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Route, Auth, Http, Log, DB};
use App\Http\Controllers\Auth\{LoginController, RegisterController};
use App\Http\Controllers\User\DashboardController as UserDashboard;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;

/*
|--------------------------------------------------------------------------
| Guest Routes (ไม่ต้องเข้าสู่ระบบ)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'guest'], function () {
    Route::get('/', function () {
        return view('welcome');
    });
    
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register/insert', [RegisterController::class, 'register'])->name('registerInsert');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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
| Admin Routes (บทบาท: admin)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'role:admin', 'log.activity'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    
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
