# 🔧 Developer Guide - คู่มือนักพัฒนา

## 📋 Overview
คู่มือสำหรับนักพัฒนาที่ต้องการขยายหรือปรับแต่งระบบ Laravel Authentication Template

## 🏗️ Project Architecture

### 1. Architecture Pattern
ระบบใช้หลักการ **Clean Architecture** ร่วมกับ **Repository Pattern** และ **Service Layer Pattern**

```
┌─────────────────────────────────────┐
│           Controllers               │ ← HTTP Layer
├─────────────────────────────────────┤
│             Services                │ ← Business Logic Layer
├─────────────────────────────────────┤
│           Repositories              │ ← Data Access Layer
├─────────────────────────────────────┤
│             Models                  │ ← Eloquent ORM Layer
├─────────────────────────────────────┤
│            Database                 │ ← Storage Layer
└─────────────────────────────────────┘
```

### 2. Folder Structure
```
app/
├── Console/Commands/           # Custom Artisan Commands
├── Events/                     # Event Classes
├── Exceptions/                 # Custom Exceptions
├── Http/
│   ├── Controllers/           # MVC Controllers
│   ├── Middleware/            # HTTP Middleware
│   ├── Requests/              # Form Request Validation
│   └── Resources/             # API Resources
├── Jobs/                      # Background Jobs
├── Listeners/                 # Event Listeners
├── Models/                    # Eloquent Models
├── Providers/                 # Service Providers
├── Repositories/              # Repository Pattern
└── Services/                  # Business Logic Services
```

## 🛠️ Development Setup

### 1. Environment Requirements
```bash
# PHP Requirements
PHP 8.1+
Composer 2.0+
Node.js 18+
NPM or Yarn

# PHP Extensions
ext-openssl
ext-pdo
ext-mbstring
ext-tokenizer
ext-xml
ext-ctype
ext-json
ext-bcmath
ext-gd
```

### 2. Installation
```bash
# Clone repository
git clone https://github.com/your-username/laravel-auth-template.git
cd laravel-auth-template

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate --seed

# Compile assets
npm run dev

# Start development server
php artisan serve
```

### 3. Development Tools
```bash
# Code Style
composer require --dev friendsofphp/php-cs-fixer
./vendor/bin/php-cs-fixer fix

# Static Analysis
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse

# Testing
php artisan test
php artisan test --coverage

# Database
php artisan tinker
php artisan migrate:status
php artisan db:seed
```

## 🎯 Extending the System

### 1. Adding New Roles
```php
// 1. Create migration
php artisan make:migration add_new_role_to_roles_table

// 2. Update RoleSeeder
public function run()
{
    $roles = [
        ['name' => 'moderator', 'display_name' => 'Moderator', 'description' => 'Content moderator'],
        // ... existing roles
    ];
}

// 3. Create permissions
php artisan make:migration add_moderator_permissions

// 4. Update middleware
// In CheckRole middleware, add new role handling

// 5. Create controller
php artisan make:controller Moderator/DashboardController

// 6. Add routes
Route::group(['middleware' => ['auth', 'role:moderator']], function () {
    Route::get('moderator/dashboard', [ModeratorController::class, 'index']);
});
```

### 2. Adding New Permissions
```php
// 1. Create migration for new permissions
Schema::table('permissions', function (Blueprint $table) {
    // Add new permission categories
});

// 2. Update PermissionSeeder
public function run()
{
    $permissions = [
        // Content Management
        ['name' => 'content.create', 'display_name' => 'Create Content', 'module' => 'content'],
        ['name' => 'content.edit', 'display_name' => 'Edit Content', 'module' => 'content'],
        ['name' => 'content.delete', 'display_name' => 'Delete Content', 'module' => 'content'],
        ['name' => 'content.publish', 'display_name' => 'Publish Content', 'module' => 'content'],
    ];
}

// 3. Create middleware for permission checking
php artisan make:middleware CheckPermission

// 4. Use in routes
Route::group(['middleware' => ['auth', 'permission:content.create']], function () {
    // Routes that require content.create permission
});
```

### 3. Adding New Features
```bash
# 1. Create feature structure
php artisan make:model FeatureName -mrc
php artisan make:service FeatureService
php artisan make:repository FeatureRepository

# 2. Create requests
php artisan make:request Feature/CreateFeatureRequest
php artisan make:request Feature/UpdateFeatureRequest

# 3. Create resources
php artisan make:resource FeatureResource
php artisan make:resource FeatureCollection

# 4. Create tests
php artisan make:test Feature/FeatureTest
php artisan make:test Unit/FeatureServiceTest
```

## 🧪 Testing Guidelines

