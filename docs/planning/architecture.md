# 🏗️ System Architecture & Design

## 📋 Architecture Overview
ระบบ Laravel Authentication Template ถูกออกแบบด้วยหลักการ **MVC (Model-View-Controller)** และ **Clean Architecture** เพื่อให้สามารถขยายและบำรุงรักษาได้ง่าย

## 🎯 Design Principles

### 1. SOLID Principles
- **Single Responsibility**: แต่ละ Class มีหน้าที่เดียว
- **Open/Closed**: เปิดสำหรับการขยาย ปิดสำหรับการแก้ไข
- **Liskov Substitution**: Subclass สามารถแทนที่ Parent class ได้
- **Interface Segregation**: แยก Interface ตามการใช้งาน
- **Dependency Inversion**: พึ่งพา Abstraction ไม่ใช่ Concrete

### 2. Laravel Best Practices
- Repository Pattern สำหรับ Data Access
- Service Layer สำหรับ Business Logic
- Form Request สำหรับ Validation
- Event-Driven Architecture
- Resource Pattern สำหรับ API

## 🏛️ System Architecture Layers

### 1. Presentation Layer (UI/UX)
```
┌─────────────────────────────────────┐
│           Frontend (Blade)          │
├─────────────────────────────────────┤
│  ├── Auth Views (Login/Register)    │
│  ├── User Dashboard & Profile       │
│  ├── Admin Management Interface     │
│  ├── Super Admin System Control     │
│  └── Shared Components & Layouts    │
└─────────────────────────────────────┘
```

### 2. Application Layer (Controllers)
```
┌─────────────────────────────────────┐
│           Controllers               │
├─────────────────────────────────────┤
│  ├── Auth Controllers               │
│  │   ├── LoginController            │
│  │   ├── RegisterController         │
│  │   └── ProfileController          │
│  ├── Admin Controllers              │
│  │   ├── DashboardController        │
│  │   ├── UserManagementController   │
│  │   └── ReportController           │
│  └── SuperAdmin Controllers         │
│      ├── AdminManagementController  │
│      ├── RoleManagementController   │
│      └── SystemController           │
└─────────────────────────────────────┘
```

### 3. Business Logic Layer (Services)
```
┌─────────────────────────────────────┐
│             Services                │
├─────────────────────────────────────┤
│  ├── AuthService                    │
│  ├── UserService                    │
│  ├── RoleService                    │
│  ├── PermissionService              │
│  ├── ActivityService                │
│  ├── ReportService                  │
│  └── SystemSettingService           │
└─────────────────────────────────────┘
```

### 4. Data Access Layer (Repositories)
```
┌─────────────────────────────────────┐
│           Repositories              │
├─────────────────────────────────────┤
│  ├── UserRepository                 │
│  ├── RoleRepository                 │
│  ├── PermissionRepository           │
│  ├── ActivityRepository             │
│  └── SystemSettingRepository        │
└─────────────────────────────────────┘
```

### 5. Data Layer (Models & Database)
```
┌─────────────────────────────────────┐
│              Models                 │
├─────────────────────────────────────┤
│  ├── User (Enhanced)                │
│  ├── Role                           │
│  ├── Permission                     │
│  ├── UserActivity                   │
│  └── SystemSetting                  │
└─────────────────────────────────────┘
│
├─────────────────────────────────────┤
│             Database                │
├─────────────────────────────────────┤
│  ├── users                          │
│  ├── roles                          │
│  ├── permissions                    │
│  ├── role_permissions               │
│  ├── user_roles                     │
│  ├── user_activities                │
│  └── system_settings                │
└─────────────────────────────────────┘
```

## 🔄 Request Flow Architecture

### 1. Authentication Flow
```
┌──────────┐    ┌──────────────┐    ┌─────────────┐    ┌──────────┐
│ Browser  │───▶│    Route     │───▶│ Controller  │───▶│ Service  │
└──────────┘    │ (web.php)    │    │ (Auth)      │    │ (Auth)   │
                └──────────────┘    └─────────────┘    └──────────┘
                       │                    │                │
                       ▼                    ▼                ▼
┌──────────┐    ┌──────────────┐    ┌─────────────┐    ┌──────────┐
│   View   │◀───│ Middleware   │◀───│ Validation  │◀───│Repository│
│ (Blade)  │    │ (Security)   │    │ (Request)   │    │ (Data)   │
└──────────┘    └──────────────┘    └─────────────┘    └──────────┘
```

