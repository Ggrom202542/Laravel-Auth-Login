@extends('layouts.dashboard')

@section('title', 'การตั้งค่าทั่วไป')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('super-admin.settings.index') }}">การตั้งค่าระบบ</a></li>
            <li class="breadcrumb-item active">การตั้งค่าทั่วไป</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">การตั้งค่าทั่วไป</h1>
            <p class="text-muted">จัดการการตั้งค่าพื้นฐานของระบบ</p>
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

    <!-- Settings Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-sliders text-primary me-2"></i>การตั้งค่าทั่วไป
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.settings.update-general') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Application Settings -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">ข้อมูลแอปพลิเคชัน</h6>
                        
                        <div class="mb-3">
                            <label for="app_name" class="form-label">ชื่อแอปพลิเคชัน</label>
                            <input type="text" class="form-control" id="app_name" name="app_name" 
                                   value="{{ $settings['app_name'] }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="app_description" class="form-label">คำอธิบายแอปพลิเคชัน</label>
                            <textarea class="form-control" id="app_description" name="app_description" 
                                      rows="3" placeholder="คำอธิบายเกี่ยวกับแอปพลิเคชัน">{{ $settings['app_description'] }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="app_timezone" class="form-label">เขตเวลา</label>
                            <select class="form-select" id="app_timezone" name="app_timezone" required>
                                <option value="Asia/Bangkok" {{ $settings['app_timezone'] == 'Asia/Bangkok' ? 'selected' : '' }}>Asia/Bangkok (UTC+7)</option>
                                <option value="UTC" {{ $settings['app_timezone'] == 'UTC' ? 'selected' : '' }}>UTC (UTC+0)</option>
                                <option value="Asia/Tokyo" {{ $settings['app_timezone'] == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (UTC+9)</option>
                                <option value="America/New_York" {{ $settings['app_timezone'] == 'America/New_York' ? 'selected' : '' }}>America/New_York (UTC-5)</option>
                                <option value="Europe/London" {{ $settings['app_timezone'] == 'Europe/London' ? 'selected' : '' }}>Europe/London (UTC+0)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="app_locale" class="form-label">ภาษา</label>
                            <select class="form-select" id="app_locale" name="app_locale" required>
                                <option value="th" {{ $settings['app_locale'] == 'th' ? 'selected' : '' }}>ไทย (th)</option>
                                <option value="en" {{ $settings['app_locale'] == 'en' ? 'selected' : '' }}>English (en)</option>
                            </select>
                        </div>
                    </div>

                    <!-- System Settings -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">การตั้งค่าระบบ</h6>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" 
                                       name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="maintenance_mode">
                                    โหมดบำรุงรักษา
                                </label>
                                <div class="form-text">เมื่อเปิดใช้งาน จะแสดงหน้าบำรุงรักษาให้ผู้ใช้ทั่วไป</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="registration_enabled" 
                                       name="registration_enabled" value="1" {{ $settings['registration_enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="registration_enabled">
                                    เปิดให้ลงทะเบียน
                                </label>
                                <div class="form-text">อนุญาตให้ผู้ใช้ใหม่สามารถลงทะเบียนได้</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_verification_required" 
                                       name="email_verification_required" value="1" {{ $settings['email_verification_required'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_verification_required">
                                    ต้องยืนยันอีเมล
                                </label>
                                <div class="form-text">ผู้ใช้ต้องยืนยันอีเมลก่อนใช้งาน</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="admin_approval_required" 
                                       name="admin_approval_required" value="1" {{ $settings['admin_approval_required'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="admin_approval_required">
                                    ต้องได้รับการอนุมัติ
                                </label>
                                <div class="form-text">ผู้ใช้ใหม่ต้องได้รับการอนุมัติจากผู้ดูแลระบบก่อน</div>
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
                        <i class="bi bi-check-lg me-1"></i>บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Information Card -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-info-circle text-info me-2"></i>ข้อมูลเพิ่มเติม
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">โหมดบำรุงรักษา</h6>
                    <p class="text-muted small">เมื่อเปิดใช้งาน ผู้ใช้ทั่วไปจะไม่สามารถเข้าถึงระบบได้ ยกเว้น Super Admin</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">การอนุมัติผู้ใช้</h6>
                    <p class="text-muted small">หากเปิดใช้งาน ผู้ใช้ใหม่จะต้องรอการอนุมัติจากผู้ดูแลระบบก่อนสามารถใช้งานได้</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Show confirmation when enabling maintenance mode
    const maintenanceToggle = document.getElementById('maintenance_mode');
    maintenanceToggle.addEventListener('change', function() {
        if (this.checked) {
            if (!confirm('คุณแน่ใจหรือไม่ที่จะเปิดโหมดบำรุงรักษา? ผู้ใช้ทั่วไปจะไม่สามารถเข้าถึงระบบได้')) {
                this.checked = false;
            }
        }
    });
});
</script>
@endpush