### 1. Test Structure
```
tests/
├── Feature/                   # Integration Tests
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── ProfileTest.php
│   ├── Admin/
│   │   ├── UserManagementTest.php
│   │   └── DashboardTest.php
│   └── Api/
│       ├── AuthApiTest.php
│       └── UserApiTest.php
└── Unit/                      # Unit Tests
    ├── Models/
    │   ├── UserTest.php
    │   ├── RoleTest.php
    │   └── PermissionTest.php
    ├── Services/
    │   ├── AuthServiceTest.php
    │   └── UserServiceTest.php
    └── Repositories/
        └── UserRepositoryTest.php
```

### 2. Test Examples
```php
// Feature Test Example
class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'username' => 'invaliduser',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors(['username']);
        $this->assertGuest();
    }
}

// Unit Test Example
class UserServiceTest extends TestCase
{
    public function test_can_create_user()
    {
        $userService = new UserService(new UserRepository(new User()));
        
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'password123'
        ];
        
        $user = $userService->createUser($userData);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->first_name);
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
```

### 3. Testing Commands
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run tests with coverage
php artisan test --coverage

# Run tests in parallel
php artisan test --parallel

# Run specific test method
php artisan test --filter test_user_can_login

# Generate test coverage report
php artisan test --coverage-html reports/
```

## 🔄 API Development

### 1. API Structure
```php
// API Routes (routes/api.php)
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('user', [UserController::class, 'profile']);
        Route::put('user', [UserController::class, 'updateProfile']);
        
        // Admin routes
        Route::middleware('role:admin,super_admin')->group(function () {
            Route::apiResource('users', AdminUserController::class);
            Route::get('reports/users', [ReportController::class, 'users']);
        });
    });
});
```

### 2. API Resources
```php
// UserResource.php
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'username' => $this->username,
            'profile_image' => $this->profile_image ? asset('storage/avatars/' . $this->profile_image) : null,
            'status' => $this->status,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}

// API Response Helper
trait ApiResponse
{
    protected function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ], $code);
    }

    protected function errorResponse($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toISOString()
        ], $code);
    }
}
```

### 3. API Authentication
```php
// Sanctum configuration
// config/sanctum.php
'expiration' => 60 * 24, // 24 hours

// Middleware for API
Route::middleware('auth:sanctum')->group(function () {
    // Protected API routes
});

// Creating tokens in LoginController
$token = $user->createToken('auth-token', ['user-access'])->plainTextToken;

// Revoking tokens in LogoutController
$request->user()->currentAccessToken()->delete();
```

## 🏛️ Service Layer Implementation

### 1. Service Class Template
```php
<?php
// File: app/Services/UserService.php

namespace App\Services;

use App\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->create($data);
    }

    public function updateProfile(User $user, array $data): User
    {
        return $this->userRepository->update($user, $data);
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $this->userRepository->update($user, [
            'password' => Hash::make($newPassword)
        ]);
    }

    public function uploadAvatar(User $user, UploadedFile $file): string
    {
        $fileName = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/avatars', $fileName);
        
        $this->userRepository->update($user, ['profile_image' => $fileName]);
        
        return $fileName;
    }

    public function getProfileCompletion(User $user): int
    {
        $fields = ['first_name', 'last_name', 'email', 'phone', 'profile_image'];
        $completed = 0;
        
        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $completed++;
            }
        }
        
        return round(($completed / count($fields)) * 100);
    }
}
```

### 2. Repository Pattern
```php
<?php
// File: app/Repositories/UserRepositoryInterface.php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByUsername(string $username): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
    public function getPaginated(?string $search = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator;
}

// File: app/Repositories/UserRepository.php
class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?User
    {
        return $this->model->with(['roles'])->find($id);
    }

    public function getPaginated(?string $search = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['roles']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }
}
```

## 🔧 Custom Middleware Development

### 1. Role-Based Middleware
```php
<?php
// File: app/Http/Middleware/CheckRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }
}

// Register in app/Http/Kernel.php
protected $routeMiddleware = [
    'role' => \App\Http\Middleware\CheckRole::class,
    'permission' => \App\Http\Middleware\CheckPermission::class,
];
```

### 2. Activity Logging Middleware
```php
<?php
// File: app/Http/Middleware/LogActivity.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityService;

class LogActivity
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log activity for authenticated users
        if (Auth::check() && $this->shouldLog($request)) {
            $this->activityService->logRequest($request, $response);
        }

        return $response;
    }

    protected function shouldLog(Request $request): bool
    {
        // Don't log certain routes
        $excludeRoutes = ['api/health', '_debugbar', 'telescope'];
        
        foreach ($excludeRoutes as $route) {
            if (str_contains($request->path(), $route)) {
                return false;
            }
        }

        return true;
    }
}
```

## 📊 Database Optimization

### 1. Query Optimization
```php
// Bad: N+1 Problem
$users = User::all();
foreach ($users as $user) {
    echo $user->roles->first()->name; // N+1 queries
}