### 2. User Management Flow
```
┌──────────┐    ┌──────────────┐    ┌─────────────┐    ┌──────────┐
│   Admin  │───▶│  Middleware  │───▶│ Controller  │───▶│ Service  │
│ Interface│    │ (CheckRole)  │    │ (Admin)     │    │ (User)   │
└──────────┘    └──────────────┘    └─────────────┘    └──────────┘
                       │                    │                │
                       ▼                    ▼                ▼
┌──────────┐    ┌──────────────┐    ┌─────────────┐    ┌──────────┐
│ Response │◀───│  Activity    │◀───│ Validation  │◀───│Repository│
│  (JSON)  │    │  Logging     │    │ (Request)   │    │ (User)   │
└──────────┘    └──────────────┘    └─────────────┘    └──────────┘
```

## 🗄️ Database Design

### 1. Entity Relationship Diagram
```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    Users    │       │ User_Roles  │       │    Roles    │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id (PK)     │───┐   │ user_id(FK) │   ┌───│ id (PK)     │
│ prefix      │   └──▶│ role_id(FK) │◀──┘   │ name        │
│ first_name  │       │assigned_at  │       │display_name │
│ last_name   │       │assigned_by  │       │description  │
│ email       │       └─────────────┘       └─────────────┘
│ username    │                                     │
│ password    │                                     │
│ status      │                                     ▼
│ ...         │                             ┌─────────────┐
└─────────────┘                             │Role_Perms   │
       │                                    ├─────────────┤
       │                                    │role_id (FK) │
       ▼                                    │perm_id (FK) │
┌─────────────┐                             └─────────────┘
│User_Activity│                                     │
├─────────────┤                                     ▼
│ id (PK)     │                             ┌─────────────┐
│ user_id(FK) │                             │Permissions  │
│ action      │                             ├─────────────┤
│ description │                             │ id (PK)     │
│ ip_address  │                             │ name        │
│ created_at  │                             │display_name │
└─────────────┘                             │ module      │
                                            └─────────────┘
```

### 2. Database Optimization
**Indexes:**
```sql
-- Users table
INDEX idx_users_email (email)
INDEX idx_users_username (username)  
INDEX idx_users_status (status)
INDEX idx_users_created_at (created_at)

-- User Activities table
INDEX idx_activities_user_id (user_id)
INDEX idx_activities_action (action)
INDEX idx_activities_created_at (created_at)

-- Roles and Permissions
INDEX idx_roles_name (name)
INDEX idx_permissions_name (name)
INDEX idx_permissions_module (module)
```

## 🔧 Application Architecture

### 1. Service Container Bindings
```php
// AppServiceProvider.php
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);
$this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
$this->app->bind(AuthServiceInterface::class, AuthService::class);
$this->app->bind(UserServiceInterface::class, UserService::class);
```

### 2. Middleware Stack
```php
// Route Middleware Groups
'web' => [
    \App\Http\Middleware\SecurityHeaders::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
    \App\Http\Middleware\LogActivity::class,
],

'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],

// Route-specific Middleware
'auth' => \App\Http\Middleware\Authenticate::class,
'role' => \App\Http\Middleware\CheckRole::class,
'permission' => \App\Http\Middleware\CheckPermission::class,
```

### 3. Event-Driven Architecture
```php
// Events
UserRegistered::class => [
    SendWelcomeEmail::class,
    AssignDefaultRole::class,
    LogUserActivity::class,
]

UserLoggedIn::class => [
    UpdateLastLogin::class,
    LogUserActivity::class,
    ResetFailedAttempts::class,
]

UserLoggedOut::class => [
    LogUserActivity::class,
]

PasswordReset::class => [
    SendPasswordResetNotification::class,
    LogUserActivity::class,
]
```

## 🛡️ Security Architecture

