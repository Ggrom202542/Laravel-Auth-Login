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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// สำหรับ user
Route::group(['middleware' => ['auth', 'user_type:user']], function () {
    Route::get('home', [UserController::class, 'index'])->name('home');

    // profile (information & account settings)
    Route::get('user/information', [UserController::class, 'information'])->name('user.information');
    Route::post('user/information/update', [UserController::class, 'updateInformation'])->name('user.updateInformation');
    Route::get('user/account-settings', [UserController::class, 'accountSettings'])->name('user.accountSettings');
});

// สำหรับ admin
Route::group(['middleware' => ['auth', 'user_type:admin']], function () {
    Route::get('admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // profile (information & account settings)
    Route::get('admin/information', [AdminController::class, 'information'])->name('admin.information');
    Route::post('admin/information/update', [AdminController::class, 'updateInformation'])->name('admin.updateInformation');
    Route::get('admin/account-settings', [AdminController::class, 'accountSettings'])->name('admin.accountSettings');
});

// สำหรับ super admin
Route::group(['middleware' => ['auth', 'user_type:super_admin']], function () {
    Route::get('super_admin/dashboard', [SuperAdminController::class, 'index'])->name('super_admin.dashboard');

    // profile (information & account settings)
    Route::get('super_admin/information', [SuperAdminController::class, 'information'])->name('super_admin.information');
    Route::post('super_admin/information/update', [SuperAdminController::class, 'updateInformation'])->name('super_admin.updateInformation');
    Route::get('super_admin/account-settings', [SuperAdminController::class, 'accountSettings'])->name('super_admin.accountSettings');
});
