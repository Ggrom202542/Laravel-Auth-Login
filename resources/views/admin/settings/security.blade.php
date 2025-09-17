@extends('layouts.dashboard')

@section('title', 'การตั้งค่าความปลอดภัย')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('super-admin.settings.index') }}">การตั้งค่าระบบ</a></li>
            <li class="breadcrumb-item active">ความปลอดภัย</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">การตั้งค่าความปลอดภัย</h1>
            <p class="text-muted">จัดการการตั้งค่าด้านความปลอดภัยของระบบ</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Security Settings Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-shield-check text-primary me-2"></i>การตั้งค่าความปลอดภัย
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.settings.update-security') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Session & Login Settings -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">เซสชันและการเข้าสู่ระบบ</h6>
                        
                        <div class="mb-3">
                            <label for="session_lifetime" class="form-label">
                                ระยะเวลาเซสชัน (นาที)
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="ระยะเวลาที่ผู้ใช้สามารถใช้งานได้โดยไม่ต้องเข้าสู่ระบบใหม่"></i>
                            </label>
                            <input type="number" class="form-control" id="session_lifetime" name="session_lifetime" 
                                   value="{{ $settings['session_lifetime'] }}" min="5" max="1440" required>
                        </div>

                        <div class="mb-3">
                            <label for="max_login_attempts" class="form-label">
                                จำนวนการพยายามเข้าสู่ระบบสูงสุด
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="จำนวนครั้งที่อนุญาตให้ลองเข้าสู่ระบบก่อนจะถูกล็อค"></i>
                            </label>
                            <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" 
                                   value="{{ $settings['max_login_attempts'] }}" min="3" max="10" required>
                        </div>

                        <div class="mb-3">
                            <label for="lockout_duration" class="form-label">
                                ระยะเวลาล็อคบัญชี (นาที)
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="ระยะเวลาที่บัญชีจะถูกล็อคหลังจากพยายามเข้าสู่ระบบเกินกำหนด"></i>
                            </label>
                            <input type="number" class="form-control" id="lockout_duration" name="lockout_duration" 
                                   value="{{ $settings['lockout_duration'] }}" min="1" max="60" required>
                        </div>
                    </div>

                    <!-- Password Policy -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">นโยบายรหัสผ่าน</h6>
                        
                        <div class="mb-3">
                            <label for="password_min_length" class="form-label">
                                ความยาวรหัสผ่านขั้นต่ำ
                            </label>
                            <input type="number" class="form-control" id="password_min_length" name="password_min_length" 
                                   value="{{ $settings['password_min_length'] }}" min="6" max="20" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="password_require_uppercase" 
                                       name="password_require_uppercase" value="1" {{ $settings['password_require_uppercase'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="password_require_uppercase">
                                    ต้องมีตัวพิมพ์ใหญ่
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="password_require_lowercase" 
                                       name="password_require_lowercase" value="1" {{ $settings['password_require_lowercase'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="password_require_lowercase">
                                    ต้องมีตัวพิมพ์เล็ก
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="password_require_numbers" 
                                       name="password_require_numbers" value="1" {{ $settings['password_require_numbers'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="password_require_numbers">
                                    ต้องมีตัวเลข
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="password_require_symbols" 
                                       name="password_require_symbols" value="1" {{ $settings['password_require_symbols'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="password_require_symbols">
                                    ต้องมีสัญลักษณ์พิเศษ
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Security Settings -->
                <hr class="my-4">
                <h6 class="fw-bold mb-3">การตั้งค่าความปลอดภัยขั้นสูง</h6>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="two_factor_enabled" 
                                       name="two_factor_enabled" value="1" {{ $settings['two_factor_enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="two_factor_enabled">
                                    เปิดใช้งาน Two-Factor Authentication
                                </label>
                                <div class="form-text">การยืนยันตัวตนแบบ 2 ขั้นตอน</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="force_https" 
                                       name="force_https" value="1" {{ $settings['force_https'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="force_https">
                                    บังคับใช้ HTTPS
                                </label>
                                <div class="form-text">เปลี่ยนเส้นทางทั้งหมดไปยัง HTTPS</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ip_restriction_enabled" 
                                       name="ip_restriction_enabled" value="1" {{ $settings['ip_restriction_enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="ip_restriction_enabled">
                                    เปิดใช้งานการจำกัด IP
                                </label>
                                <div class="form-text">จำกัดการเข้าถึงระบบจาก IP ที่กำหนด</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('super-admin.settings.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i>กลับ
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-check me-1"></i>บันทึกการตั้งค่าความปลอดภัย
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Status Card -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-shield-fill-check text-success me-2"></i>สถานะความปลอดภัย
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <div class="mb-2">
                        <i class="bi bi-key-fill text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <h6>นโยบายรหัสผ่าน</h6>
                    <span class="badge bg-success">เปิดใช้งาน</span>
                </div>
                <div class="col-md-3 text-center">
                    <div class="mb-2">
                        <i class="bi bi-clock-fill text-info" style="font-size: 2rem;"></i>
                    </div>
                    <h6>การจำกัดเซสชัน</h6>
                    <span class="badge bg-success">{{ $settings['session_lifetime'] }} นาที</span>
                </div>
                <div class="col-md-3 text-center">
                    <div class="mb-2">
                        <i class="bi bi-lock-fill text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <h6>การล็อคบัญชี</h6>
                    <span class="badge bg-success">{{ $settings['max_login_attempts'] }} ครั้ง</span>
                </div>
                <div class="col-md-3 text-center">
                    <div class="mb-2">
                        <i class="bi bi-shield-lock-fill text-danger" style="font-size: 2rem;"></i>
                    </div>
                    <h6>2FA</h6>
                    <span class="badge {{ $settings['two_factor_enabled'] ? 'bg-success' : 'bg-secondary' }}">
                        {{ $settings['two_factor_enabled'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Password requirements preview
    const passwordInputs = ['password_min_length', 'password_require_uppercase', 'password_require_lowercase', 'password_require_numbers', 'password_require_symbols'];
    passwordInputs.forEach(function(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('change', updatePasswordPreview);
        }
    });

    function updatePasswordPreview() {
        // This could show a live preview of password requirements
        console.log('Password requirements updated');
    }
});
</script>
@endpush