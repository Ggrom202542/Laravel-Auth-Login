<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Route, Auth, Http, Log, DB};
use App\Http\Controllers\Auth\{LoginController, RegisterController};
use App\Http\Controllers\Super_Admin\SuperAdminController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\User\UserController;

Route::group(['middleware' => 'guest'], function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register/insert', [RegisterController::class, 'register'])->name('registerInsert');
});

Route::get('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');

// เคลียร์ log
Route::get('/clear-log', function () {
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        file_put_contents($logPath, '');
    }
    return 'Log cleared!';
})->middleware('auth');

Auth::routes();

Route::get('/', function () {
    return view('welcome');
});

// สำหรับ user
Route::group(['middleware' => ['auth', 'user_type:user']], function () {
    Route::get('home', [UserController::class, 'index'])->name('home');

    // profile (information & account settings)
    Route::get('user/information', [UserController::class, 'information'])->name('user.information');
    Route::post('user/information/update', [UserController::class, 'updateInformation'])->name('user.updateInformation');
    Route::get('user/account-settings', [UserController::class, 'accountSettings'])->name('user.accountSettings');
    Route::post('user/account-settings/update', [UserController::class, 'updateAccountSettings'])->name('user.updateAccountSettings');
});

// สำหรับ admin
Route::group(['middleware' => ['auth', 'user_type:admin']], function () {
    Route::get('admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // profile (information & account settings)
    Route::get('admin/information', [AdminController::class, 'information'])->name('admin.information');
    Route::post('admin/information/update', [AdminController::class, 'updateInformation'])->name('admin.updateInformation');
    Route::get('admin/account-settings', [AdminController::class, 'accountSettings'])->name('admin.accountSettings');
    Route::post('admin/account-settings/update', [AdminController::class, 'updateAccountSettings'])->name('admin.updateAccountSettings');

    // data management
    Route::get('admin/user-management', [AdminController::class, 'userManagement'])->name('admin.userManagement');
    Route::get('admin/user-info/{id}', [AdminController::class, 'userInfo'])->name('admin.userInfo');
    Route::post('admin/user-info/update/{id}', [AdminController::class, 'updateUserInfo'])->name('admin.updateUserInfo');
    Route::get('admin/user-info/delete/{id}', [AdminController::class, 'deleteUser'])->name('admin.deleteUser');
    Route::get('admin/user-info/register/{id}', [AdminController::class, 'registerUser'])->name('admin.registerUser');
    Route::post('admin/user-info/register/insert/{id}', [AdminController::class, 'registerUserInsert'])->name('admin.registerUserInsert');
    Route::get('admin/user-info/register/delete/{id}', [AdminController::class, 'deleteRegisteredUser'])->name('admin.deleteRegisteredUser');

    Route::get('admin/admin-management', [AdminController::class, 'adminManagement'])->name('admin.adminManagement');
    Route::get('admin/admin-info/{id}', [AdminController::class, 'adminInfo'])->name('admin.adminInfo');
});

// สำหรับ super admin
Route::group(['middleware' => ['auth', 'user_type:super_admin']], function () {
    Route::get('super_admin/dashboard', [SuperAdminController::class, 'index'])->name('super_admin.dashboard');

    // profile (information & account settings)
    Route::get('super_admin/information', [SuperAdminController::class, 'information'])->name('super_admin.information');
    Route::post('super_admin/information/update', [SuperAdminController::class, 'updateInformation'])->name('super_admin.updateInformation');
    Route::get('super_admin/account-settings', [SuperAdminController::class, 'accountSettings'])->name('super_admin.accountSettings');
    Route::post('super_admin/account-settings/update', [SuperAdminController::class, 'updateAccountSettings'])->name('super_admin.updateAccountSettings');

    // data management
    Route::get('super_admin/user-management', [SuperAdminController::class, 'userManagement'])->name('super_admin.userManagement');
    Route::get('super_admin/user-info/{id}', [SuperAdminController::class, 'userInfo'])->name('super_admin.userInfo');
    Route::post('super_admin/user-info/update/{id}', [SuperAdminController::class, 'updateUserInfo'])->name('super_admin.updateUserInfo');
    Route::get('super_admin/user-info/delete/{id}', [SuperAdminController::class, 'deleteUser'])->name('super_admin.deleteUser');
    Route::get('super_admin/user-info/register/{id}', [SuperAdminController::class, 'registerUser'])->name('super_admin.registerUser');
    Route::post('super_admin/user-info/register/insert/{id}', [SuperAdminController::class, 'registerUserInsert'])->name('super_admin.registerUserInsert');
    Route::get('super_admin/user-info/register/delete/{id}', [SuperAdminController::class, 'deleteRegisteredUser'])->name('super_admin.deleteRegisteredUser');

    Route::get('super_admin/admin-management', [SuperAdminController::class, 'adminManagement'])->name('super_admin.adminManagement');
    Route::get('super_admin/admin-info/{id}', [SuperAdminController::class, 'adminInfo'])->name('super_admin.adminInfo');
    Route::post('super_admin/admin-info/update/{id}', [SuperAdminController::class, 'updateAdminInfo'])->name('super_admin.updateAdminInfo');
    Route::get('super_admin/admin-info/delete/{id}', [SuperAdminController::class, 'deleteAdmin'])->name('super_admin.deleteAdmin');

    Route::get('super_admin/super-admin-management', [SuperAdminController::class, 'superAdminManagement'])->name('super_admin.superAdminManagement');
    Route::get('super_admin/super-admin-info/{id}', [SuperAdminController::class, 'superAdminInfo'])->name('super_admin.superAdminInfo');
    Route::post('super_admin/super-admin-info/update/{id}', [SuperAdminController::class, 'updateSuperAdminInfo'])->name('super_admin.updateSuperAdminInfo');
});
