@extends('layouts.dashboard')

@section('title', 'จัดการอุปกรณ์')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-phone text-primary me-2"></i>
                        อุปกรณ์ของฉัน
                    </h1>
                    <p class="text-muted mb-0">จัดการอุปกรณ์ที่เชื่อถือได้และการตั้งค่าความปลอดภัย</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshDevices()">
                        <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                    </button>
                </div>
            </div>

            <!-- Device Security Overview -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-primary-subtle border-primary-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-devices text-primary fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $userDevices->count() }}</h3>
                            <p class="text-muted mb-0">อุปกรณ์ที่ลงทะเบียน</p>
                            <small class="text-primary">ทั้งหมด</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-success-subtle border-success-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-shield-check text-success fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $userDevices->where('is_trusted', true)->count() }}</h3>
                            <p class="text-muted mb-0">อุปกรณ์ที่เชื่อถือได้</p>
                            <small class="text-success">เชื่อถือได้</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-clock text-info fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">
                                {{ $userDevices->where('last_seen_at', '>', now()->subDays(30))->count() }}
                            </h3>
                            <p class="text-muted mb-0">อุปกรณ์ที่ใช้งาน</p>
                            <small class="text-info">30 วันที่ผ่านมา</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Device Alert -->
            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle me-3 fs-4"></i>
                <div>
                    <h6 class="alert-heading mb-1">ข้อมูลอุปกรณ์ปัจจุบัน</h6>
                    <p class="mb-0">
                        คุณกำลังใช้: <strong id="currentDevice">กำลังโหลด...</strong><br>
                        <small class="text-muted">
                            IP: <code id="currentIp">{{ request()->ip() }}</code> • 
                            ใช้งานล่าสุด: <span id="currentTime">{{ now()->format('d M Y H:i:s') }}</span>
                        </small>
                    </p>
                </div>
            </div>

            <!-- Device Management Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-gear text-primary me-2"></i>
                        การดำเนินการด่วน
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-success" onclick="trustCurrentDevice()">
                                    <i class="bi bi-shield-check me-2"></i>
                                    เชื่อถืออุปกรณ์นี้
                                </button>
                                <small class="text-muted mt-1 text-center">
                                    ข้ามการตรวจสอบตัวตนในอุปกรณ์นี้ในการเข้าสู่ระบบครั้งต่อไป
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-warning" onclick="logoutAllDevices()">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    ออกจากระบบทุกอุปกรณ์
                                </button>
                                <small class="text-muted mt-1 text-center">
                                    ออกจากระบบจากอุปกรณ์ทั้งหมดยกเว้นอุปกรณ์นี้
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Devices List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-list text-primary me-2"></i>
                            อุปกรณ์ของฉัน
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showAllDevices" onchange="toggleDeviceView()">
                            <label class="form-check-label" for="showAllDevices">
                                <small>แสดงอุปกรณ์ที่ไม่ได้ใช้งาน</small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($userDevices->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($userDevices as $device)
                            <div class="list-group-item border-0 py-4 device-item {{ $device->last_seen_at && $device->last_seen_at->lt(now()->subDays(30)) ? 'inactive-device' : '' }}" 
                                 style="{{ $device->last_seen_at && $device->last_seen_at->lt(now()->subDays(30)) ? 'display: none;' : '' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center">
                                            <!-- Device Icon -->
                                            <div class="avatar-lg {{ $device->is_trusted ? 'bg-success-subtle' : 'bg-warning-subtle' }} rounded-circle d-flex align-items-center justify-content-center me-4">
                                                @if($device->device_type === 'mobile')
                                                    <i class="bi bi-phone {{ $device->is_trusted ? 'text-success' : 'text-warning' }} fs-3"></i>
                                                @elseif($device->device_type === 'tablet')
                                                    <i class="bi bi-tablet {{ $device->is_trusted ? 'text-success' : 'text-warning' }} fs-3"></i>
                                                @else
                                                    <i class="bi bi-pc-display {{ $device->is_trusted ? 'text-success' : 'text-warning' }} fs-3"></i>
                                                @endif
                                            </div>
                                            
                                            <!-- Device Details -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h5 class="mb-0 fw-bold text-dark">{{ $device->device_name }}</h5>
                                                    @if($device->is_trusted)
                                                        <span class="badge bg-success ms-2">
                                                            <i class="bi bi-shield-check me-1"></i> เชื่อถือได้
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-question-circle me-1"></i> ไม่เชื่อถือ
                                                        </span>
                                                    @endif
                                                    
                                                    @if($device->last_seen_at && $device->last_seen_at->gt(now()->subHours(1)))
                                                        <span class="badge bg-primary ms-2">
                                                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> ออนไลน์
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="text-muted mb-2">
                                                    <i class="bi bi-pc-display me-2"></i>
                                                    {{ $device->operating_system ?? $device->platform }} 
                                                    @if($device->browser_name)
                                                        • {{ $device->browser_name }}
                                                        @if($device->browser_version)
                                                            {{ $device->browser_version }}
                                                        @endif
                                                    @endif
                                                </div>
                                                
                                                @if($device->ip_address)
                                                    <div class="text-muted mb-2">
                                                        <i class="bi bi-geo-alt me-2"></i>
                                                        <code class="text-muted">{{ $device->ip_address }}</code>
                                                        @if($device->location)
                                                            • {{ $device->location }}
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                                <div class="text-muted">
                                                    <i class="bi bi-clock me-2"></i>
                                                    @if($device->last_seen_at)
                                                        ใช้งานล่าสุด: {{ $device->last_seen_at->diffForHumans() }}
                                                        <small class="ms-2">({{ $device->last_seen_at->format('d M Y H:i') }})</small>
                                                    @else
                                                        ไม่เคยใช้งาน
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 text-end">
                                        <div class="btn-group" role="group">
                                            @if($device->is_trusted)
                                                <form action="{{ route('user.devices.untrust', $device) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning" 
                                                            onclick="return confirm('ยกเลิกการเชื่อถืออุปกรณ์นี้หรือไม่? คุณจะต้องตรวจสอบตัวตนอีกครั้ง')"
                                                            title="ยกเลิกการเชื่อถือ">
                                                        <i class="bi bi-x me-1"></i> ยกเลิก
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('user.devices.trust', $device) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" 
                                                            title="เชื่อถืออุปกรณ์">
                                                        <i class="bi bi-check me-1"></i> เชื่อถือ
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <button class="btn btn-outline-primary" 
                                                    onclick="viewDeviceDetails('{{ $device->id }}')"
                                                    title="ดูรายละเอียด">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            <form action="{{ route('user.devices.destroy', $device) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" 
                                                        onclick="return confirm('ลบอุปกรณ์นี้หรือไม่? คุณจะต้องลงทะเบียนใหม่ในการเข้าสู่ระบบครั้งต่อไป')"
                                                        title="ลบอุปกรณ์">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-phone text-primary fs-2"></i>
                            </div>
                            <h5 class="text-dark fw-bold mb-2">ไม่มีอุปกรณ์ที่ลงทะเบียน</h5>
                            <p class="text-muted mb-4">อุปกรณ์ของคุณจะปรากฏที่นี่หลังจากเข้าสู่ระบบ</p>
                            <button class="btn btn-primary" onclick="registerCurrentDevice()">
                                <i class="bi bi-plus me-2"></i> ลงทะเบียนอุปกรณ์นี้
                            </button>
                        </div>
                    @endif
                    
                    <!-- Pagination -->
                    @if($userDevices->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $userDevices->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Security Tips -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-lightbulb text-primary me-2"></i>
                        เคล็ดลับความปลอดภัย
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark">
                                <i class="bi bi-shield-check text-success me-2"></i>
                                อุปกรณ์ที่เชื่อถือได้
                            </h6>
                            <ul class="list-unstyled text-muted">
                                <li>• เชื่อถืออุปกรณ์ที่คุณเป็นเจ้าของและควบคุมเท่านั้น</li>
                                <li>• ตรวจสอบและลบอุปกรณ์เก่าหรือไม่ได้ใช้งานเป็นประจำ</li>
                                <li>• อย่าเชื่อถือคอมพิวเตอร์สาธารณะหรือที่ใช้ร่วมกัน</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                ความปลอดภัย
                            </h6>
                            <ul class="list-unstyled text-muted">
                                <li>• ออกจากระบบทุกอุปกรณ์หากสงสัยว่ามีการเข้าถึงโดยไม่ได้รับอนุญาต</li>
                                <li>• อัปเดตอุปกรณ์ของคุณด้วยแพตช์ความปลอดภัยล่าสุด</li>
                                <li>• ใช้รหัสผ่านที่แข็งแกร่งและเป็นเอกลักษณ์สำหรับบัญชีของคุณ</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Device Details Modal -->
<div class="modal fade" id="deviceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-phone text-primary me-2"></i>
                    รายละเอียดอุปกรณ์
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="deviceDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<style>
.avatar-lg {
    width: 60px;
    height: 60px;
}

.avatar-xl {
    width: 80px;
    height: 80px;
}

.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1) !important;
}

