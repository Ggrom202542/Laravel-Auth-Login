@extends('layouts.dashboard')

@section('title', 'การตั้งค่าเซสชัน - ผู้ดูแลระบบสูงสุด')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-gear me-2"></i>การตั้งค่าเซสชัน
                    </h4>
                    <a href="{{ route('super-admin.sessions.dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>กลับไปยังแดชบอร์ด
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('super-admin.sessions.update-settings') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">การกำหนดค่าเซสชัน</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="session_lifetime" class="form-label">ระยะเวลาเซสชัน (นาที)</label>
                                            <input type="number" class="form-control" id="session_lifetime" 
                                                   name="session_lifetime" value="{{ $config['session_lifetime'] }}" 
                                                   min="5" max="1440" required>
                                            <small class="text-muted">ระยะเวลาที่เซสชันจะคงอยู่</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="max_concurrent_sessions" class="form-label">เซสชันพร้อมกันสูงสุด</label>
                                            <input type="number" class="form-control" id="max_concurrent_sessions" 
                                                   name="max_concurrent_sessions" value="{{ $config['max_concurrent_sessions'] }}" 
                                                   min="1" max="50" required>
                                            <small class="text-muted">จำนวนเซสชันสูงสุดต่อผู้ใช้</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="device_trust_period" class="form-label">ระยะเวลาเชื่อถืออุปกรณ์ (วัน)</label>
                                            <input type="number" class="form-control" id="device_trust_period" 
                                                   name="device_trust_period" value="{{ $config['device_trust_period'] }}" 
                                                   min="1" max="365" required>
                                            <small class="text-muted">ระยะเวลาที่อุปกรณ์จะได้รับความเชื่อถือ</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">ฟีเจอร์ความปลอดภัย</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="auto_cleanup_enabled" 
                                                   name="auto_cleanup_enabled" value="1" 
                                                   {{ $config['auto_cleanup_enabled'] ? 'checked' : '' }}>
                                            <label class="form-check-label" for="auto_cleanup_enabled">
                                                ล้างเซสชันหมดอายุอัตโนมัติ
                                            </label>
                                            <small class="d-block text-muted">ลบเซสชันเก่าอัตโนมัติ</small>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="suspicious_activity_monitoring" 
                                                   name="suspicious_activity_monitoring" value="1" 
                                                   {{ $config['suspicious_activity_monitoring'] ? 'checked' : '' }}>
                                            <label class="form-check-label" for="suspicious_activity_monitoring">
                                                ตรวจสอบกิจกรรมที่น่าสงสัย
                                            </label>
                                            <small class="d-block text-muted">ตรวจสอบรูปแบบการเข้าสู่ระบบที่ผิดปกติ</small>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="geo_location_tracking" 
                                                   name="geo_location_tracking" value="1" 
                                                   {{ $config['geo_location_tracking'] ? 'checked' : '' }}>
                                            <label class="form-check-label" for="geo_location_tracking">
                                                ติดตามตำแหน่งทางภูมิศาสตร์
                                            </label>
                                            <small class="d-block text-muted">ติดตามตำแหน่งการเข้าสู่ระบบของผู้ใช้</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">สถานะระบบปัจจุบัน</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h5 class="text-primary">{{ $stats['active_sessions'] ?? 0 }}</h5>
                                                    <small class="text-muted">เซสชันที่ใช้งาน</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h5 class="text-success">{{ $stats['online_users'] ?? 0 }}</h5>
                                                    <small class="text-muted">ผู้ใช้ออนไลน์</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h5 class="text-info">{{ $stats['trusted_devices'] ?? 0 }}</h5>
                                                    <small class="text-muted">อุปกรณ์ที่เชื่อถือได้</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h5 class="text-warning">{{ $stats['suspicious_sessions'] ?? 0 }}</h5>
                                                    <small class="text-muted">เซสชันที่น่าสงสัย</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>บันทึกการตั้งค่า
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-switch .form-check-input {
    width: 2.5rem;
    height: 1.25rem;
}

.form-check-label {
    font-weight: 500;
}
</style>
@endpush
