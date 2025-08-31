# 🎨 View Templates

## 📋 Overview
เทมเพลต Blade Views สำหรับระบบ Authentication แบบ Role-Based พร้อม Modern UI/UX

## 🏗️ Layout Templates

### 1. Main App Layout
```blade
{{-- File: resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="@yield('body-class', '')">
    <!-- Navigation -->
    @include('components.navigation')
    
    <!-- Main Content -->
    <main class="main-content">
        @include('components.alerts')
        
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('components.footer')
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
```

### 2. Auth Layout
```blade
{{-- File: resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-container">
            @yield('content')
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @stack('scripts')
    
    <!-- Global Alert Handler -->
    @if (session('success'))
        <script>
            Swal.fire({
                title: "สำเร็จ!",
                icon: "success",
                text: "{{ session('success') }}",
                draggable: true,
                timer: 3000
            });
        </script>
    @endif
    
    @if (session('error'))
        <script>
            Swal.fire({
                title: "ข้อผิดพลาด!",
                icon: "error",
                text: "{{ session('error') }}",
                draggable: true
            });
        </script>
    @endif
</body>
</html>
```

### 3. Admin Layout
```blade
{{-- File: resources/views/layouts/admin.blade.php --}}
@extends('layouts.app')

@section('body-class', 'admin-layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            @include('components.admin.sidebar')
        </nav>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Breadcrumb -->
            @include('components.breadcrumb')
            
            <!-- Page Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    @yield('page-actions')
                </div>
            </div>
            
            <!-- Page Content -->
            @yield('page-content')
        </main>
    </div>
</div>
@endsection
```

## 🔐 Authentication Views

### 1. Enhanced Login View
```blade
{{-- File: resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'เข้าสู่ระบบ')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <img src="{{ asset('images/logo/app-logo.png') }}" alt="{{ config('app.name') }}" class="auth-logo">
        <h2 class="auth-title">เข้าสู่ระบบ</h2>
        <p class="auth-subtitle">กรุณาเข้าสู่ระบบเพื่อใช้งาน</p>
    </div>
    
    <div class="auth-body">
        <form action="{{ route('login') }}" method="POST" class="auth-form" id="loginForm">
            @csrf
            
            <!-- Username Field -->
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="bi bi-person"></i> ชื่อผู้ใช้งาน
                </label>
                <input 
                    type="text" 
                    class="form-control @error('username') is-invalid @enderror" 
                    id="username" 
                    name="username" 
                    value="{{ old('username') }}" 
                    required 
                    autocomplete="username"
                    autofocus
                >
                @error('username')
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </div>
                @enderror
            </div>
            
            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="bi bi-lock"></i> รหัสผ่าน
                </label>
                <div class="password-input-group">
                    <input 
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="passwordToggleIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </div>
                @enderror
            </div>
            
            <!-- Remember Me -->
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        จดจำการเข้าสู่ระบบ
                    </label>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-auth" id="loginBtn">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                    </span>
                    <span class="btn-loading d-none">
                        <i class="bi bi-arrow-clockwise spin"></i> กำลังเข้าสู่ระบบ...
                    </span>
                </button>
            </div>
        </form>
        
        <!-- Additional Links -->
        <div class="auth-links">
            <a href="{{ route('password.request') }}" class="auth-link">
                <i class="bi bi-question-circle"></i> ลืมรหัสผ่าน?
            </a>
            <div class="auth-divider">
                <span>หรือ</span>
            </div>
            <a href="{{ route('register') }}" class="auth-link">
                <i class="bi bi-person-plus"></i> ยังไม่มีบัญชี? ลงทะเบียนที่นี่
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
    } else {
        passwordField.type = 'password';
        toggleIcon.className = 'bi bi-eye';
    }
}

document.getElementById('loginForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('loginBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    btnText.classList.add('d-none');
    btnLoading.classList.remove('d-none');
    submitBtn.disabled = true;
});
</script>
@endpush
@endsection
```

