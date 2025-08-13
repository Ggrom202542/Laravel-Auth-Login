<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Route, Auth, Http, Log, DB};
use App\Http\Controllers\Auth\{LoginController, RegisterController};
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Super_Admin\SuperAdminController;

Route::group(['middleware' => 'guest'], function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register/insert', [RegisterController::class, 'register'])->name('registerInsert');
});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// สำหรับ user
Route::group(['middleware' => ['auth', 'user_type:user']], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

// สำหรับ admin
Route::group(['middleware' => ['auth', 'user_type:admin']], function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

// สำหรับ super admin
Route::group(['middleware' => ['auth', 'user_type:super_admin']], function () {
    Route::get('/super_admin/dashboard', [SuperAdminController::class, 'index'])->name('super_admin.dashboard');
});