.border-primary-subtle {
    border-color: rgba(13, 110, 253, 0.2) !important;
}

.border-warning-subtle {
    border-color: rgba(255, 193, 7, 0.2) !important;
}

.border-success-subtle {
    border-color: rgba(25, 135, 84, 0.2) !important;
}

.border-info-subtle {
    border-color: rgba(13, 202, 240, 0.2) !important;
}

.device-item {
    transition: opacity 0.3s ease;
}

/* SweetAlert2 Custom Styles */
.swal-wide {
    width: 600px !important;
}

.swal2-popup {
    border-radius: 15px !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2) !important;
}

.swal2-title {
    font-size: 1.5rem !important;
    font-weight: 600 !important;
    color: #2c3e50 !important;
}

.swal2-content {
    font-size: 1rem !important;
    color: #5a6c7d !important;
}

.swal2-confirm {
    border-radius: 8px !important;
    font-weight: 500 !important;
    padding: 10px 20px !important;
}

.swal2-cancel {
    border-radius: 8px !important;
    font-weight: 500 !important;
    padding: 10px 20px !important;
}

.swal2-loading .swal2-styled.swal2-confirm {
    border: 2px solid transparent !important;
}

.swal2-timer-progress-bar {
    background: rgba(0,123,255,.75) !important;
}

