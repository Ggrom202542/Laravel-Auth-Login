@extends('layouts.dashboard')

@section('title', 'การตั้งค่า')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-gear me-2"></i>
                    การตั้งค่า
                </h1>
                <div>
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        กลับ
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>เกิดข้อผิดพลาด!</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Settings Menu -->
        <div class="col-xl-3 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-list me-2"></i>
                        เมนูการตั้งค่า
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#preferences-section" class="list-group-item list-group-item-action border-0 active" data-target="preferences">
                            <i class="bi bi-palette me-2"></i>
                            การตั้งค่าทั่วไป
                        </a>
                        <a href="#notifications-section" class="list-group-item list-group-item-action border-0" data-target="notifications">
                            <i class="bi bi-bell me-2"></i>
                            การแจ้งเตือน
                        </a>
                        <a href="#security-section" class="list-group-item list-group-item-action border-0" data-target="security">
                            <i class="bi bi-shield-check me-2"></i>
                            ความปลอดภัย
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-xl-9 col-lg-8">
            <!-- General Preferences -->
            <div class="settings-section" id="preferences-section">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-palette me-2"></i>
                            การตั้งค่าทั่วไป
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update-settings') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Theme Setting -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-palette me-2"></i>
                                        ธีมเว็บไซต์
                                    </label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme" id="light_theme" 
                                               value="light" {{ old('theme', $user->theme) === 'light' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="light_theme">
                                            <i class="bi bi-sun me-1"></i>
                                            โหมดสว่าง (Light Mode)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme" id="dark_theme" 
                                               value="dark" {{ old('theme', $user->theme) === 'dark' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dark_theme">
                                            <i class="bi bi-moon me-1"></i>
                                            โหมดมืด (Dark Mode)
                                        </label>
                                    </div>
                                    <div class="form-text">เลือกธีมที่คุณต้องการใช้งาน</div>
                                </div>

                                <!-- Language Setting -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-translate me-2"></i>
                                        ภาษา
                                    </label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="language" id="thai_lang" 
                                               value="th" {{ old('language', $user->language) === 'th' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="thai_lang">
                                            <i class="bi bi-flag me-1"></i>
                                            ภาษาไทย
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="language" id="english_lang" 
                                               value="en" {{ old('language', $user->language) === 'en' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="english_lang">
                                            <i class="bi bi-flag-fill me-1"></i>
                                            English
                                        </label>
                                    </div>
                                    <div class="form-text">เลือกภาษาที่ใช้แสดงผล</div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Save Button -->
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>
                                    บันทึกการตั้งค่า
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Notifications Settings -->
            <div class="settings-section" id="notifications-section" style="display: none;">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-bell me-2"></i>
                            การแจ้งเตือน
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update-settings') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <!-- Keep current theme and language values -->
                            <input type="hidden" name="theme" value="{{ $user->theme }}">
                            <input type="hidden" name="language" value="{{ $user->language }}">

                            <div class="mb-4">
                                <h6 class="text-secondary mb-3">ประเภทการแจ้งเตือน</h6>
                                
                                <!-- Email Notifications -->
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" 
                                           name="email_notifications" value="1" 
                                           {{ old('email_notifications', $user->email_notifications) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_notifications">
                                        <i class="bi bi-envelope me-2"></i>
                                        การแจ้งเตือนทางอีเมล
                                    </label>
                                    <div class="form-text">รับการแจ้งเตือนสำคัญทางอีเมล</div>
                                </div>

                                <!-- SMS Notifications -->
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="sms_notifications" 
                                           name="sms_notifications" value="1" 
                                           {{ old('sms_notifications', $user->sms_notifications) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sms_notifications">
                                        <i class="bi bi-phone me-2"></i>
                                        การแจ้งเตือนทาง SMS
                                    </label>
                                    <div class="form-text">รับการแจ้งเตือนทาง SMS (อาจมีค่าใช้จ่าย)</div>
                                </div>

                                <!-- Push Notifications -->
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="push_notifications" 
                                           name="push_notifications" value="1" 
                                           {{ old('push_notifications', $user->push_notifications) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="push_notifications">
                                        <i class="bi bi-app-indicator me-2"></i>
                                        การแจ้งเตือนแบบ Push
                                    </label>
                                    <div class="form-text">รับการแจ้งเตือนบนเบราว์เซอร์</div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>หมายเหตุ:</strong> คุณสามารถเปลี่ยนการตั้งค่าการแจ้งเตือนได้ตลอดเวลา 
                                การแจ้งเตือนจะช่วยให้คุณไม่พลาดข้อมูลสำคัญ
                            </div>

                            <hr class="my-4">

                            <!-- Save Button -->
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>
                                    บันทึกการตั้งค่า
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="settings-section" id="security-section" style="display: none;">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-shield-check me-2"></i>
                            ความปลอดภัย
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Change Password Form -->
                        <div class="mb-5">
                            <h6 class="text-secondary mb-3">
                                <i class="bi bi-key me-2"></i>
                                เปลี่ยนรหัสผ่าน
                            </h6>
                            
                            <form action="{{ route('profile.change-password') }}" method="POST" class="row g-3">
                                @csrf
                                
                                <div class="col-12">
                                    <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" name="current_password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label">รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                           id="new_password" name="new_password" required minlength="8">
                                    <div class="form-text">อย่างน้อย 8 ตัวอักษร</div>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="new_password_confirmation" class="form-label">ยืนยันรหัสผ่านใหม่ <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" 
                                           id="new_password_confirmation" name="new_password_confirmation" required minlength="8">
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-key me-1"></i>
                                        เปลี่ยนรหัสผ่าน
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Two-Factor Authentication -->
                        <div class="mb-5">
                            <h6 class="text-secondary mb-3">
                                <i class="bi bi-shield-lock me-2"></i>
                                Two-Factor Authentication (2FA)
                            </h6>
                            
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            @if(auth()->user()->hasTwoFactorEnabled())
                                                <i class="bi bi-shield-fill-check text-success" style="font-size: 2rem;"></i>
                                            @else
                                                <i class="bi bi-shield-exclamation text-warning" style="font-size: 2rem;"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2">
                                                @if(auth()->user()->hasTwoFactorEnabled())
                                                    <span class="badge bg-success me-2">เปิดใช้งาน</span>
                                                    Two-Factor Authentication Active
                                                @else
                                                    <span class="badge bg-warning me-2">ปิดใช้งาน</span>
                                                    Two-Factor Authentication Disabled
                                                @endif
                                            </h6>
                                            <p class="text-muted mb-3 small">
                                                @if(auth()->user()->hasTwoFactorEnabled())
                                                    Your account is protected with two-factor authentication. You'll need both your password and a verification code from your authenticator app to log in.
                                                @else
                                                    Add an extra layer of security to your account by enabling two-factor authentication. You'll need an authenticator app to generate verification codes.
                                                @endif
                                            </p>
                                            
                                            @if(auth()->user()->hasTwoFactorEnabled())
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar-check me-1"></i>
                                                        Enabled on: {{ auth()->user()->google2fa_confirmed_at->format('M j, Y') }}
                                                    </small>
                                                    @if(auth()->user()->hasRecoveryCodes())
                                                        <br>
                                                        <small class="text-success">
                                                            <i class="bi bi-key me-1"></i>
                                                            {{ count(auth()->user()->recovery_codes) }} recovery codes available
                                                        </small>
                                                    @else
                                                        <br>
                                                        <small class="text-warning">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                                            No recovery codes generated
                                                        </small>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            <a href="{{ route('2fa.setup') }}" class="btn btn-primary btn-sm">
                                                <i class="bi bi-gear me-1"></i>
                                                @if(auth()->user()->hasTwoFactorEnabled())
                                                    Manage 2FA Settings
                                                @else
                                                    Setup Two-Factor Authentication
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="mb-4">
                            <h6 class="text-secondary mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                ข้อมูลบัญชี
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title mb-1">เข้าสู่ระบบล่าสุด</h6>
                                            <p class="card-text text-muted small mb-0">
                                                {{ safe_date_format($user->last_login_at, 'd/m/Y H:i:s', 'ยังไม่เคยเข้าสู่ระบบ') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title mb-1">IP Address ล่าสุด</h6>
                                            <p class="card-text text-muted small mb-0">
                                                {{ $user->last_login_ip ?: 'ไม่ได้บันทึก' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title mb-1">สร้างบัญชีเมื่อ</h6>
                                            <p class="card-text text-muted small mb-0">
                                                {{ safe_date_format($user->created_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title mb-1">อัพเดทล่าสุด</h6>
                                            <p class="card-text text-muted small mb-0">
                                                {{ safe_date_format($user->updated_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Tips -->
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>เคล็ดลับความปลอดภัย:</strong>
                            <ul class="mb-0 mt-2">
                                <li>ใช้รหัสผ่านที่แข็งแกร่งและไม่เหมือนกับเว็บไซต์อื่น</li>
                                <li>เปลี่ยนรหัสผ่านเป็นระยะๆ</li>
                                <li>ไม่แชร์รหัสผ่านกับผู้อื่น</li>
                                <li>ออกจากระบบทุกครั้งหลังใช้งานเสร็จ</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Settings menu navigation
    const settingsLinks = document.querySelectorAll('[data-target]');
    const settingsSections = document.querySelectorAll('.settings-section');

    settingsLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            settingsLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Hide all sections
            settingsSections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Show target section
            const targetId = this.getAttribute('data-target') + '-section';
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.style.display = 'block';
            }
        });
    });

    // Password strength indicator
    const newPasswordInput = document.getElementById('new_password');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            // Remove existing strength indicators
            const existingIndicator = this.parentNode.querySelector('.password-strength');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            if (password.length > 0) {
                const indicator = document.createElement('div');
                indicator.className = 'password-strength mt-1';
                
                let strengthText = '';
                let strengthClass = '';
                
                switch(strength) {
                    case 0:
                    case 1:
                        strengthText = 'อ่อนมาก';
                        strengthClass = 'text-danger';
                        break;
                    case 2:
                        strengthText = 'อ่อน';
                        strengthClass = 'text-warning';
                        break;
                    case 3:
                        strengthText = 'ปานกลาง';
                        strengthClass = 'text-info';
                        break;
                    case 4:
                        strengthText = 'แข็งแกร่ง';
                        strengthClass = 'text-success';
                        break;
                }
                
                indicator.innerHTML = `<small class="${strengthClass}">ความแข็งแกร่ง: ${strengthText}</small>`;
                this.parentNode.appendChild(indicator);
            }
        });
    }

    function calculatePasswordStrength(password) {
        let strength = 0;
        
        // Length
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Character variety
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        return Math.min(strength, 4);
    }

    // Confirm password validation
    const confirmPasswordInput = document.getElementById('new_password_confirmation');
    if (confirmPasswordInput && newPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = newPasswordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    }

    // Theme preview
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    themeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Add visual preview logic here if needed
            console.log('Theme changed to:', this.value);
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}

.form-switch .form-check-input {
    width: 2em;
    margin-left: -2.5em;
    background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'><circle r='3' fill='rgba%2855, 63, 71, 0.25%29'/></svg>");
}

.form-switch .form-check-input:checked {
    background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'><circle r='3' fill='rgba%28255, 255, 255, 1.0%29'/></svg>");
}

.list-group-item-action:hover {
    background-color: #f8f9fc;
}

.list-group-item-action.active {
    background-color: #4e73df;
    color: white;
    border-color: #4e73df;
}

.card-header h6 {
    border-bottom: 2px solid #4e73df;
    display: inline-block;
    padding-bottom: 0.25rem;
}

.password-strength {
    transition: all 0.3s ease;
}
</style>
@endpush
