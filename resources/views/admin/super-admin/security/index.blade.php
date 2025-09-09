@extends('layouts.dashboard')

@section('title', 'ระบบรักษาความปลอดภัยขั้นสูง - Super Admin')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-dark fw-bold mb-1">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        ระบบรักษาความปลอดภัยขั้นสูง
                    </h2>
                    <p class="text-muted mb-0">ระบบควบคุมและติดตามความปลอดภัยระดับองค์กรสำหรับ Super Admin</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" id="refreshStats">
                        <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                    </button>
                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i> การจัดการ
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="runSystemScan()">
                                <i class="bi bi-search me-2"></i>สแกนระบบความปลอดภัย
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="cleanupExpiredLocks()">
                                <i class="bi bi-trash3 me-2"></i>ลบข้อมูลล็อกที่หมดอายุ
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="forceLogoutAll()">
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
    if (confirm('ต้องการเริ่มการสแกนระบบความปลอดภัยหรือไม่?')) {
        fetch('{{ route("super-admin.security.system-scan") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('เริ่มการสแกนระบบแล้ว', 'success');
                setTimeout(() => window.location.reload(), 3000);
            } else {
                showToast('เกิดข้อผิดพลาดในการสแกน', 'error');
            }
        });
    }
}

function cleanupExpiredLocks() {
    if (confirm('ต้องการลบข้อมูลล็อกที่หมดอายุหรือไม่?')) {
        fetch('{{ route("super-admin.security.cleanup-expired") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                setTimeout(() => window.location.reload(), 2000);
            }
        });
    }
}

function forceLogoutAll() {
    if (confirm('ต้องการบังคับ logout ผู้ใช้ทั้งหมดหรือไม่? การกระทำนี้ไม่สามารถยกเลิกได้')) {
        fetch('{{ route("super-admin.security.force-logout-all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
        });
    }
}

function investigateActivity(activityId) {
    window.open(`{{ url('/super-admin/security/investigate') }}/${activityId}`, '_blank');
}

function blockIP(ipAddress) {
    if (confirm(`ต้องการบล็อก IP ${ipAddress} หรือไม่?`)) {
        fetch('{{ route("super-admin.security.block-ip") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ip_address: ipAddress })
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                setTimeout(() => window.location.reload(), 2000);
            }
        });
    }
}

function suspendUser(userId) {
    // เปิด modal สำหรับระงับผู้ใช้
    // TODO: Implement suspend user modal
    alert('ฟีเจอร์ระงับผู้ใช้จะเปิดใช้งานในขั้นตอนถัดไป');
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.innerHTML = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Auto refresh every 30 seconds
setInterval(() => {
    fetch('{{ route("super-admin.security.stats") }}')
        .then(response => response.json())
        .then(data => {
            // Update stats without full page reload
            // TODO: Implement dynamic stats update
        });
}, 30000);
</script>
@endpush
@endsection
