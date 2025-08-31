# 🎮 Controller Templates

## 📋 Overview
เทมเพลต Controllers สำหรับระบบ Authentication แบบ Role-Based พร้อมตัวอย่างการใช้งาน

## 🔐 Authentication Controllers

### 1. Enhanced Login Controller
```php
<?php
// File: app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Services\ActivityService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $authService;
    protected $activityService;

    public function __construct(AuthService $authService, ActivityService $activityService)
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
        $this->authService = $authService;
        $this->activityService = $activityService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');
        $remember = $request->boolean('remember');
        
        try {
            $result = $this->authService->attemptLogin($credentials, $remember, $request);
            
            if ($result['success']) {
                return redirect()
                    ->intended($this->getRedirectUrl($result['user']))
                    ->with('success', 'เข้าสู่ระบบสำเร็จ');
            }
            
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => $result['message']]);
                
        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง']);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log activity
        $this->activityService->log($user->id, 'logout', 'User logged out', $request);
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'ออกจากระบบแล้ว');
    }

    protected function getRedirectUrl($user)
    {
        if ($user->hasRole('super_admin')) {
            return route('super-admin.dashboard');
        } elseif ($user->hasRole('admin')) {
            return route('admin.dashboard');
        } else {
            return route('dashboard');
        }
    }
}
```

### 2. Enhanced Register Controller
```php
<?php
// File: app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use App\Services\UserService;
use App\Events\UserRegistered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $userService;
    protected $authService;

    public function __construct(UserService $userService, AuthService $authService)
    {
        $this->middleware('guest');
        $this->userService = $userService;
        $this->authService = $authService;
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        try {
            $userData = $request->validated();
            $userData['password'] = Hash::make($userData['password']);
            
            $user = $this->userService->createUser($userData);
            
            // Assign default role
            $this->authService->assignRole($user, 'user');
            
            // Fire event
            event(new UserRegistered($user));
            
            // Auto login
            Auth::login($user);
            
            return redirect()
                ->route('dashboard')
                ->with('success', 'ลงทะเบียนสำเร็จ ยินดีต้อนรับ!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'เกิดข้อผิดพลาดในการลงทะเบียน กรุณาลองใหม่อีกครั้ง']);
        }
    }
}
```

### 3. Profile Controller
```php
<?php
// File: app/Http/Controllers/Auth/ProfileController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\UserService;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    protected $userService;
    protected $activityService;

    public function __construct(UserService $userService, ActivityService $activityService)
    {
        $this->middleware('auth');
        $this->userService = $userService;
        $this->activityService = $activityService;
    }

    public function index()
    {
        $user = Auth::user();
        $activities = $this->activityService->getUserActivities($user->id, 10);
        
        return view('profile.index', compact('user', 'activities'));
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(UpdateProfileRequest $request)
    {
        try {
            $user = Auth::user();
            $oldData = $user->toArray();
            
            $this->userService->updateProfile($user, $request->validated());
            
            // Log activity
            $this->activityService->log(
                $user->id, 
                'profile_updated', 
                'User updated profile information', 
                $request,
                ['old_data' => $oldData, 'new_data' => $request->validated()]
            );
            
            return redirect()
                ->route('profile.index')
                ->with('success', 'อัปเดตข้อมูลโปรไฟล์สำเร็จ');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล']);
        }
    }

    public function security()
    {
        return view('profile.security', ['user' => Auth::user()]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $user = Auth::user();
            
            $this->userService->updatePassword($user, $request->new_password);
            
            // Log activity
            $this->activityService->log(
                $user->id, 
                'password_changed', 
                'User changed password', 
                $request
            );
            
            return redirect()
                ->route('profile.security')
                ->with('success', 'เปลี่ยนรหัสผ่านสำเร็จ');
                
        } catch (\Exception $e) {
            return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
        }
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $user = Auth::user();
            
            // Delete old avatar
            if ($user->profile_image) {
                Storage::delete('public/avatars/' . $user->profile_image);
            }
            
            // Upload new avatar
            $fileName = $this->userService->uploadAvatar($user, $request->file('avatar'));
            
            // Log activity
            $this->activityService->log(
                $user->id, 
                'avatar_updated', 
                'User updated profile image', 
                $request
            );
            
            return response()->json([
                'success' => true,
                'message' => 'อัปโหลดรูปโปรไฟล์สำเร็จ',
                'avatar_url' => asset('storage/avatars/' . $fileName)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัปโหลดรูป'
            ], 500);
        }
    }
}
```

## 👥 User Management Controllers