### 1. Authentication Flow
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Request   │───▶│ Validation  │───▶│  Database   │
│ (Creds)     │    │ (Rules)     │    │ (Check)     │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Session   │◀───│   Success   │◀───│    User     │
│ (Create)    │    │ (Response)  │    │ (Verified)  │
└─────────────┘    └─────────────┘    └─────────────┘
```

### 2. Authorization Flow
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Request   │───▶│ Middleware  │───▶│    Role     │
│ (Resource)  │    │ (Check)     │    │ (Verify)    │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Access    │◀───│   Grant     │◀───│Permission   │
│ (Allowed)   │    │ (Access)    │    │ (Check)     │
└─────────────┘    └─────────────┘    └─────────────┘
```

## 📡 API Architecture

### 1. RESTful API Design
```
Authentication:
POST   /api/auth/login
POST   /api/auth/logout  
POST   /api/auth/register
POST   /api/auth/refresh

User Management:
GET    /api/users
GET    /api/users/{id}
POST   /api/users
PUT    /api/users/{id}
DELETE /api/users/{id}

Profile Management:
GET    /api/profile
PUT    /api/profile
POST   /api/profile/avatar
PUT    /api/profile/password

Admin Management:
GET    /api/admin/users
GET    /api/admin/reports
GET    /api/admin/settings
PUT    /api/admin/settings

Super Admin:
GET    /api/super-admin/admins
GET    /api/super-admin/roles
POST   /api/super-admin/roles
GET    /api/super-admin/system
```

### 2. API Response Structure
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  },
  "meta": {
    "pagination": {
      "current_page": 1,
      "total_pages": 10,
      "total_items": 100
    },
    "timestamp": "2025-08-31T10:00:00Z"
  }
}
```

## 🔒 Security Architecture

### 1. Authentication Security
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  Password   │───▶│   Hashing   │───▶│  Database   │
│ (Plain)     │    │ (bcrypt)    │    │ (Stored)    │
└─────────────┘    └─────────────┘    └─────────────┘

┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Session   │───▶│ Encryption  │───▶│   Storage   │
│ (Data)      │    │ (Laravel)   │    │ (Secure)    │
└─────────────┘    └─────────────┘    └─────────────┘
```

### 2. Authorization Security
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Request   │───▶│    Auth     │───▶│    Role     │
│ (Action)    │    │ (Check)     │    │ (Verify)    │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Permit    │◀───│   Allow     │◀───│Permission   │
│ (Access)    │    │ (Grant)     │    │ (Check)     │
└─────────────┘    └─────────────┘    └─────────────┘
```

## 🏗️ File Structure Architecture

### 1. Backend Structure
```
app/
├── Console/
│   └── Commands/               # Custom Artisan Commands
├── Exceptions/
│   └── Handler.php             # Exception Handling
├── Http/
│   ├── Controllers/
│   │   ├── Auth/               # Authentication Controllers
│   │   ├── User/               # User Controllers  
│   │   ├── Admin/              # Admin Controllers
│   │   ├── SuperAdmin/         # Super Admin Controllers
│   │   └── Api/                # API Controllers
│   ├── Middleware/             # Custom Middleware
│   ├── Requests/               # Form Request Validation
│   └── Resources/              # API Resources
├── Models/                     # Eloquent Models
├── Providers/                  # Service Providers
├── Repositories/               # Repository Pattern
├── Services/                   # Business Logic Services
├── Events/                     # Event Classes
├── Listeners/                  # Event Listeners
└── Jobs/                       # Background Jobs
```

### 2. Frontend Structure
```
resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php       # Main Layout
│   │   ├── auth.blade.php      # Auth Layout
│   │   ├── admin.blade.php     # Admin Layout
│   │   └── super-admin.blade.php # Super Admin Layout
│   ├── components/             # Reusable Components
│   ├── auth/                   # Authentication Views
│   ├── user/                   # User Views
│   ├── admin/                  # Admin Views
│   └── super-admin/            # Super Admin Views
├── js/
│   ├── app.js                  # Main JS File
│   ├── components/             # JS Components
│   └── modules/                # Feature Modules
└── sass/
    ├── app.scss                # Main SCSS File
    ├── components/             # Component Styles
    └── layouts/                # Layout Styles