// Good: Eager Loading
$users = User::with('roles')->get();
foreach ($users as $user) {
    echo $user->roles->first()->name; // 2 queries only
}

// Better: Specific columns
$users = User::with(['roles:id,name'])->select('id', 'first_name', 'last_name')->get();
```

### 2. Database Indexes
```php
// Migration with proper indexes
Schema::create('user_activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('action');
    $table->timestamp('created_at');
    
    // Composite index for common queries
    $table->index(['user_id', 'created_at']);
    $table->index(['action', 'created_at']);
});
```

### 3. Query Caching
```php
// Cache query results
public function getActiveUsersCount(): int
{
    return Cache::remember('active_users_count', 300, function () {
        return User::where('status', 'active')->count();
    });
}

// Clear cache when data changes
public function updateUser(User $user, array $data): User
{
    $user = $this->userRepository->update($user, $data);
    
    // Clear related caches
    Cache::forget('active_users_count');
    Cache::forget("user_profile_{$user->id}");
    
    return $user;
}
```

## 🎨 Frontend Development

### 1. Asset Compilation
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/admin.js',
                'resources/js/super-admin.js'
            ],
            refresh: true,
        }),
    ],
});
```

### 2. JavaScript Modules
```javascript
// resources/js/modules/userManagement.js
export class UserManagement {
    constructor() {
        this.initEventListeners();
    }

    initEventListeners() {
        // Search functionality
        document.getElementById('userSearch')?.addEventListener('input', 
            this.debounce(this.handleSearch.bind(this), 300)
        );

        // Status update
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', this.handleStatusChange.bind(this));
        });
    }

    async handleStatusChange(event) {
        const userId = event.target.dataset.userId;
        const newStatus = event.target.value;
        
        try {
            const response = await fetch(`/admin/users/${userId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status: newStatus })
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('success', result.message);
            } else {
                this.showNotification('error', result.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาดในการอัปเดตสถานะ');
        }
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new UserManagement();
});
```

### 3. SCSS Organization
```scss
// resources/sass/app.scss
@import 'bootstrap/scss/bootstrap';
@import 'variables';
@import 'layouts/app';
@import 'components/cards';
@import 'components/forms';
@import 'components/tables';
@import 'pages/auth';
@import 'pages/dashboard';
@import 'pages/profile';

// resources/sass/_variables.scss
:root {
    --primary-color: #3B82F6;
    --secondary-color: #10B981;
    --success-color: #10B981;
    --danger-color: #EF4444;
    --warning-color: #F59E0B;
    --info-color: #06B6D4;
    --light-color: #F8FAFC;
    --dark-color: #1E293B;
}
```

## 🚀 Deployment Guide

### 1. Production Checklist
```bash
# Environment configuration
cp .env.example .env.production

# Security settings
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...

# Database optimization
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=production_db

# Cache configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Build assets
npm run build
```

### 2. Server Configuration
```nginx
# Nginx configuration
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🔒 Security Implementation

### 1. Input Validation
```php
// Form Request Validation
class UpdateUserRequest extends FormRequest
{
    public function rules()
    {
        $userId = $this->route('user');
        
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$userId}",
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
            'username' => "required|string|min:3|max:20|unique:users,username,{$userId}",
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'กรุณากรอกชื่อ',
            'email.unique' => 'อีเมลนี้มีการใช้งานแล้ว',
            'phone.regex' => 'เบอร์โทรศัพท์ไม่ถูกต้อง',
        ];
    }
}
```

### 2. XSS Protection
```php
// Output encoding in Blade
{{ $user->name }}  // Escaped automatically
{!! $user->bio !!} // Raw output (use carefully)

// HTML Purifier for rich content
composer require mews/purifier
echo clean($user->bio);
```

### 3. CSRF Protection
```blade
<!-- In forms -->
@csrf

<!-- In AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>
```

## 📈 Performance Optimization

### 1. Database Query Optimization
```php
// Use query builder for complex queries
DB::table('users')
    ->select('id', 'first_name', 'last_name', 'email', 'status')
    ->where('status', 'active')
    ->whereHas('roles', function($query) {
        $query->where('name', 'user');
    })
    ->orderBy('created_at', 'desc')
    ->limit(50)
    ->get();

// Use raw queries for aggregations
DB::select('
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count
    FROM users 
    WHERE created_at >= ? 
    GROUP BY DATE(created_at)
    ORDER BY date DESC
', [now()->subDays(30)]);
```

### 2. Caching Strategies
```php
// Model caching
class User extends Model
{
    public function getRolesAttribute()
    {
        return Cache::remember("user_roles_{$this->id}", 3600, function () {
            return $this->roles()->get();
        });
    }
}

// View caching
@cache('user-dashboard-' . $user->id, 300)
    @include('components.user-stats', ['user' => $user])
@endcache
```