.swal2-success .swal2-success-ring {
    border-color: rgba(25,135,84,.3) !important;
}

.swal2-success .swal2-success-fix {
    background-color: #198754 !important;
}

.swal2-error .swal2-x-mark {
    color: #dc3545 !important;
}
</style>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    detectCurrentDevice();
    updateCurrentTime();
    
    // Update time every minute
    setInterval(updateCurrentTime, 60000);
});

function detectCurrentDevice() {
    const userAgent = navigator.userAgent;
    let deviceInfo = 'อุปกรณ์ไม่ทราบ';
    
    // Simple device detection
    if (/Mobile|Android|iPhone|iPad/.test(userAgent)) {
        if (/iPad/.test(userAgent)) {
            deviceInfo = 'iPad';
        } else if (/iPhone/.test(userAgent)) {
            deviceInfo = 'iPhone';
        } else if (/Android/.test(userAgent)) {
            deviceInfo = 'อุปกรณ์ Android';
        } else {
            deviceInfo = 'อุปกรณ์มือถือ';
        }
    } else {
        if (/Windows/.test(userAgent)) {
            deviceInfo = 'คอมพิวเตอร์ Windows';
        } else if (/Mac/.test(userAgent)) {
            deviceInfo = 'คอมพิวเตอร์ Mac';
        } else if (/Linux/.test(userAgent)) {
            deviceInfo = 'คอมพิวเตอร์ Linux';
        } else {
            deviceInfo = 'คอมพิวเตอร์เดสก์ท็อป';
        }
    }
    
    // Add browser info
    if (/Chrome/.test(userAgent)) {
        deviceInfo += ' (Chrome)';
    } else if (/Firefox/.test(userAgent)) {
        deviceInfo += ' (Firefox)';
    } else if (/Safari/.test(userAgent)) {
        deviceInfo += ' (Safari)';
    } else if (/Edge/.test(userAgent)) {
        deviceInfo += ' (Edge)';
    }
    
    document.getElementById('currentDevice').textContent = deviceInfo;
}

function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
    document.getElementById('currentTime').textContent = timeString;
}

function refreshDevices() {
    location.reload();
}

function toggleDeviceView() {
    const showAll = document.getElementById('showAllDevices').checked;
    const inactiveDevices = document.querySelectorAll('.inactive-device');
    
    inactiveDevices.forEach(device => {
        device.style.display = showAll ? 'block' : 'none';
    });
}

