@extends('layouts.app')

@section('title', 'เปลี่ยนรหัสผ่าน')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <!-- Header Card -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-gradient-primary text-white border-0 py-4">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-shield-lock-fill" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="mb-0 fw-bold">เปลี่ยนรหัสผ่าน</h3>
                        <p class="mb-0 opacity-75">อัปเดตรหัสผ่านของคุณเพื่อความปลอดภัย</p>
                    </div>
                </div>

                <div class="card-body p-5">
                    @if(session('error'))
                        <div class="alert alert-danger border-0 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.2rem;"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success border-0 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-3" style="font-size: 1.2rem;"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle-fill me-3 mt-1" style="font-size: 1.2rem;"></i>
                                <div>
                                    <strong>เกิดข้อผิดพลาด:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Password Expiration Warning -->
                    @if(isset($passwordExpired) && $passwordExpired)
                        <div class="alert alert-danger border-0 shadow-sm border-start border-danger border-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-exclamation-octagon-fill text-danger" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading mb-1">รหัสผ่านหมดอายุแล้ว!</h6>
                                    <p class="mb-0">คุณจำเป็นต้องเปลี่ยนรหัสผ่านใหม่เพื่อใช้งานระบบต่อไป</p>
                                </div>
                            </div>
                        </div>
                    @elseif(isset($daysLeft) && $daysLeft <= 7)
                        <div class="alert alert-warning border-0 shadow-sm border-start border-warning border-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-clock-fill text-warning" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading mb-1">รหัสผ่านใกล้หมดอายุ</h6>
                                    <p class="mb-0">รหัสผ่านจะหมดอายุใน <strong>{{ $daysLeft }} วัน</strong> กรุณาเปลี่ยนรหัสผ่านใหม่เพื่อความปลอดภัย</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" id="changePasswordForm" class="mt-4">
                        @csrf

                        <!-- Current Password -->
                        <div class="mb-4">
                            <label for="current_password" class="form-label fw-semibold text-dark">
                                <i class="bi bi-lock-fill me-2 text-primary"></i>
                                รหัสผ่านปัจจุบัน
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-key-fill text-muted"></i>
                                </span>
                                <input id="current_password" 
                                       type="password" 
                                       class="form-control border-start-0 @error('current_password') is-invalid @enderror" 
                                       name="current_password" 
                                       placeholder="กรอกรหัสผ่านปัจจุบัน"
                                       required>
                                <button class="btn btn-outline-secondary border-start-0" type="button" id="toggleCurrentPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold text-dark">
                                <i class="bi bi-shield-lock-fill me-2 text-success"></i>
                                รหัสผ่านใหม่
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-shield-plus text-muted"></i>
                                </span>
                                <input id="password" 
                                       type="password" 
                                       class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" 
                                       name="password" 
                                       placeholder="กรอกรหัสผ่านใหม่"
                                       required>
                                <button class="btn btn-outline-secondary border-start-0" type="button" id="toggleNewPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password Strength Indicator & Requirements -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <!-- Strength Indicator -->
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <h6 class="mb-0 text-dark fw-bold">
                                                <i class="bi bi-shield-check me-2 text-primary"></i>
                                                ความแข็งแรงของรหัสผ่าน
                                            </h6>
                                            <span class="badge bg-secondary px-3 py-2" id="strengthBadge">ยังไม่ได้กรอก</span>
                                        </div>
                                        
                                        <!-- Modern Progress Bar -->
                                        <div class="strength-progress-container mb-3">
                                            <div class="strength-progress-bar">
                                                <div class="strength-progress-fill" id="strengthFill"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="strength-description text-muted" id="strengthText">
                                            <i class="bi bi-info-circle me-2"></i>
                                            กรุณาใส่รหัสผ่านเพื่อดูความแข็งแรง
                                        </div>
                                    </div>
                                </div>

                                <!-- Password Requirements -->
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-gradient-light border-0 py-3">
                                        <h6 class="mb-0 text-dark fw-bold">
                                            <i class="bi bi-list-check me-2 text-info"></i>
                                            ข้อกำหนดรหัสผ่าน
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <div class="col-lg-6">
                                                <div class="requirement-card" id="lengthReq">
                                                    <div class="requirement-icon-container">
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                    </div>
                                                    <div class="requirement-content">
                                                        <div class="requirement-title">ความยาวอย่างน้อย 8 ตัวอักษร</div>
                                                        <div class="requirement-subtitle">จำเป็นสำหรับความปลอดภัย</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="requirement-card" id="uppercaseReq">
                                                    <div class="requirement-icon-container">
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                    </div>
                                                    <div class="requirement-content">
                                                        <div class="requirement-title">อักษรพิมพ์ใหญ่ (A-Z)</div>
                                                        <div class="requirement-subtitle">เพิ่มความซับซ้อน</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="requirement-card" id="lowercaseReq">
                                                    <div class="requirement-icon-container">
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                    </div>
                                                    <div class="requirement-content">
                                                        <div class="requirement-title">อักษรพิมพ์เล็ก (a-z)</div>
                                                        <div class="requirement-subtitle">รูปแบบมาตรฐาน</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="requirement-card" id="numberReq">
                                                    <div class="requirement-icon-container">
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                    </div>
                                                    <div class="requirement-content">
                                                        <div class="requirement-title">ตัวเลข (0-9)</div>
                                                        <div class="requirement-subtitle">เพิ่มความหลากหลาย</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="requirement-card" id="specialReq">
                                                    <div class="requirement-icon-container">
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                    </div>
                                                    <div class="requirement-content">
                                                        <div class="requirement-title">อักขระพิเศษ (!@#$%)</div>
                                                        <div class="requirement-subtitle">ความปลอดภัยสูงสุด</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="requirement-card" id="historyReq">
                                                    <div class="requirement-icon-container">
                                                        <i class="bi bi-clock-history text-warning"></i>
                                                    </div>
                                                    <div class="requirement-content">
                                                        <div class="requirement-title">ไม่เคยใช้ในอดีต</div>
                                                        <div class="requirement-subtitle">ป้องกันการใช้ซ้ำ</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-5">
                            <label for="password_confirmation" class="form-label fw-semibold text-dark">
                                <i class="bi bi-check2-square me-2 text-info"></i>
                                ยืนยันรหัสผ่านใหม่
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-shield-check text-muted"></i>
                                </span>
                                <input id="password_confirmation" 
                                       type="password" 
                                       class="form-control border-start-0 border-end-0" 
                                       name="password_confirmation" 
                                       placeholder="กรอกรหัสผ่านใหม่อีกครั้ง"
                                       required>
                                <button class="btn btn-outline-secondary border-start-0" type="button" id="toggleConfirmPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatchFeedback" class="mt-2"></div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="submitBtn" disabled>
                                <i class="bi bi-shield-check me-2"></i>
                                เปลี่ยนรหัสผ่าน
                            </button>
                            
                            @if(!isset($passwordExpired) || !$passwordExpired)
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg w-100">
                                            <i class="bi bi-arrow-left me-2"></i>
                                            ยกเลิก
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('password.status') }}" class="btn btn-outline-info btn-lg w-100">
                                            <i class="bi bi-graph-up me-2"></i>
                                            ดูสถานะรหัสผ่าน
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Security Tips -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="card-title text-primary mb-3">
                        <i class="bi bi-lightbulb me-2"></i>
                        เคล็ดลับความปลอดภัย
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex mb-3">
                                <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                                <div>
                                    <strong>ใช้รหัสผ่านที่แข็งแรง</strong>
                                    <p class="small text-muted mb-0">ควรมีความยาวอย่างน้อย 12 ตัวอักษรและประกอบด้วยตัวอักษร ตัวเลข และสัญลักษณ์</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex mb-3">
                                <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                                <div>
                                    <strong>อย่าใช้ข้อมูลส่วนตัว</strong>
                                    <p class="small text-muted mb-0">หลีกเลี่ยงการใช้ชื่อ วันเกิด หรือข้อมูลที่เดาได้ง่าย</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex mb-3">
                                <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                                <div>
                                    <strong>เปลี่ยนรหัสผ่านเป็นประจำ</strong>
                                    <p class="small text-muted mb-0">แนะนำให้เปลี่ยนรหัสผ่านทุก 90 วัน</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex mb-3">
                                <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                                <div>
                                    <strong>ใช้รหัสผ่านเฉพาะ</strong>
                                    <p class="small text-muted mb-0">อย่าใช้รหัสผ่านเดียวกันในหลายๆ เว็บไซต์</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Styles -->
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.password-strength-container .strength-bar {
    height: 8px;
    background-color: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.password-strength-container .strength-fill {
    height: 100%;
    border-radius: 10px;
    transition: all 0.3s ease;
    background: linear-gradient(90deg, #dc3545, #ffc107, #28a745);
}

/* Modern Strength Progress Bar */
.strength-progress-container {
    position: relative;
}

.strength-progress-bar {
    height: 12px;
    background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 25px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
}

.strength-progress-fill {
    height: 100%;
    width: 0%;
    border-radius: 25px;
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(90deg, #dc3545, #fd7e14);
    position: relative;
    overflow: hidden;
}

.strength-progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Modern Requirement Cards */
.requirement-card {
    display: flex;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 2px solid #e9ecef;
    border-radius: 15px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    min-height: 80px;
}

.requirement-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
    transition: left 0.6s;
}

.requirement-card:hover::before {
    left: 100%;
}

.requirement-card.met {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-color: #28a745;
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
    transform: translateY(-2px);
}

.requirement-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.requirement-icon-container {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.requirement-card.met .requirement-icon-container {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3);
    animation: successPulse 0.6s ease-out;
}

.requirement-icon-container i {
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.requirement-card.met .requirement-icon-container i {
    color: white !important;
    transform: scale(1.1);
}

.requirement-content {
    flex: 1;
}

.requirement-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
    transition: color 0.3s ease;
}

.requirement-card.met .requirement-title {
    color: #155724;
}

.requirement-subtitle {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 400;
    transition: color 0.3s ease;
}

.requirement-card.met .requirement-subtitle {
    color: #0f4419;
}

.strength-description {
    font-size: 0.9rem;
    font-weight: 500;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    border-left: 4px solid #6c757d;
    transition: all 0.3s ease;
}

.bg-gradient-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

@keyframes successPulse {
    0% { 
        transform: scale(1); 
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    50% { 
        transform: scale(1.1); 
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
    }
    100% { 
        transform: scale(1); 
        box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3);
    }
}

/* Progress Fill Strength Colors */
.strength-very-weak { 
    background: linear-gradient(90deg, #dc3545, #fd7e14) !important;
}
.strength-weak { 
    background: linear-gradient(90deg, #fd7e14, #ffc107) !important;
}
.strength-medium { 
    background: linear-gradient(90deg, #ffc107, #20c997) !important;
}
.strength-strong { 
    background: linear-gradient(90deg, #20c997, #28a745) !important;
}
.strength-very-strong { 
    background: linear-gradient(90deg, #28a745, #198754) !important;
}

.requirement {
    transition: all 0.3s ease;
}

.requirement.valid i {
    color: #28a745 !important;
}

.requirement.valid {
    color: #28a745;
}

.input-group-text {
    border: 1px solid #ced4da;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

#strengthBadge {
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border-radius: 20px;
}

/* ซ่อน password strength meter และ requirements ทั้งหมดที่สร้างโดย JavaScript เดิม */
.password-strength-container:not(.mt-4),
.password-strength-meter:not(.strength-progress-container .strength-progress-bar),
.password-requirements,
.requirements-list {
    display: none !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ลบ Password Strength Meter เดิมออกทั้งหมด - ใช้แค่ UI ใหม่
    
    // Password visibility toggles with Bootstrap Icons
    const toggleButtons = [
        { button: 'toggleCurrentPassword', input: 'current_password' },
        { button: 'toggleNewPassword', input: 'password' },
        { button: 'toggleConfirmPassword', input: 'password_confirmation' }
    ];

    toggleButtons.forEach(({ button, input }) => {
        document.getElementById(button).addEventListener('click', function() {
            const passwordInput = document.getElementById(input);
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // Enhanced password confirmation validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const submitBtn = document.getElementById('submitBtn');
    const passwordMatchFeedback = document.getElementById('passwordMatchFeedback');
    const strengthBadge = document.getElementById('strengthBadge');

    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword === '') {
            passwordMatchFeedback.innerHTML = '';
            return false;
        }
        
        if (password === confirmPassword) {
            passwordMatchFeedback.innerHTML = `
                <div class="alert alert-success border-0 py-2">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <small>รหัสผ่านตรงกัน</small>
                </div>`;
            return true;
        } else {
            passwordMatchFeedback.innerHTML = `
                <div class="alert alert-danger border-0 py-2">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <small>รหัสผ่านไม่ตรงกัน</small>
                </div>`;
            return false;
        }
    }

    function updateSubmitButton() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const currentPassword = document.getElementById('current_password').value;
        
        // ตรวจสอบความแข็งแรงของรหัสผ่านแบบง่ายๆ
        const requirements = [
            password.length >= 8,
            /[A-Z]/.test(password),
            /[a-z]/.test(password),
            /[0-9]/.test(password),
            /[!@#$%^&*(),.?":{}|<>]/.test(password)
        ];
        const passwordValid = requirements.filter(req => req).length >= 4; // อย่างน้อย 4 ข้อ
        const passwordsMatch = password === confirmPassword && confirmPassword !== '';
        
        const allValid = passwordValid && passwordsMatch && currentPassword.length > 0;
        
        if (allValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-primary');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-secondary');
        }
    }

    // Modern requirement validation with enhanced UI
    function updateRequirements() {
        const password = passwordInput.value;
        const requirements = [
            { id: 'lengthReq', test: password.length >= 8 },
            { id: 'uppercaseReq', test: /[A-Z]/.test(password) },
            { id: 'lowercaseReq', test: /[a-z]/.test(password) },
            { id: 'numberReq', test: /[0-9]/.test(password) },
            { id: 'specialReq', test: /[!@#$%^&*(),.?":{}|<>]/.test(password) },
            { id: 'historyReq', test: true } // Default to true, will be validated on server
        ];

        requirements.forEach(req => {
            const card = document.getElementById(req.id);
            if (!card) return;
            
            const icon = card.querySelector('.requirement-icon-container i');
            if (!icon) return;
            
            if (req.test) {
                card.classList.add('met');
                icon.classList.remove('bi-x-circle', 'text-danger', 'bi-clock-history', 'text-warning');
                icon.classList.add('bi-check-circle-fill', 'text-white');
            } else {
                card.classList.remove('met');
                icon.classList.remove('bi-check-circle-fill', 'text-white');
                if (req.id === 'historyReq') {
                    icon.classList.add('bi-clock-history', 'text-warning');
                } else {
                    icon.classList.add('bi-x-circle', 'text-danger');
                }
            }
        });

        // Update modern progress bar
        const basicRequirements = requirements.slice(0, 5);
        const passedCount = basicRequirements.filter(req => req.test).length;
        const strengthText = document.getElementById('strengthText');
        const strengthFill = document.getElementById('strengthFill');
        
        let strengthPercentage = (passedCount / basicRequirements.length) * 100;
        
        // Update progress bar with smooth animation
        if (strengthFill) {
            strengthFill.className = 'strength-progress-fill';
            
            setTimeout(() => {
                if (strengthPercentage === 0) {
                    strengthFill.style.width = '0%';
                } else if (strengthPercentage <= 20) {
                    strengthFill.style.width = '20%';
                    strengthFill.classList.add('strength-very-weak');
                } else if (strengthPercentage <= 40) {
                    strengthFill.style.width = '40%';
                    strengthFill.classList.add('strength-weak');
                } else if (strengthPercentage <= 60) {
                    strengthFill.style.width = '60%';
                    strengthFill.classList.add('strength-medium');
                } else if (strengthPercentage <= 80) {
                    strengthFill.style.width = '80%';
                    strengthFill.classList.add('strength-strong');
                } else {
                    strengthFill.style.width = '100%';
                    strengthFill.classList.add('strength-very-strong');
                }
            }, 100);
        }
        
        // Update strength badge and description
        if (password.length === 0) {
            strengthBadge.textContent = 'ยังไม่ได้กรอก';
            strengthBadge.className = 'badge bg-secondary px-3 py-2';
            if (strengthText) {
                strengthText.innerHTML = '<i class="bi bi-info-circle me-2"></i>กรุณาใส่รหัสผ่านเพื่อดูความแข็งแรง';
                strengthText.style.borderLeftColor = '#6c757d';
            }
        } else if (passedCount < 2) {
            strengthBadge.textContent = 'อ่อนแอมาก';
            strengthBadge.className = 'badge bg-danger px-3 py-2';
            if (strengthText) {
                strengthText.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>รหัสผ่านนี้ไม่ปลอดภัย ควรปรับปรุงทันที';
                strengthText.style.borderLeftColor = '#dc3545';
            }
        } else if (passedCount < 4) {
            strengthBadge.textContent = 'ปานกลาง';
            strengthBadge.className = 'badge bg-warning px-3 py-2';
            if (strengthText) {
                strengthText.innerHTML = '<i class="bi bi-shield-exclamation me-2"></i>รหัสผ่านใช้ได้ แต่ควรเพิ่มความซับซ้อน';
                strengthText.style.borderLeftColor = '#ffc107';
            }
        } else if (passedCount < 5) {
            strengthBadge.textContent = 'ดี';
            strengthBadge.className = 'badge bg-info px-3 py-2';
            if (strengthText) {
                strengthText.innerHTML = '<i class="bi bi-shield-check me-2"></i>รหัสผ่านมีความปลอดภัยดี แนะนำให้ใช้';
                strengthText.style.borderLeftColor = '#0dcaf0';
            }
        } else {
            strengthBadge.textContent = 'แข็งแรงมาก';
            strengthBadge.className = 'badge bg-success px-3 py-2';
            if (strengthText) {
                strengthText.innerHTML = '<i class="bi bi-shield-fill-check me-2"></i>รหัสผ่านมีความปลอดภัยสูงมาก ยอดเยี่ยม!';
                strengthText.style.borderLeftColor = '#28a745';
            }
        }
    }

    // Event listeners
    passwordInput.addEventListener('input', function() {
        updateRequirements();
        updateSubmitButton();
    });
    
    confirmPasswordInput.addEventListener('input', updateSubmitButton);
    document.getElementById('current_password').addEventListener('input', updateSubmitButton);

    // Custom event listener แทน password strength validation
    passwordInput.addEventListener('passwordValidationUpdate', updateSubmitButton);

    // Form submission enhancement
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm me-2"></i>กำลังเปลี่ยนรหัสผ่าน...';
        submitBtn.disabled = true;
    });
});
</script>
@endsection