### 3. Asset Optimization
```bash
# Optimize images
npm install imagemin imagemin-mozjpeg imagemin-pngquant --save-dev

# Minify CSS/JS
npm run build

# Use CDN for static assets
# In .env
ASSET_URL=https://cdn.yourdomain.com
```

## 🧪 Testing Best Practices

### 1. Test Database Setup
```php
// Use in-memory SQLite for faster tests
// phpunit.xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>

// Use factories for test data
public function test_admin_can_view_users()
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $users = User::factory()->count(5)->create();
    
    $response = $this->actingAs($admin)->get('/admin/users');
    
    $response->assertStatus(200);
    $response->assertSee($users->first()->full_name);
}
```

### 2. Feature Testing
```php
// Test complete user flows
public function test_user_registration_flow()
{
    // 1. Visit registration page
    $response = $this->get('/register');
    $response->assertStatus(200);
    
    // 2. Submit registration form
    $userData = [
        'prefix' => 'นาย',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'phone' => '0812345678',
        'username' => 'testuser',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => true
    ];
    
    $response = $this->post('/register', $userData);
    
    // 3. Assert user is created and redirected
    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'username' => 'testuser'
    ]);
    
    // 4. Assert user has default role
    $user = User::where('email', 'test@example.com')->first();
    $this->assertTrue($user->hasRole('user'));
}
```

## 🔄 Event-Driven Architecture

### 1. Creating Events
```php
// Create event
php artisan make:event UserRegistered

// Event class
class UserRegistered
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}

// Create listener
php artisan make:listener SendWelcomeEmail --event=UserRegistered

// Listener class
class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
    {
        Mail::to($event->user->email)->send(new WelcomeMail($event->user));
    }
}

// Register in EventServiceProvider
protected $listen = [
    UserRegistered::class => [
        SendWelcomeEmail::class,
        AssignDefaultRole::class,
        LogUserActivity::class,
    ],
];
```

### 2. Job Queues
```php
// Create job
php artisan make:job ProcessUserReport

// Job class
class ProcessUserReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportType;
    protected $dateRange;

    public function __construct(string $reportType, array $dateRange)
    {
        $this->reportType = $reportType;
        $this->dateRange = $dateRange;
    }

    public function handle(ReportService $reportService)
    {
        $reportService->generateReport($this->reportType, $this->dateRange);
    }
}

// Dispatch job
ProcessUserReport::dispatch('monthly', ['start' => now()->startOfMonth(), 'end' => now()]);
```

## 📝 Code Style and Standards

### 1. PSR Standards
```php
// Follow PSR-12 coding style
class UserService
{
    public function createUser(array $data): User
    {
        // Validate input
        $this->validateUserData($data);
        
        // Process data
        $processedData = $this->processUserData($data);
        
        // Create user
        return $this->userRepository->create($processedData);
    }
    
    private function validateUserData(array $data): void
    {
        // Validation logic
    }
}
```

### 2. Documentation Standards
```php
/**
 * Create a new user in the system.
 *
 * @param array $data User data including first_name, last_name, email, etc.
 * @return User The created user instance
 * @throws \Exception If user creation fails
 * 
 * @example
 * $userData = [
 *     'first_name' => 'John',
 *     'last_name' => 'Doe',
 *     'email' => 'john@example.com',
 *     'username' => 'johndoe',
 *     'password' => 'securepassword'
 * ];
 * $user = $userService->createUser($userData);
 */
public function createUser(array $data): User
{
    // Implementation
}
```

## 🔍 Debugging and Troubleshooting

### 1. Laravel Debugging Tools
```bash
# Install Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev

# Install Telescope (for production monitoring)
composer require laravel/telescope
php artisan telescope:install
php artisan migrate

# Use Log::debug() for debugging
Log::debug('User creation data', $userData);
Log::info('User created successfully', ['user_id' => $user->id]);
```

### 2. Database Debugging
```php
// Enable query logging
DB::enableQueryLog();
// ... your code ...
$queries = DB::getQueryLog();
dd($queries);

// Use explain for query analysis
DB::select('EXPLAIN SELECT * FROM users WHERE status = ?', ['active']);
```

### 3. Performance Monitoring
```php
// Measure execution time
$start = microtime(true);
// ... your code ...
$end = microtime(true);
Log::info('Operation took ' . ($end - $start) . ' seconds');

// Use Laravel Telescope for monitoring
```

---

**คู่มือเวอร์ชัน:** 1.0  
**อัปเดตล่าสุด:** 31 สิงหาคม 2025  
**สำหรับ Developer Level:** Intermediate to Advanced