function trustCurrentDevice() {
    Swal.fire({
        title: 'เชื่อถืออุปกรณ์นี้',
        text: 'คุณจะไม่ต้องตรวจสอบตัวตนอีกครั้งในการเข้าสู่ระบบครั้งต่อไปจากอุปกรณ์นี้',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-shield-check me-2"></i>เชื่อถือ',
        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // แสดง loading
            Swal.fire({
                title: 'กำลังตั้งค่าอุปกรณ์ที่เชื่อถือได้...',
                html: '<div class="d-flex align-items-center justify-content-center"><div class="spinner-border text-success me-3" role="status"></div>กรุณารอสักครู่</div>',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });

            fetch('{{ route("user.devices.trust-current") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: 'เชื่อถืออุปกรณ์เรียบร้อยแล้ว',
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        confirmButtonText: '<i class="bi bi-check-circle me-2"></i>ตกลง',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message || 'ไม่สามารถเชื่อถืออุปกรณ์ได้',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: '<i class="bi bi-exclamation-circle me-2"></i>ตกลง'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'เกิดข้อผิดพลาดขณะเชื่อมต่อกับเซิร์ฟเวอร์',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: '<i class="bi bi-exclamation-circle me-2"></i>ตกลง'
                });
            });
        }
    });
}

function logoutAllDevices() {
    Swal.fire({
        title: 'ออกจากระบบทุกอุปกรณ์',
        text: 'คุณจะยังคงเข้าสู่ระบบอยู่ในอุปกรณ์นี้ แต่จะออกจากระบบจากอุปกรณ์อื่นทั้งหมด',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ',
        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // แสดง loading
            Swal.fire({
                title: 'กำลังออกจากระบบอุปกรณ์อื่น...',
                html: '<div class="d-flex align-items-center justify-content-center"><div class="spinner-border text-warning me-3" role="status"></div>กรุณารอสักครู่</div>',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });

            fetch('{{ route("user.devices.logout-all") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: `ออกจากระบบจากอุปกรณ์ ${data.count} เครื่องเรียบร้อยแล้ว`,
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        confirmButtonText: '<i class="bi bi-check-circle me-2"></i>ตกลง',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถออกจากระบบจากอุปกรณ์อื่นได้',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: '<i class="bi bi-exclamation-circle me-2"></i>ตกลง'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'เกิดข้อผิดพลาดขณะเชื่อมต่อกับเซิร์ฟเวอร์',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: '<i class="bi bi-exclamation-circle me-2"></i>ตกลง'
                });
            });
        }
    });
}

function registerCurrentDevice() {
    Swal.fire({
        title: 'ลงทะเบียนอุปกรณ์นี้',
        text: 'ต้องการลงทะเบียนอุปกรณ์นี้สำหรับการติดตามความปลอดภัยที่ดีขึ้นหรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-plus-circle me-2"></i>ลงทะเบียน',
        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>ยกเลิก',
        reverseButtons: true,
        customClass: {
            popup: 'swal-wide'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // แสดง loading
            Swal.fire({
                title: 'กำลังลงทะเบียนอุปกรณ์...',
                html: '<div class="d-flex align-items-center justify-content-center"><div class="spinner-border text-primary me-3" role="status"></div>กรุณารอสักครู่</div>',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });

            fetch('{{ route("user.devices.register-current") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: 'ลงทะเบียนอุปกรณ์เรียบร้อยแล้ว',
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        confirmButtonText: '<i class="bi bi-check-circle me-2"></i>ตกลง',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message || 'ไม่สามารถลงทะเบียนอุปกรณ์ได้',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: '<i class="bi bi-exclamation-circle me-2"></i>ตกลง'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'เกิดข้อผิดพลาดขณะเชื่อมต่อกับเซิร์ฟเวอร์',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: '<i class="bi bi-exclamation-circle me-2"></i>ตกลง'
                });
            });
        }
    });
}

function viewDeviceDetails(deviceId) {
    const modal = new bootstrap.Modal(document.getElementById('deviceDetailsModal'));
    const content = document.getElementById('deviceDetailsContent');
    
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary me-3" role="status"></div>กำลังโหลดรายละเอียดอุปกรณ์...</div>';
    modal.show();
    
    fetch(`{{ route('user.devices.show', ':id') }}`.replace(':id', deviceId))
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            modal.hide();
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: 'ไม่สามารถโหลดรายละเอียดอุปกรณ์ได้',
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: '<i class="bi bi-exclamation-circle me-2"></i>ตกลง'
            });
        });
}
</script>
@endsection
