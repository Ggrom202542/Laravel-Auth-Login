@extends('layouts.dashboard')

@section('title', 'ระบบรักษาความปลอดภัยขั้นสูง - Super Admin')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4 security-header-wrapper">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-dark fw-bold mb-1">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        ระบบรักษาความปลอดภัยขั้นสูง
                    </h2>
                    <p class="text-muted mb-0">ระบบควบคุมและติดตามความปลอดภัยระดับองค์กรสำหรับ Super Admin</p>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary me-2" id="refreshStats">
                        <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                    </button>
                    <div class="dropdown security-dropdown">
                        <button class="btn btn-primary dropdown-toggle" 
                                type="button" 
                                id="securityDropdown"
                                aria-expanded="false">
                            <i class="bi bi-gear me-1"></i> การจัดการ
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" 
                            aria-labelledby="securityDropdown">
                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); runSystemScan();">
                                <i class="bi bi-search me-2"></i>สแกนระบบความปลอดภัย
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); cleanupExpiredLocks();">
                                <i class="bi bi-trash3 me-2"></i>ลบข้อมูลล็อกที่หมดอายุ
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); forceLogoutAll();">
                                <i class="bi bi-box-arrow-right me-2"></i>บังคับ Logout ทั้งหมด
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('super-admin.security.policies') }}">
                                <i class="bi bi-shield-fill-check me-2"></i>จัดการนโยบายความปลอดภัย
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-activity text-success me-2"></i>
                        สถานะระบบความปลอดภัย
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="position-relative d-inline-block">
                                    <canvas id="securityLevelChart" width="120" height="120"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <h4 class="mb-0 fw-bold text-{{ $systemHealth['security_level'] >= 80 ? 'success' : ($systemHealth['security_level'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ $systemHealth['security_level'] }}%
                                        </h4>
                                        <small class="text-muted">ระดับความปลอดภัย</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                            <i class="bi bi-exclamation-triangle-fill text-{{ $systemHealth['threat_level'] === 'สูง' ? 'danger' : ($systemHealth['threat_level'] === 'ปานกลาง' ? 'warning' : 'success') }} fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">ระดับภัยคุกคาม</h6>
                                            <span class="badge bg-{{ $systemHealth['threat_level'] === 'สูง' ? 'danger' : ($systemHealth['threat_level'] === 'ปานกลาง' ? 'warning' : 'success') }}">
                                                {{ $systemHealth['threat_level'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                            <i class="bi bi-people-fill text-info fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">เซสชันที่ใช้งาน</h6>
                                            <p class="mb-0 fw-bold">{{ number_format($systemHealth['active_sessions']) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                            <i class="bi bi-clock-history text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">สแกนล่าสุด</h6>
                                            <p class="mb-0 small text-muted">{{ $systemHealth['last_security_scan'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Users -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-people text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">ผู้ใช้ทั้งหมด</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($securityStats['total_users']) }}</h3>
                            <small class="text-success">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ number_format($securityStats['active_users']) }} ใช้งานอยู่
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Locked Accounts -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                            <i class="bi bi-lock text-danger fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">บัญชีที่ถูกล็อก</h6>
                            <h3 class="mb-0 fw-bold text-danger">{{ number_format($securityStats['locked_accounts']) }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>
                                {{ number_format($securityStats['admin_accounts']) }} Admin
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspicious Activities -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                            <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">กิจกรรมน่าสงสัยวันนี้</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ number_format($securityStats['suspicious_logins_today']) }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ number_format($securityStats['failed_attempts_today']) }} ล้มเหลว
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IP Restrictions -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                            <i class="bi bi-globe text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">การจำกัด IP</h6>
                            <h3 class="mb-0 fw-bold text-info">{{ number_format($securityStats['blocked_ips']) }}</h3>
                            <small class="text-success">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ number_format($securityStats['whitelisted_ips']) }} อนุญาต
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Management Modules -->
    <div class="row g-4 mb-4">
        <!-- Device Management -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-phone text-primary me-2"></i>
                        การจัดการอุปกรณ์
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">ตรวจสอบและจัดการอุปกรณ์ทั้งหมดในระบบ</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('super-admin.security.devices') }}" class="btn btn-outline-primary">
                            <i class="bi bi-gear me-1"></i> จัดการอุปกรณ์
                        </a>
                        <button class="btn btn-outline-warning" onclick="revokeAllSuspiciousDevices()">
                            <i class="bi bi-ban me-1"></i> เพิกถอนอุปกรณ์ที่น่าสงสัย
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- IP Management -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-globe text-info me-2"></i>
                        การจัดการ IP
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">ควบคุมการเข้าถึงจาก IP address ต่างๆ</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('super-admin.security.ip-management') }}" class="btn btn-outline-info">
                            <i class="bi bi-list-ul me-1"></i> จัดการ IP
                        </a>
                        <button class="btn btn-outline-danger" onclick="blockSuspiciousIPs()">
                            <i class="bi bi-shield-slash me-1"></i> บล็อก IP น่าสงสัย
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent High-Risk Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-exclamation-diamond text-danger me-2"></i>
                            กิจกรรมเสี่ยงสูงล่าสุด
                        </h6>
                        <a href="{{ route('super-admin.security.suspicious-activity') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i> ดูทั้งหมด
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($recentHighRiskActivities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">เวลา</th>
                                        <th class="border-0">ผู้ใช้</th>
                                        <th class="border-0">IP Address</th>
                                        <th class="border-0">ความเสี่ยง</th>
                                        <th class="border-0">สถานที่</th>
                                        <th class="border-0">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentHighRiskActivities as $activity)
                                    <tr>
                                        <td>
                                            <div>{{ $activity->attempted_at->format('d/m/Y H:i') }}</div>
                                            <small class="text-muted">{{ $activity->attempted_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if($activity->user)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="bi bi-person text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">{{ $activity->user->name }}</div>
                                                        <small class="text-muted">{{ $activity->user->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">ไม่ระบุผู้ใช้</span>
                                            @endif
                                        </td>
                                        <td>
                                            <code class="bg-light px-2 py-1 rounded">{{ $activity->ip_address }}</code>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                                    <div class="progress-bar bg-{{ $activity->risk_score >= 80 ? 'danger' : 'warning' }}" 
                                                         style="width: {{ $activity->risk_score }}%"></div>
                                                </div>
                                                <span class="badge bg-{{ $activity->risk_score >= 80 ? 'danger' : 'warning' }}-subtle text-{{ $activity->risk_score >= 80 ? 'danger' : 'warning' }}">
                                                    {{ $activity->risk_score }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $activity->city ?? 'ไม่ทราบ' }}</div>
                                            <small class="text-muted">{{ $activity->country_name ?? 'ไม่ระบุประเทศ' }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="investigateActivity({{ $activity->id }})">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="blockIP('{{ $activity->ip_address }}')">
                                                    <i class="bi bi-ban"></i>
                                                </button>
                                                @if($activity->user)
                                                <button class="btn btn-outline-warning" onclick="suspendUser({{ $activity->user->id }})">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-success">ไม่มีกิจกรรมเสี่ยงสูง</h5>
                            <p class="text-muted">ระบบปลอดภัยในช่วงเวลานี้</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    .avatar-sm {
        width: 35px;
        height: 35px;
    }
    
    /* Reset และ Override dropdown styles สำหรับหน้า security dashboard */
    .security-dropdown {
        position: relative !important;
        z-index: 10050 !important;
    }
    
    .security-dropdown .dropdown-menu {
        /* Reset all previous styles */
        all: unset;
        
        /* Apply new styles */
        display: none;
        position: absolute !important;
        top: 100% !important;
        right: 0 !important;
        left: auto !important;
        z-index: 10051 !important;
        
        min-width: 250px !important;
        max-width: 300px !important;
        
        background: #ffffff !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2) !important;
        
        padding: 0.5rem 0 !important;
        margin-top: 0.25rem !important;
        
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
        transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
        
        /* Override any inherited properties */
        font-family: inherit !important;
        font-size: 1rem !important;
        color: #212529 !important;
        text-align: left !important;
        list-style: none !important;
        background-clip: padding-box !important;
        backdrop-filter: none !important;
        animation: none !important;
    }
    
    .security-dropdown .dropdown-menu.show {
        display: block !important;
        opacity: 1 !important;
        transform: translateY(0) scale(1) !important;
    }
    
    .security-dropdown .dropdown-item {
        /* Reset all previous styles */
        all: unset;
        
        /* Apply new styles */
        display: block !important;
        width: 100% !important;
        padding: 0.75rem 1.25rem !important;
        clear: both !important;
        font-weight: 400 !important;
        font-size: 0.9rem !important;
        color: #374151 !important;
        text-align: inherit !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        background-color: transparent !important;
        border: 0 !important;
        cursor: pointer !important;
        transition: all 0.15s ease-in-out !important;
        
        /* เอา animation ของ dashboard.css ออก */
        animation: none !important;
        backdrop-filter: none !important;
    }
    
    .security-dropdown .dropdown-item:hover,
    .security-dropdown .dropdown-item:focus {
        background-color: #f8f9fa !important;
        color: #1f2937 !important;
        text-decoration: none !important;
        transform: none !important;
    }
    
    .security-dropdown .dropdown-item:active {
        background-color: #e9ecef !important;
        color: #1f2937 !important;
    }
    
    .security-dropdown .dropdown-item i {
        display: inline-block !important;
        width: 16px !important;
        margin-right: 0.5rem !important;
        color: #6b7280 !important;
        font-size: 0.875rem !important;
        text-align: center !important;
        transition: color 0.15s ease-in-out !important;
    }
    
    .security-dropdown .dropdown-item:hover i {
        color: #374151 !important;
    }
    
    .security-dropdown .dropdown-divider {
        height: 0 !important;
        margin: 0.5rem 0 !important;
        overflow: hidden !important;
        border-top: 1px solid #e5e7eb !important;
        opacity: 1 !important;
    }
    
    /* ป้องกัน container overflow และ z-index conflicts */
    .container-fluid,
    .card,
    .card-body,
    .card-header {
        overflow: visible !important;
    }
    
    /* เพิ่ม space สำหรับ dropdown */
    .security-header-wrapper {
        position: relative !important;
        z-index: 10050 !important;
        margin-bottom: 2rem !important;
    }
    
    @media (max-width: 768px) {
        .security-dropdown .dropdown-menu {
            min-width: 200px !important;
            max-width: 250px !important;
            right: 0 !important;
            left: auto !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global SweetAlert2 configuration
Swal.mixin({
    customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-secondary'
    },
    buttonsStyling: false
});

// Security Level Chart
const securityLevelCtx = document.getElementById('securityLevelChart').getContext('2d');
const securityLevel = {{ $systemHealth['security_level'] }};

new Chart(securityLevelCtx, {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [securityLevel, 100 - securityLevel],
            backgroundColor: [
                securityLevel >= 80 ? '#198754' : (securityLevel >= 60 ? '#ffc107' : '#dc3545'),
                '#e9ecef'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: false,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                enabled: false
            }
        }
    }
});

// Security Actions
function refreshStats() {
    window.location.reload();
}

function runSystemScan() {
    Swal.fire({
        title: 'สแกนระบบความปลอดภัย',
        text: 'ต้องการเริ่มการสแกนระบบความปลอดภัยหรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-search me-1"></i>เริ่มสแกน',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch('{{ route("super-admin.security.system-scan") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`เกิดข้อผิดพลาด: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value && result.value.success) {
                Swal.fire({
                    title: 'เริ่มการสแกนแล้ว!',
                    text: 'ระบบกำลังสแกนความปลอดภัย กรุณารอสักครู่...',
                    icon: 'success',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถเริ่มการสแกนระบบได้',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            }
        }
    });
}

function cleanupExpiredLocks() {
    Swal.fire({
        title: 'ลบข้อมูลล็อกที่หมดอายุ',
        text: 'ต้องการลบข้อมูลล็อกที่หมดอายุหรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#fd7e14',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash3 me-1"></i>ลบข้อมูล',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch('{{ route("super-admin.security.cleanup-expired") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`เกิดข้อผิดพลาด: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value && result.value.success) {
                Swal.fire({
                    title: 'ลบข้อมูลสำเร็จ!',
                    text: result.value.message || 'ลบข้อมูลล็อกที่หมดอายุเรียบร้อยแล้ว',
                    icon: 'success',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: result.value?.message || 'ไม่สามารถลบข้อมูลได้',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            }
        }
    });
}

function forceLogoutAll() {
    Swal.fire({
        title: 'บังคับ Logout ผู้ใช้ทั้งหมด',
        html: `
            <div class="text-start">
                <p class="mb-2">การกระทำนี้จะ:</p>
                <ul class="list-unstyled ms-3">
                    <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>บังคับให้ผู้ใช้ทั้งหมดออกจากระบบ</li>
                    <li><i class="bi bi-arrow-clockwise text-info me-2"></i>ต้องเข้าสู่ระบบใหม่</li>
                    <li><i class="bi bi-shield-check text-danger me-2"></i>ไม่สามารถยกเลิกได้</li>
                </ul>
                <p class="text-danger fw-bold mb-0">ต้องการดำเนินการต่อหรือไม่?</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i>บังคับ Logout',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch('{{ route("super-admin.security.force-logout-all") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`เกิดข้อผิดพลาด: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value && result.value.success) {
                Swal.fire({
                    title: 'บังคับ Logout สำเร็จ!',
                    text: result.value.message || 'บังคับให้ผู้ใช้ทั้งหมดออกจากระบบเรียบร้อยแล้ว',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                });
            } else {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: result.value?.message || 'ไม่สามารถบังคับ logout ได้',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            }
        }
    });
}

function investigateActivity(activityId) {
    window.open(`{{ url('/super-admin/security/investigate') }}/${activityId}`, '_blank');
}

function blockIP(ipAddress) {
    Swal.fire({
        title: 'บล็อก IP Address',
        html: `
            <div class="text-start">
                <p class="mb-3">ต้องการบล็อก IP Address นี้หรือไม่?</p>
                <div class="bg-light p-3 rounded mb-3">
                    <code class="fs-5 text-danger">${ipAddress}</code>
                </div>
                <div class="form-group">
                    <label for="blockReason" class="form-label fw-bold">เหตุผล (ไม่บังคับ):</label>
                    <input type="text" id="blockReason" class="form-control" placeholder="ระบุเหตุผลในการบล็อก...">
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-ban me-1"></i>บล็อก IP',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const reason = document.getElementById('blockReason').value;
            return fetch('{{ route("super-admin.security.block-ip") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    ip_address: ipAddress,
                    reason: reason || 'บล็อกโดย Super Admin'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`เกิดข้อผิดพลาด: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value && result.value.success) {
                Swal.fire({
                    title: 'บล็อก IP สำเร็จ!',
                    html: `
                        <div class="text-center">
                            <p class="mb-3">บล็อก IP Address เรียบร้อยแล้ว</p>
                            <div class="bg-light p-3 rounded">
                                <code class="fs-6 text-danger">${ipAddress}</code>
                            </div>
                        </div>
                    `,
                    icon: 'success',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: result.value?.message || 'ไม่สามารถบล็อก IP ได้',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            }
        }
    });
}

function suspendUser(userId) {
    Swal.fire({
        title: 'ระงับผู้ใช้',
        text: 'ฟีเจอร์ระงับผู้ใช้จะเปิดใช้งานในขั้นตอนถัดไป',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-info-circle me-1"></i>รับทราب',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ปิด',
        footer: '<small class="text-muted">ฟีเจอร์นี้จะพร้อมใช้งานในอนาคต</small>'
    });
}

function revokeAllSuspiciousDevices() {
    Swal.fire({
        title: 'เพิกถอนอุปกรณ์ที่น่าสงสัย',
        text: 'ฟีเจอร์นี้จะเปิดใช้งานในขั้นตอนถัดไป',
        icon: 'info',
        confirmButtonText: '<i class="bi bi-info-circle me-1"></i>รับทราบ',
        footer: '<small class="text-muted">ฟีเจอร์นี้จะพร้อมใช้งานในอนาคต</small>'
    });
}

function blockSuspiciousIPs() {
    Swal.fire({
        title: 'บล็อก IP น่าสงสัย',
        text: 'ฟีเจอร์นี้จะเปิดใช้งานในขั้นตอนถัดไป',
        icon: 'info',
        confirmButtonText: '<i class="bi bi-info-circle me-1"></i>รับทราบ',
        footer: '<small class="text-muted">ฟีเจอร์นี้จะพร้อมใช้งานในอนาคต</small>'
    });
}

function showToast(message, type = 'info') {
    // ใช้ SweetAlert2 Toast แทน
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    let icon = 'info';
    if (type === 'success') icon = 'success';
    else if (type === 'error') icon = 'error';
    else if (type === 'warning') icon = 'warning';

    Toast.fire({
        icon: icon,
        title: message
    });
}

// Security Dropdown Enhancement
document.addEventListener('DOMContentLoaded', function() {
    try {
        const securityDropdown = document.querySelector('.security-dropdown');
        const dropdownButton = securityDropdown?.querySelector('.dropdown-toggle');
        const dropdownMenu = securityDropdown?.querySelector('.dropdown-menu');
        
        if (!securityDropdown || !dropdownButton || !dropdownMenu) {
            console.warn('Security Dashboard: Dropdown elements not found');
            return;
        }
        
        // เพิ่ม event listener สำหรับการคลิก
        dropdownButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle dropdown
            const isShown = dropdownMenu.classList.contains('show');
            
            // ปิด dropdown อื่นๆ ก่อน
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu !== dropdownMenu) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle dropdown ปัจจุบัน
            if (isShown) {
                dropdownMenu.classList.remove('show');
                dropdownButton.setAttribute('aria-expanded', 'false');
            } else {
                dropdownMenu.classList.add('show');
                dropdownButton.setAttribute('aria-expanded', 'true');
            }
        });
        
        // ปิด dropdown เมื่อคลิกข้างนอก
        document.addEventListener('click', function(e) {
            if (!securityDropdown.contains(e.target)) {
                dropdownMenu.classList.remove('show');
                dropdownButton.setAttribute('aria-expanded', 'false');
            }
        });
        
        // ปิด dropdown เมื่อ escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdownMenu.classList.remove('show');
                dropdownButton.setAttribute('aria-expanded', 'false');
            }
        });
        
        // Setup refresh button
        const refreshButton = document.getElementById('refreshStats');
        if (refreshButton) {
            refreshButton.addEventListener('click', function(e) {
                e.preventDefault();
                try {
                    location.reload();
                } catch (error) {
                    console.error('Error refreshing page:', error);
                }
            });
        }
        
    } catch (error) {
        console.error('Security Dashboard: Error setting up dropdown:', error);
    }
});

// Auto refresh every 30 seconds with error handling
try {
    setInterval(() => {
        fetch('{{ route("super-admin.security.stats") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Update stats without full page reload
                // TODO: Implement dynamic stats update
            })
            .catch(error => {
                console.warn('Failed to fetch security stats:', error);
            });
    }, 30000);
} catch (error) {
    console.error('Error setting up auto refresh:', error);
}
</script>
@endpush
@endsection