```

### 3. Configuration Structure
```
config/
├── auth.php                    # Authentication Config
├── permission.php              # Permission Config (New)
├── security.php                # Security Config (New)
├── app.php                     # Application Config
└── services.php                # Third-party Services
```

## 🔄 Data Flow Architecture

### 1. User Registration Flow
```
Form Input → Validation → Password Hash → Database → Email → Role Assignment → Redirect
```

### 2. User Login Flow
```
Credentials → Validation → Authentication → Session → Activity Log → Role Check → Dashboard
```

### 3. User Management Flow
```
Admin Request → Authorization → Validation → Business Logic → Database → Activity Log → Response
```

## 🚀 Deployment Architecture

### 1. Development Environment
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Laravel   │───▶│   Laragon   │───▶│   Browser   │
│ Application │    │   Server    │    │   Testing   │
└─────────────┘    └─────────────┘    └─────────────┘
```

### 2. Production Environment  
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    NGINX    │───▶│   PHP-FPM   │───▶│   Laravel   │
│Load Balancer│    │   Workers   │    │Application  │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    Redis    │    │    MySQL    │    │   Storage   │
│   Cache     │    │  Database   │    │    Files    │
└─────────────┘    └─────────────┘    └─────────────┘
```

## 📊 Performance Architecture

### 1. Caching Strategy
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Browser   │───▶│    CDN      │───▶│   Assets    │
│   Cache     │    │   Cache     │    │  (Static)   │
└─────────────┘    └─────────────┘    └─────────────┘

┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│Application  │───▶│    Redis    │───▶│  Database   │
│   Cache     │    │   Cache     │    │  (Dynamic)  │
└─────────────┘    └─────────────┘    └─────────────┘
```

### 2. Query Optimization
- Database indexing strategy
- Eager loading relationships
- Query result caching
- Connection pooling

## 🎨 Frontend Architecture

### 1. Component Architecture
```
Layouts/
├── AppLayout (Main)
├── AuthLayout (Login/Register)
├── AdminLayout (Admin Panel)
└── SuperAdminLayout (Super Admin Panel)

Components/
├── Navigation/
├── Forms/
├── Tables/
├── Cards/
├── Modals/
└── Alerts/
```

### 2. Asset Management
```
Source Files → Compilation → Optimization → Deployment
     ↓              ↓            ↓            ↓
   SCSS         →  CSS      → Minified  →  CDN/Public
JavaScript    →   JS       → Bundled   →  CDN/Public
Images        → Optimized → Compressed → CDN/Public
```

## 📱 Mobile-First Architecture

### 1. Responsive Breakpoints
```
Mobile:    320px - 767px
Tablet:    768px - 1023px
Desktop:   1024px - 1439px
Large:     1440px+
```

### 2. Progressive Enhancement
- Basic functionality works without JavaScript
- Enhanced features with JavaScript enabled
- Graceful degradation for older browsers

## 🔄 State Management

### 1. Session State
- User authentication status
- User role and permissions
- Shopping cart (if applicable)
- Form data persistence

### 2. Application State
- System settings
- Cached user data
- Activity logs
- Temporary files

## 📈 Monitoring Architecture

### 1. Application Monitoring
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Laravel   │───▶│    Logs     │───▶│ Monitoring  │
│Application  │    │  (Files)    │    │  Dashboard  │
└─────────────┘    └─────────────┘    └─────────────┘
```

### 2. Performance Monitoring
- Response time tracking
- Database query monitoring
- Memory usage tracking
- Error rate monitoring

## 🔧 Development Tools Architecture

### 1. Development Stack
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   VS Code   │───▶│    Git      │───▶│   GitHub    │
│    IDE      │    │  Version    │    │  Repository │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  Composer   │    │    NPM      │    │   Artisan   │
│ Dependencies│    │  Frontend   │    │   CLI       │
└─────────────┘    └─────────────┘    └─────────────┘
```

### 2. Testing Architecture
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   PHPUnit   │───▶│   Feature   │───▶│    Unit     │
│   Testing   │    │   Tests     │    │   Tests     │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Browser   │    │   Database  │    │   Mocking   │
│   Testing   │    │   Testing   │    │   Tests     │
└─────────────┘    └─────────────┘    └─────────────┘
```

---

**Architecture Version:** 1.0  
**Created:** August 31, 2025  
**Last Updated:** August 31, 2025  
**Architect:** System Design Team