### 1. User Dashboard Controller
```php
<?php
// File: app/Http/Controllers/User/DashboardController.php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ActivityService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $userService;
    protected $activityService;

    public function __construct(UserService $userService, ActivityService $activityService)
    {
        $this->middleware('auth');
        $this->middleware('role:user');
        $this->userService = $userService;
        $this->activityService = $activityService;
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get user statistics
        $stats = [
            'login_count' => $this->activityService->getLoginCount($user->id),
            'last_login' => $user->last_login_at,
            'account_age' => $user->created_at->diffForHumans(),
            'profile_completion' => $this->userService->getProfileCompletion($user)
        ];
        
        // Get recent activities
        $activities = $this->activityService->getUserActivities($user->id, 5);
        
        return view('user.dashboard', compact('user', 'stats', 'activities'));
    }
}
```

### 2. Admin User Management Controller
```php
<?php
// File: app/Http/Controllers/Admin/UserManagementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Services\UserService;
use App\Services\RoleService;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    protected $userService;
    protected $roleService;
    protected $activityService;

    public function __construct(
        UserService $userService, 
        RoleService $roleService,
        ActivityService $activityService
    ) {
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin');
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->activityService = $activityService;
    }

    public function index(Request $request)
    {
        $users = $this->userService->getPaginatedUsers(
            $request->search,
            $request->status,
            $request->role,
            15
        );
        
        $roles = $this->roleService->getAllRoles();
        
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show($id)
    {
        $user = $this->userService->findUser($id);
        $activities = $this->activityService->getUserActivities($id, 20);
        
        return view('admin.users.show', compact('user', 'activities'));
    }

    public function create()
    {
        $roles = $this->roleService->getAllRoles();
        return view('admin.users.create', compact('roles'));
    }

    public function store(CreateUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());
            
            // Assign role
            if ($request->role) {
                $this->roleService->assignRole($user, $request->role, Auth::id());
            }
            
            // Log activity
            $this->activityService->log(
                Auth::id(), 
                'user_created', 
                "Admin created user: {$user->username}", 
                $request
            );
            
            return redirect()
                ->route('admin.users.index')
                ->with('success', 'สร้างผู้ใช้ใหม่สำเร็จ');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'เกิดข้อผิดพลาดในการสร้างผู้ใช้']);
        }
    }

    public function edit($id)
    {
        $user = $this->userService->findUser($id);
        $roles = $this->roleService->getAllRoles();
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $user = $this->userService->findUser($id);
            $oldData = $user->toArray();
            
            $this->userService->updateUser($user, $request->validated());
            
            // Update roles if provided
            if ($request->has('roles')) {
                $this->roleService->syncUserRoles($user, $request->roles, Auth::id());
            }
            
            // Log activity
            $this->activityService->log(
                Auth::id(), 
                'user_updated', 
                "Admin updated user: {$user->username}", 
                $request,
                ['old_data' => $oldData, 'new_data' => $request->validated()]
            );
            
            return redirect()
                ->route('admin.users.show', $id)
                ->with('success', 'อัปเดตข้อมูลผู้ใช้สำเร็จ');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล']);
        }
    }

    public function destroy($id)
    {
        try {
            $user = $this->userService->findUser($id);
            $username = $user->username;
            
            $this->userService->deleteUser($user);
            
            // Log activity
            $this->activityService->log(
                Auth::id(), 
                'user_deleted', 
                "Admin deleted user: {$username}", 
                request()
            );
            
            return redirect()
                ->route('admin.users.index')
                ->with('success', 'ลบผู้ใช้สำเร็จ');
                
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'เกิดข้อผิดพลาดในการลบผู้ใช้']);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended'
        ]);

        try {
            $user = $this->userService->findUser($id);
            $oldStatus = $user->status;
            
            $this->userService->updateStatus($user, $request->status);
            
            // Log activity
            $this->activityService->log(
                Auth::id(), 
                'user_status_updated', 
                "Admin changed user {$user->username} status from {$oldStatus} to {$request->status}", 
                $request
            );
            
            return response()->json([
                'success' => true,
                'message' => 'อัปเดตสถานะผู้ใช้สำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัปเดตสถานะ'
            ], 500);
        }
    }
}
```

## 📊 Dashboard Controllers

### 1. Admin Dashboard Controller
```php
<?php
// File: app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\UserService;
use App\Services\ActivityService;

class DashboardController extends Controller
{
    protected $reportService;
    protected $userService;
    protected $activityService;

    public function __construct(
        ReportService $reportService,
        UserService $userService,
        ActivityService $activityService
    ) {
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin');
        $this->reportService = $reportService;
        $this->userService = $userService;
        $this->activityService = $activityService;
    }

    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_users' => $this->userService->getTotalUsers(),
            'active_users' => $this->userService->getActiveUsers(),
            'new_users_today' => $this->userService->getNewUsersToday(),
            'online_users' => $this->userService->getOnlineUsers(),
        ];
        
        // Get recent activities
        $recentActivities = $this->activityService->getRecentActivities(10);
        
        // Get user growth chart data
        $userGrowthData = $this->reportService->getUserGrowthData(30);
        
        // Get user status distribution
        $statusDistribution = $this->reportService->getUserStatusDistribution();
        
        return view('admin.dashboard', compact(
            'stats', 
            'recentActivities', 
            'userGrowthData', 
            'statusDistribution'
        ));
    }
}
```

