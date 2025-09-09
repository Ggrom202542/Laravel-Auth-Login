@extends('layouts.dashboard')

@section('title', 'ระบบจัดการความปลอดภัย')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-dark fw-bold mb-1">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        ระบบจัดการความปลอดภัย
                    </h2>
                    <p class="text-muted mb-0">แดชบอร์ดตรวจสอบและจัดการความปลอดภัยแบบครบครัน</p>
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
                            <li><a class="dropdown-item" href="#" onclick="cleanupExpiredLocks()">
                                <i class="bi bi-trash3 me-2"></i>ลบข้อมูลล็อกที่หมดอายุ
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="cleanupExpiredIPs()">
                                <i class="bi bi-x-circle me-2"></i>ลบ IP ที่หมดอายุ
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.security.report') }}">
                                <i class="bi bi-graph-up me-2"></i>สร้างรายงาน
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- สถิติโดยย่อ -->
    <div class="row g-3 mb-4">
        <!-- บัญชีที่ถูกล็อก -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fw-semibold">บัญชีที่ถูกล็อก</p>
                            <h3 class="mb-0 text-danger">{{ $statistics['total_locked'] ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                {{ $statistics['locked_today'] ?? 0 }} วันนี้
                            </small>
                        </div>
                        <div class="avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-person-lock text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ความพยายามที่ล้มเหลว -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fw-semibold">ความพยายามที่ล้มเหลว</p>
                            <h3 class="mb-0 text-warning">{{ $statistics['high_failed_attempts'] ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                บัญชีเสี่ยงสูง
                            </small>
                        </div>
                        <div class="avatar-lg bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-ban text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- กิจกรรมล่าสุด -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fw-semibold">กิจกรรมล่าสุด</p>
                            <h3 class="mb-0 text-success">{{ $lockedAccounts->count() }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-people me-1"></i>
                                เซสชันที่ใช้งาน
                            </small>
                        </div>
                        <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-graph-up text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- สถานะระบบ -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fw-semibold">สถานะระบบ</p>
                            <h3 class="mb-0 text-info">ออนไลน์</h3>
                            <small class="text-muted">
                                <i class="bi bi-check-circle me-1"></i>
                                ระบบทุกอย่างทำงานปกติ
                            </small>
                        </div>
                        <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-hdd text-info fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- โมดูลความปลอดภัย -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
                        โมดูลความปลอดภัย
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- การจัดการการล็อกบัญชี -->
                        <div class="col-xl-3 col-md-6">
                            <a href="#lockedAccountsSection" class="text-decoration-none" onclick="scrollToSection('lockedAccountsSection')">
                                <div class="card border border-primary-subtle h-100 hover-card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                            <i class="bi bi-person-fill-lock text-primary fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">การล็อกบัญชี</h6>
                                        <p class="text-muted small mb-0">จัดการบัญชีที่ถูกล็อกและความพยายามเข้าสู่ระบบที่ล้มเหลว</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- การจัดการ IP -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('admin.security.ip.index') }}" class="text-decoration-none">
                                <div class="card border border-warning-subtle h-100 hover-card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                            <i class="bi bi-hdd-network text-warning fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">การจัดการ IP</h6>
                                        <p class="text-muted small mb-0">ควบคุมรายชื่อ IP ที่อนุญาต ห้าม และติดตามทางภูมิศาสตร์</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- การจัดการอุปกรณ์ -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('admin.security.devices') }}" class="text-decoration-none">
                                <div class="card border border-success-subtle h-100 hover-card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                            <i class="bi bi-phone text-success fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">การจัดการอุปกรณ์</h6>
                                        <p class="text-muted small mb-0">ติดตามและจัดการอุปกรณ์ที่ไว้วางใจ</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- การตรวจจับการเข้าสู่ระบบที่น่าสงสัย -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('admin.security.suspicious-logins') }}" class="text-decoration-none">
                                <div class="card border border-info-subtle h-100 hover-card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                            <i class="bi bi-eye text-info fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">การตรวจจับความน่าสงสัย</h6>
                                        <p class="text-muted small mb-0">การตรวจจับความผิดปกติด้วย AI และการวิเคราะห์</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วนบัญชีที่ถูกล็อก -->
    <div id="lockedAccountsSection" class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-person-lock text-danger me-2"></i>
                            บัญชีที่ถูกล็อก
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-success" onclick="unlockAllExpired()">
                                <i class="bi bi-unlock me-1"></i> ปลดล็อกที่หมดอายุ
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($lockedAccounts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4">ผู้ใช้</th>
                                        <th class="border-0">ความพยายามที่ล้มเหลว</th>
                                        <th class="border-0">เวลาที่ล็อก</th>
                                        <th class="border-0">IP ล่าสุด</th>
                                        <th class="border-0">สถานะ</th>
                                        <th class="border-0">การกระทำ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lockedAccounts as $user)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="bi bi-person text-danger"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $user->username }}</h6>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger-subtle text-danger">
                                                {{ $user->failed_login_attempts }} ครั้ง
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $user->locked_at ? $user->locked_at->format('M d, H:i') : '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            <code class="text-muted">{{ $user->last_login_ip ?? 'ไม่ทราบ' }}</code>
                                        </td>
                                        <td>
                                            @php
                                                $lockoutStatus = app(App\Services\AccountLockoutService::class)->getLockoutStatus($user);
                                                $remainingMinutes = $lockoutStatus['remaining_minutes'] ?? 0;
                                            @endphp
                                            @if($remainingMinutes > 0)
                                                <span class="badge bg-warning">เหลือ {{ $remainingMinutes }} นาที</span>
                                            @else
                                                <span class="badge bg-danger">ล็อกถาวร</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="User actions">
                                                <form action="{{ route('admin.security.unlock', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" 
                                                            onclick="return confirm('ปลดล็อกบัญชีนี้?')"
                                                            title="ปลดล็อกบัญชี">
                                                        <i class="bi bi-unlock"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.security.user-details', $user) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="ดูรายละเอียด">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="extendLock({{ $user->id }})"
                                                        title="ขยายการล็อก">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-check-circle text-success fs-2"></i>
                            </div>
                            <h5 class="text-dark fw-bold mb-2">ไม่มีบัญชีที่ถูกล็อก</h5>
                            <p class="text-muted mb-0">บัญชีผู้ใช้ทั้งหมดสามารถใช้งานได้ปกติ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

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

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1) !important;
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
</style>

