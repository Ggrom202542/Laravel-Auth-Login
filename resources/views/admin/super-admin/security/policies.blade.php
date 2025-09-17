@extends('layouts.dashboard')

@section('title', 'นโยบายความปลอดภัย - Super Admin')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-dark fw-bold mb-1">
                        <i class="bi bi-shield-fill-check text-primary me-2"></i>
                        นโยบายความปลอดภัย
                    </h2>
                    <p class="text-muted mb-0">จัดการและกำหนดนโยบายความปลอดภัยระดับระบบ</p>
                </div>
                <div>
                    <a href="{{ route('super-admin.security.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i> กลับ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Policies Configuration -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-gear text-primary me-2"></i>
                        การตั้งค่านโยบายความปลอดภัย
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('super-admin.security.policies.update') }}" method="POST">
                        @csrf
                        
                        <div class="row g-4">
                            <!-- Session Configuration -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3">
                                            <i class="bi bi-clock-history text-info me-2"></i>
                                            การจัดการเซสชัน
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <label for="session_timeout" class="form-label fw-bold">
                                                ระยะเวลา Session Timeout (นาที)
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="session_timeout" 
                                                   name="session_timeout" 
                                                   value="{{ $policies['session_timeout'] }}" 
                                                   min="5" 
                                                   max="1440">
                                            <small class="text-muted">ค่าปัจจุบัน: {{ $policies['session_timeout'] }} นาที</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Login Security -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3">
                                            <i class="bi bi-shield-lock text-warning me-2"></i>
                                            ความปลอดภัยการเข้าสู่ระบบ
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <label for="max_login_attempts" class="form-label fw-bold">
                                                จำนวนครั้งที่พยายามเข้าสู่ระบบสูงสุด
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="max_login_attempts" 
                                                   name="max_login_attempts" 
                                                   value="{{ $policies['max_login_attempts'] }}" 
                                                   min="1" 
                                                   max="20">
                                            <small class="text-muted">ค่าปัจจุบัน: {{ $policies['max_login_attempts'] }} ครั้ง</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="lockout_duration" class="form-label fw-bold">
                                                ระยะเวลาล็อกบัญชี (วินาที)
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="lockout_duration" 
                                                   name="lockout_duration" 
                                                   value="{{ $policies['lockout_duration'] }}" 
                                                   min="60" 
                                                   max="3600">
                                            <small class="text-muted">ค่าปัจจุบัน: {{ round($policies['lockout_duration']/60) }} นาที</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Security Features -->
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3">
                                            <i class="bi bi-shield-check text-success me-2"></i>
                                            ฟีเจอร์ความปลอดภัยขั้นสูง
                                        </h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="ip_restriction_enabled" 
                                                           name="ip_restriction_enabled" 
                                                           value="1"
                                                           {{ $policies['ip_restriction_enabled'] ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="ip_restriction_enabled">
                                                        เปิดใช้งานการจำกัด IP Address
                                                    </label>
                                                    <div class="text-muted small">
                                                        จำกัดการเข้าถึงจาก IP Address ที่ระบุเท่านั้น
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="suspicious_login_detection" 
                                                           name="suspicious_login_detection" 
                                                           value="1"
                                                           {{ $policies['suspicious_login_detection'] ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="suspicious_login_detection">
                                                        ตรวจจับการเข้าสู่ระบบที่น่าสงสัย
                                                    </label>
                                                    <div class="text-muted small">
                                                        ตรวจสอบและแจ้งเตือนการเข้าสู่ระบบที่ผิดปกติ
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('super-admin.security.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-lg me-1"></i> ยกเลิก
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i> บันทึกการตั้งค่า
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Policies Summary -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-info-circle text-info me-2"></i>
                        สรุปนโยบายปัจจุบัน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-primary mb-1">{{ $policies['session_timeout'] }}</h4>
                                <small class="text-muted">นาที - Session Timeout</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-warning mb-1">{{ $policies['max_login_attempts'] }}</h4>
                                <small class="text-muted">ครั้ง - ความพยายามสูงสุด</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-danger mb-1">{{ round($policies['lockout_duration']/60) }}</h4>
                                <small class="text-muted">นาที - ระยะเวลาล็อก</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-success mb-1">
                                    <i class="bi bi-{{ $policies['ip_restriction_enabled'] ? 'check-circle-fill' : 'x-circle-fill' }}"></i>
                                </h4>
                                <small class="text-muted">การจำกัด IP</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    .card {
        transition: transform 0.2s ease-in-out;
    }
    .card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const sessionTimeout = document.getElementById('session_timeout').value;
            const maxAttempts = document.getElementById('max_login_attempts').value;
            const lockoutDuration = document.getElementById('lockout_duration').value;

            if (sessionTimeout < 5 || sessionTimeout > 1440) {
                e.preventDefault();
                alert('Session timeout ต้องอยู่ระหว่าง 5-1440 นาที');
                return;
            }

            if (maxAttempts < 1 || maxAttempts > 20) {
                e.preventDefault();
                alert('จำนวนความพยายามต้องอยู่ระหว่าง 1-20 ครั้ง');
                return;
            }

            if (lockoutDuration < 60 || lockoutDuration > 3600) {
                e.preventDefault();
                alert('ระยะเวลาล็อกต้องอยู่ระหว่าง 60-3600 วินาที');
                return;
            }
        });
    }

    // Real-time calculation for lockout duration
    const lockoutInput = document.getElementById('lockout_duration');
    if (lockoutInput) {
        lockoutInput.addEventListener('input', function() {
            const seconds = this.value;
            const minutes = Math.round(seconds / 60);
            const small = this.parentElement.querySelector('small');
            small.textContent = `ค่าปัจจุบัน: ${minutes} นาที`;
        });
    }
});
</script>
@endpush
@endsection