### 2. Super Admin Dashboard Controller
```php
<?php
// File: app/Http/Controllers/SuperAdmin/DashboardController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SystemService;
use App\Services\ReportService;
use App\Services\ActivityService;

class DashboardController extends Controller
{
    protected $systemService;
    protected $reportService;
    protected $activityService;

    public function __construct(
        SystemService $systemService,
        ReportService $reportService,
        ActivityService $activityService
    ) {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
        $this->systemService = $systemService;
        $this->reportService = $reportService;
        $this->activityService = $activityService;
    }

    public function index()
    {
        // System statistics
        $systemStats = [
            'system_health' => $this->systemService->getSystemHealth(),
            'database_size' => $this->systemService->getDatabaseSize(),
            'storage_usage' => $this->systemService->getStorageUsage(),
            'cache_status' => $this->systemService->getCacheStatus(),
        ];
        
        // User statistics
        $userStats = [
            'total_users' => $this->reportService->getTotalUsers(),
            'total_admins' => $this->reportService->getTotalAdmins(),
            'active_sessions' => $this->reportService->getActiveSessions(),
            'failed_logins' => $this->reportService->getFailedLoginsToday(),
        ];
        
        // Recent admin activities
        $adminActivities = $this->activityService->getAdminActivities(15);
        
        // System performance data
        $performanceData = $this->systemService->getPerformanceData(7);
        
        return view('super-admin.dashboard', compact(
            'systemStats',
            'userStats', 
            'adminActivities',
            'performanceData'
        ));
    }
}
```

## 🛠️ API Controllers

### 1. Auth API Controller
```php
<?php
// File: app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->attemptLogin(
                $request->only('username', 'password'),
                false,
                $request
            );
            
            if ($result['success']) {
                $token = $result['user']->createToken('auth-token')->plainTextToken;
                
                return response()->json([
                    'success' => true,
                    'message' => 'เข้าสู่ระบบสำเร็จ',
                    'data' => [
                        'user' => new UserResource($result['user']),
                        'token' => $token,
                        'token_type' => 'Bearer'
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 401);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในระบบ'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            // Log activity
            $this->authService->logActivity($user->id, 'api_logout', 'User logged out via API', $request);
            
            return response()->json([
                'success' => true,
                'message' => 'ออกจากระบบสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการออกจากระบบ'
            ], 500);
        }
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user())
        ]);
    }
}
```

## 🎯 Controller Best Practices

### 1. Constructor Pattern
```php
public function __construct(
    ServiceInterface $service,
    RepositoryInterface $repository
) {
    $this->middleware('auth');
    $this->middleware('role:admin');
    $this->service = $service;
    $this->repository = $repository;
}
```

### 2. Error Handling Pattern
```php
try {
    // Business logic here
    $result = $this->service->performAction($data);
    
    return redirect()->route('success.route')->with('success', 'ดำเนินการสำเร็จ');
    
} catch (\Exception $e) {
    Log::error('Error in controller action: ' . $e->getMessage());
    
    return back()
        ->withInput()
        ->withErrors(['general' => 'เกิดข้อผิดพลาดในระบบ']);
}
```

### 3. Resource Response Pattern
```php
public function index()
{
    $data = $this->service->getData();
    
    return view('module.index', compact('data'));
}

public function apiIndex()
{
    $data = $this->service->getData();
    
    return response()->json([
        'success' => true,
        'data' => ResourceCollection::make($data)
    ]);
}
```

### 4. Validation Pattern
```php
public function store(CustomRequest $request)
{
    // Validation จะทำใน FormRequest
    $validatedData = $request->validated();
    
    // Business logic
    $result = $this->service->create($validatedData);
    
    return response()->json([
        'success' => true,
        'data' => new CustomResource($result)
    ]);
}
```

## 📝 Controller Generation Commands

```bash
# Authentication Controllers
php artisan make:controller Auth/LoginController
php artisan make:controller Auth/RegisterController
php artisan make:controller Auth/ProfileController

# User Controllers
php artisan make:controller User/DashboardController
php artisan make:controller User/ProfileController

# Admin Controllers
php artisan make:controller Admin/DashboardController
php artisan make:controller Admin/UserManagementController
php artisan make:controller Admin/ReportController

# Super Admin Controllers
php artisan make:controller SuperAdmin/DashboardController
php artisan make:controller SuperAdmin/AdminManagementController
php artisan make:controller SuperAdmin/RoleManagementController
php artisan make:controller SuperAdmin/SystemController

# API Controllers
php artisan make:controller Api/AuthController
php artisan make:controller Api/UserController
php artisan make:controller Api/AdminController
```

---

**Template Version:** 1.0  
**Created:** August 31, 2025  
**Compatible:** Laravel 10.x+  
**Pattern:** Service-Repository with Clean Architecture