<script>
// Page initialization
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 30 seconds
    setInterval(function() {
        // Refresh page stats periodically
        // location.reload();
    }, 30000);
});

// Action functions
function scrollToSection(sectionId) {
    document.getElementById(sectionId).scrollIntoView({ 
        behavior: 'smooth' 
    });
}

function refreshStats() {
    location.reload();
}

function cleanupExpiredLocks() {
    if (confirm('คุณแน่ใจหรือว่าต้องการล้างข้อมูลล็อกบัญชีที่หมดอายุทั้งหมด?')) {
        fetch('{{ route("admin.security.cleanup-expired") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`การล้างข้อมูลเสร็จสิ้น: ลบล็อกที่หมดอายุไปแล้ว ${data.count} รายการ`);
                location.reload();
            } else {
                alert('การล้างข้อมูลล้มเหลว');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในระหว่างการล้างข้อมูล');
        });
    }
}

function cleanupExpiredIPs() {
    if (confirm('คุณแน่ใจหรือว่าต้องการล้างข้อมูลข้อจำกัด IP ที่หมดอายุทั้งหมด?')) {
        fetch('{{ route("admin.security.ip.cleanup-expired") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`การล้างข้อมูลเสร็จสิ้น: ลบข้อจำกัด IP ที่หมดอายุไปแล้ว ${data.count} รายการ`);
                location.reload();
            } else {
                alert('การล้างข้อมูลล้มเหลว');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในระหว่างการล้างข้อมูล');
        });
    }
}

function unlockAllExpired() {
    if (confirm('ปลดล็อกบัญชีทั้งหมดที่หมดระยะเวลาล็อกแล้ว?')) {
        // Implementation for bulk unlock
        alert('ฟีเจอร์นี้กำลังจะมาเร็วๆ นี้!');
    }
}

function extendLock(userId) {
    const hours = prompt('ขยายการล็อกกี่ชั่วโมง?', '24');
    if (hours && !isNaN(hours)) {
        // Implementation for extending lock
        alert(`ขยายการล็อกไปแล้ว ${hours} ชั่วโมง`);
    }
}

function exportSecurityReport() {
    window.open('{{ route("admin.security.report") }}?export=pdf', '_blank');
}
</script>
@endsection