### 2. Enhanced Register View
```blade
{{-- File: resources/views/auth/register.blade.php --}}
@extends('layouts.auth')

@section('title', 'ลงทะเบียน')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <img src="{{ asset('images/logo/app-logo.png') }}" alt="{{ config('app.name') }}" class="auth-logo">
        <h2 class="auth-title">ลงทะเบียน</h2>
        <p class="auth-subtitle">สร้างบัญชีใหม่เพื่อเข้าใช้งานระบบ</p>
    </div>
    
    <div class="auth-body">
        <form action="{{ route('register') }}" method="POST" class="auth-form" id="registerForm">
            @csrf
            
            <!-- Name Fields -->
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="prefix" class="form-label">คำนำหน้า</label>
                        <select class="form-select @error('prefix') is-invalid @enderror" id="prefix" name="prefix" required>
                            <option value="">เลือกคำนำหน้า</option>
                            <option value="นาย" {{ old('prefix') == 'นาย' ? 'selected' : '' }}>นาย</option>
                            <option value="นาง" {{ old('prefix') == 'นาง' ? 'selected' : '' }}>นาง</option>
                            <option value="นางสาว" {{ old('prefix') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                        </select>
                        @error('prefix')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="first_name" class="form-label">ชื่อ</label>
                        <input 
                            type="text" 
                            class="form-control @error('first_name') is-invalid @enderror" 
                            id="first_name" 
                            name="first_name" 
                            value="{{ old('first_name') }}" 
                            required
                        >
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="last_name" class="form-label">นามสกุล</label>
                        <input 
                            type="text" 
                            class="form-control @error('last_name') is-invalid @enderror" 
                            id="last_name" 
                            name="last_name" 
                            value="{{ old('last_name') }}" 
                            required
                        >
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Contact Fields -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> อีเมล
                        </label>
                        <input 
                            type="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="bi bi-telephone"></i> เบอร์โทรศัพท์
                        </label>
                        <input 
                            type="tel" 
                            class="form-control @error('phone') is-invalid @enderror" 
                            id="phone" 
                            name="phone" 
                            value="{{ old('phone') }}" 
                            required
                        >
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Account Fields -->
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="bi bi-person"></i> ชื่อผู้ใช้งาน
                </label>
                <input 
                    type="text" 
                    class="form-control @error('username') is-invalid @enderror" 
                    id="username" 
                    name="username" 
                    value="{{ old('username') }}" 
                    required
                >
                <div class="form-text">ชื่อผู้ใช้งานจะใช้สำหรับเข้าสู่ระบบ (3-20 ตัวอักษร)</div>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Password Fields -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> รหัสผ่าน
                        </label>
                        <div class="password-input-group">
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="bi bi-eye" id="passwordToggleIcon"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">
                            <i class="bi bi-lock-fill"></i> ยืนยันรหัสผ่าน
                        </label>
                        <div class="password-input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="bi bi-eye" id="confirmPasswordToggleIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Terms Agreement -->
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" required>
                    <label class="form-check-label" for="terms">
                        ฉันยอมรับ <a href="#" target="_blank">ข้อกำหนดการใช้งาน</a> และ <a href="#" target="_blank">นโยบายความเป็นส่วนตัว</a>
                    </label>
                    @error('terms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-auth" id="registerBtn">
                    <span class="btn-text">
                        <i class="bi bi-person-plus"></i> ลงทะเบียน
                    </span>
                    <span class="btn-loading d-none">
                        <i class="bi bi-arrow-clockwise spin"></i> กำลังลงทะเบียน...
                    </span>
                </button>
            </div>
        </form>
        
        <!-- Additional Links -->
        <div class="auth-links">
            <a href="{{ route('login') }}" class="auth-link">
                <i class="bi bi-arrow-left"></i> กลับไปหน้าเข้าสู่ระบบ
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    const strength = checkPasswordStrength(password);
    
    strengthDiv.innerHTML = `
        <div class="strength-meter">
            <div class="strength-bar strength-${strength.level}"></div>
        </div>
        <small class="strength-text">${strength.text}</small>
    `;
});

function checkPasswordStrength(password) {
    let score = 0;
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    
    const levels = ['weak', 'fair', 'good', 'strong', 'very-strong'];
    const texts = ['อ่อนแอ', 'พอใช้', 'ดี', 'แข็งแรง', 'แข็งแรงมาก'];
    
    return {
        level: levels[score - 1] || 'weak',
        text: texts[score - 1] || 'อ่อนแอ'
    };
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + 'ToggleIcon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// Form submission handling
document.getElementById('registerForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('registerBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    btnText.classList.add('d-none');
    btnLoading.classList.remove('d-none');
    submitBtn.disabled = true;
});
</script>
@endpush
@endsection
```

## 👤 User Dashboard Views

### 1. User Dashboard
```blade
{{-- File: resources/views/user/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'ยินดีต้อนรับ, ' . Auth::user()->full_name)

@section('content')
<div class="container-fluid">
    <!-- Quick Stats -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stats-title">การเข้าสู่ระบบทั้งหมด</div>
                            <div class="stats-number">{{ $stats['login_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-arrow-in-right fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stats-title">เข้าสู่ระบบล่าสุด</div>
                            <div class="stats-number">{{ $stats['last_login']?->diffForHumans() ?? 'ไม่มีข้อมูล' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stats-title">อายุบัญชี</div>
                            <div class="stats-number">{{ $stats['account_age'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stats-title">ความสมบูรณ์โปรไฟล์</div>
                            <div class="stats-number">{{ $stats['profile_completion'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Row -->
    <div class="row">
        <!-- Profile Summary -->
        <div class="col-lg-4 mb-4">
            <div class="card profile-card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-person-circle"></i> ข้อมูลโปรไฟล์
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="profile-avatar mb-3">
                        <img src="{{ $user->profile_image ? asset('storage/avatars/' . $user->profile_image) : asset('images/default-avatar.png') }}" 
                             alt="Profile" class="avatar-lg rounded-circle">
                    </div>
                    <h5 class="profile-name">{{ $user->full_name }}</h5>
                    <p class="profile-username text-muted">{{ '@' . $user->username }}</p>
                    <p class="profile-email">{{ $user->email }}</p>
                    
                    <div class="profile-actions">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil"></i> แก้ไขโปรไฟล์
                        </a>
                        <a href="{{ route('profile.security') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-shield-lock"></i> ความปลอดภัย
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="col-lg-8 mb-4">
            <div class="card activity-card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-activity"></i> กิจกรรมล่าสุด
                    </h6>
                </div>
                <div class="card-body">
                    @if($activities->count() > 0)
                        <div class="activity-timeline">
                            @foreach($activities as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="bi {{ $activity->getIconClass() }}"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">{{ $activity->getDisplayName() }}</div>
                                        <div class="activity-time text-muted">
                                            <i class="bi bi-clock"></i> {{ $activity->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('profile.activities') }}" class="btn btn-outline-primary btn-sm">
                                ดูกิจกรรมทั้งหมด
                            </a>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-inbox empty-icon"></i>
                            <p class="empty-text">ยังไม่มีกิจกรรม</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card quick-actions-card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning"></i> การดำเนินการด่วน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('profile.edit') }}" class="quick-action-item">
                                <div class="quick-action-icon">
                                    <i class="bi bi-person-gear"></i>
                                </div>
                                <div class="quick-action-text">แก้ไขโปรไฟล์</div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('profile.security') }}" class="quick-action-item">
                                <div class="quick-action-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="quick-action-text">ความปลอดภัย</div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('profile.activities') }}" class="quick-action-item">
                                <div class="quick-action-icon">
                                    <i class="bi bi-list-ul"></i>
                                </div>
                                <div class="quick-action-text">ประวัติกิจกรรม</div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('support') }}" class="quick-action-item">
                                <div class="quick-action-icon">
                                    <i class="bi bi-headset"></i>
                                </div>
                                <div class="quick-action-text">ติดต่อสนับสนุน</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## 🎨 Component Templates

### 1. Navigation Component
```blade
{{-- File: resources/views/components/navigation.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('images/logo/app-logo-white.png') }}" alt="{{ config('app.name') }}" height="32">
            {{ config('app.name') }}
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @auth
                    @if(Auth::user()->hasRole('user'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="bi bi-house"></i> หน้าแรก
                            </a>
                        </li>
                    @endif
                    
                    @if(Auth::user()->hasRole('admin'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Admin Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people"></i> จัดการผู้ใช้
                            </a>
                        </li>
                    @endif
                    
                    @if(Auth::user()->hasRole('super_admin'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('super-admin.dashboard') }}">
                                <i class="bi bi-gear"></i> Super Admin
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
            
            <!-- User Menu -->
            <ul class="navbar-nav">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="bi bi-person-plus"></i> ลงทะเบียน
                        </a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="{{ Auth::user()->profile_image ? asset('storage/avatars/' . Auth::user()->profile_image) : asset('images/default-avatar.png') }}" 
                                 alt="Profile" class="avatar-sm rounded-circle me-1">
                            {{ Auth::user()->first_name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="bi bi-person"></i> โปรไฟล์
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.security') }}">
                                    <i class="bi bi-shield-lock"></i> ความปลอดภัย
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="confirmLogout(event)">
                                    <i class="bi bi-box-arrow-right text-danger"></i> ออกจากระบบ
                                </a>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

@push('scripts')
<script>
function confirmLogout(event) {
    event.preventDefault();
    
    Swal.fire({
        title: 'ยืนยันการออกจากระบบ',
        text: 'คุณต้องการออกจากระบบหรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ออกจากระบบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route("logout") }}';
        }
    });
}
</script>
@endpush
```

### 2. Alert Component
```blade
{{-- File: resources/views/components/alerts.blade.php --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>เกิดข้อผิดพลาด!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <strong>สำเร็จ!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>คำเตือน!</strong> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill"></i>
        <strong>ข้อมูล:</strong> {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
```

## 📊 Data Table Template

### 1. User Management Table
```blade
{{-- File: resources/views/components/user-table.blade.php --}}
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="card-title mb-0">
                    <i class="bi bi-people"></i> รายการผู้ใช้งาน
                </h6>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus"></i> เพิ่มผู้ใช้ใหม่
                </a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Search and Filter -->
        <div class="table-controls mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="ค้นหาผู้ใช้..." id="userSearch">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">ทุกสถานะ</option>
                        <option value="active">ใช้งาน</option>
                        <option value="inactive">ไม่ใช้งาน</option>
                        <option value="suspended">ถูกระงับ</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="roleFilter">
                        <option value="">ทุกบทบาท</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise"></i> รีเซ็ต
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="60">#</th>
                        <th>ผู้ใช้งาน</th>
                        <th>ติดต่อ</th>
                        <th>บทบาท</th>
                        <th>สถานะ</th>
                        <th>เข้าสู่ระบบล่าสุด</th>
                        <th width="120">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                            <td>
                                <div class="user-info">
                                    <img src="{{ $user->profile_image ? asset('storage/avatars/' . $user->profile_image) : asset('images/default-avatar.png') }}" 
                                         alt="Profile" class="avatar-sm rounded-circle me-2">
                                    <div>
                                        <div class="user-name">{{ $user->full_name }}</div>
                                        <small class="text-muted">{{ '@' . $user->username }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $user->email }}</div>
                                <small class="text-muted">{{ $user->phone }}</small>
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-{{ $role->getBadgeColor() }}">{{ $role->display_name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->getStatusBadgeColor() }}">
                                    {{ $user->getStatusDisplayName() }}
                                </span>
                            </td>
                            <td>
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'ไม่เคย' }}
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-secondary btn-sm" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete({{ $user->id }})" title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="bi bi-inbox empty-icon"></i>
                                    <p class="empty-text">ไม่พบข้อมูลผู้ใช้งาน</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="pagination-info">
                แสดง {{ $users->firstItem() }} ถึง {{ $users->lastItem() }} จาก {{ $users->total() }} รายการ
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(userId) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: 'คุณต้องการลบผู้ใช้นี้หรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit delete form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('admin.users.destroy', '') }}/${userId}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Search and filter functionality
document.getElementById('userSearch').addEventListener('input', debounce(filterUsers, 300));
document.getElementById('statusFilter').addEventListener('change', filterUsers);
document.getElementById('roleFilter').addEventListener('change', filterUsers);

function filterUsers() {
    const search = document.getElementById('userSearch').value;
    const status = document.getElementById('statusFilter').value;
    const role = document.getElementById('roleFilter').value;
    
    const url = new URL(window.location);
    url.searchParams.set('search', search);
    url.searchParams.set('status', status);
    url.searchParams.set('role', role);
    
    window.location.href = url.toString();
}

function resetFilters() {
    document.getElementById('userSearch').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('roleFilter').value = '';
    
    window.location.href = window.location.pathname;
}

function debounce(func, wait) {
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
</script>
@endpush
```

## 📝 Form Templates

### 1. Profile Edit Form
```blade
{{-- File: resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'แก้ไขโปรไฟล์')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-gear"></i> แก้ไขข้อมูลโปรไฟล์
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Profile Image -->
                        <div class="form-group text-center mb-4">
                            <div class="profile-image-upload">
                                <img src="{{ $user->profile_image ? asset('storage/avatars/' . $user->profile_image) : asset('images/default-avatar.png') }}" 
                                     alt="Profile" class="profile-preview" id="profilePreview">
                                <div class="profile-upload-overlay">
                                    <i class="bi bi-camera"></i>
                                    <span>เปลี่ยนรูป</span>
                                </div>
                                <input type="file" class="profile-upload-input" id="profileImage" name="profile_image" accept="image/*">
                            </div>
                            @error('profile_image')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="prefix" class="form-label">คำนำหน้า</label>
                                    <select class="form-select @error('prefix') is-invalid @enderror" id="prefix" name="prefix" required>
                                        <option value="นาย" {{ $user->prefix == 'นาย' ? 'selected' : '' }}>นาย</option>
                                        <option value="นาง" {{ $user->prefix == 'นาง' ? 'selected' : '' }}>นาง</option>
                                        <option value="นางสาว" {{ $user->prefix == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                    </select>
                                    @error('prefix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">ชื่อ</label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('first_name') is-invalid @enderror" 
                                        id="first_name" 
                                        name="first_name" 
                                        value="{{ old('first_name', $user->first_name) }}" 
                                        required
                                    >
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="last_name" class="form-label">นามสกุล</label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('last_name') is-invalid @enderror" 
                                        id="last_name" 
                                        name="last_name" 
                                        value="{{ old('last_name', $user->last_name) }}" 
                                        required
                                    >
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope"></i> อีเมล
                                    </label>
                                    <input 
                                        type="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        id="email" 
                                        name="email" 
                                        value="{{ old('email', $user->email) }}" 
                                        required
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="bi bi-telephone"></i> เบอร์โทรศัพท์
                                    </label>
                                    <input 
                                        type="tel" 
                                        class="form-control @error('phone') is-invalid @enderror" 
                                        id="phone" 
                                        name="phone" 
                                        value="{{ old('phone', $user->phone) }}" 
                                        required
                                    >
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> ยกเลิก
                                </a>
                                <button type="submit" class="btn btn-primary" id="updateBtn">
                                    <span class="btn-text">
                                        <i class="bi bi-check"></i> บันทึกการเปลี่ยนแปลง
                                    </span>
                                    <span class="btn-loading d-none">
                                        <i class="bi bi-arrow-clockwise spin"></i> กำลังบันทึก...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Profile image preview
document.getElementById('profileImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Form submission
document.getElementById('profileForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('updateBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    btnText.classList.add('d-none');
    btnLoading.classList.remove('d-none');
    submitBtn.disabled = true;
});
</script>
@endpush
@endsection
```

## 🎯 View Best Practices

### 1. Blade Component Usage
```blade
<!-- Using components -->
@component('components.card', ['title' => 'Card Title'])
    Card content here
@endcomponent

<!-- Using slots -->
<x-card>
    <x-slot name="header">
        Card Header
    </x-slot>
    
    Card body content
</x-card>
```

### 2. Conditional Rendering
```blade
@auth
    <!-- Authenticated content -->
@endauth

@guest
    <!-- Guest content -->
@endguest

@can('permission-name')
    <!-- Permission-based content -->
@endcan

@role('admin')
    <!-- Role-based content -->
@endrole
```

### 3. Asset Management
```blade
@push('styles')
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('js/custom.js') }}"></script>
@endpush
```

### 4. Error Handling in Views
```blade
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@error('field_name')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
```

---

**Template Version:** 1.0  
**Created:** August 31, 2025  
**Framework:** Bootstrap 5.3  
**Icons:** Bootstrap Icons 1.10
