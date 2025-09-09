@extends('layouts.dashboard')

@section('title', 'Super Admin - จัดการอุปกรณ์')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-phone text-primary me-2"></i>
                        Super Admin - จัดการอุปกรณ์
                    </h1>
                    <p class="text-muted mb-0">ติดตามและจัดการอุปกรณ์ของผู้ใช้ทั้งหมดในระบบ รวมถึงการควบคุมอุปกรณ์ที่ไว้วางใจ</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('super-admin.security.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> กลับ
                        </a>
                        <button type="button" class="btn btn-outline-primary" onclick="refreshStats()">
                            <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-gear me-1"></i> การจัดการ
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="revokeAllSuspiciousDevices()">
                                    <i class="bi bi-ban me-2"></i>เพิกถอนอุปกรณ์ที่น่าสงสัย
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="forceLogoutAllDevices()">
                                    <i class="bi bi-box-arrow-right me-2"></i>บังคับออกจากระบบทุกอุปกรณ์
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="cleanupOldDevices()">
                                    <i class="bi bi-trash3 me-2"></i>ล้างอุปกรณ์เก่า
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- สถิติการใช้งาน -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm bg-primary-subtle">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="mb-0 text-primary fw-bold">{{ $deviceStats['total'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">อุปกรณ์ทั้งหมด</p>
                                </div>
                                <i class="bi bi-phone text-primary fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm bg-success-subtle">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="mb-0 text-success fw-bold">{{ $deviceStats['trusted'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">อุปกรณ์ที่ไว้วางใจ</p>
                                </div>
                                <i class="bi bi-shield-check text-success fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm bg-warning-subtle">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="mb-0 text-warning fw-bold">{{ $deviceStats['suspicious'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">อุปกรณ์ที่น่าสงสัย</p>
                                </div>
                                <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm bg-info-subtle">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="mb-0 text-info fw-bold">{{ $deviceStats['online'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">ออนไลน์</p>
                                </div>
                                <i class="bi bi-wifi text-info fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ฟิลเตอร์และการค้นหา -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">ค้นหาผู้ใช้</label>
                                    <input type="text" class="form-control" id="searchUser" placeholder="ชื่อผู้ใช้ หรือ อีเมล">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">ประเภทอุปกรณ์</label>
                                    <select class="form-select" id="deviceType">
                                        <option value="">ทั้งหมด</option>
                                        <option value="desktop">เดสก์ท็อป</option>
                                        <option value="mobile">มือถือ</option>
                                        <option value="tablet">แท็บเล็ต</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">สถานะ</label>
                                    <select class="form-select" id="deviceStatus">
                                        <option value="">ทั้งหมด</option>
                                        <option value="trusted">ไว้วางใจ</option>
                                        <option value="untrusted">ไม่ไว้วางใจ</option>
                                        <option value="suspicious">น่าสงสัย</option>
                                        <option value="blocked">ถูกบล็อก</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">การจัดการ</label>
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                            <i class="bi bi-funnel me-1"></i> ค้นหา
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- รายการอุปกรณ์ -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-list text-primary me-2"></i>
                                รายการอุปกรณ์ทั้งหมด
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0 ps-4">ผู้ใช้</th>
                                            <th class="border-0">อุปกรณ์</th>
                                            <th class="border-0">ตำแหน่ง</th>
                                            <th class="border-0">การเข้าใช้ล่าสุด</th>
                                            <th class="border-0">สถานะ</th>
                                            <th class="border-0">การกระทำ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($devices ?? [] as $device)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="bi bi-person text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">{{ $device->user->username ?? 'N/A' }}</h6>
                                                        <small class="text-muted">{{ $device->user->email ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-{{ $device->device_type === 'mobile' ? 'phone' : ($device->device_type === 'tablet' ? 'tablet' : 'display') }} text-muted me-2"></i>
                                                    <div>
                                                        <div class="fw-semibold">{{ $device->device_name ?? 'Unknown Device' }}</div>
                                                        <small class="text-muted">{{ $device->browser ?? 'Unknown' }} • {{ $device->os ?? 'Unknown' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $device->country ?? 'Unknown' }}</div>
                                                    <small class="text-muted">{{ $device->ip_address ?? 'Unknown IP' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    {{ $device->last_used_at ? $device->last_used_at->format('d M Y, H:i') : 'Never' }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($device->is_trusted)
                                                    <span class="badge bg-success">ไว้วางใจ</span>
                                                @elseif($device->is_suspicious)
                                                    <span class="badge bg-warning">น่าสงสัย</span>
                                                @elseif($device->is_blocked)
                                                    <span class="badge bg-danger">ถูกบล็อก</span>
                                                @else
                                                    <span class="badge bg-secondary">ไม่ไว้วางใจ</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewDeviceDetails('{{ $device->id }}')" title="ดูรายละเอียด">
                                                        <i class="bi bi-info-circle"></i>
                                                    </button>
                                                    @if(!$device->is_trusted)
                                                    <button class="btn btn-sm btn-outline-success" onclick="trustDevice('{{ $device->id }}')" title="ไว้วางใจอุปกรณ์">
                                                        <i class="bi bi-shield-check"></i>
                                                    </button>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-warning" onclick="suspectDevice('{{ $device->id }}')" title="ทำเครื่องหมายว่าน่าสงสัย">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="blockDevice('{{ $device->id }}')" title="บล็อกอุปกรณ์">
                                                        <i class="bi bi-ban"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-phone fs-1"></i>
                                                    <p class="mt-3">ไม่พบข้อมูลอุปกรณ์</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
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

.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1) !important;
}
</style>

<script>
function refreshStats() {
    location.reload();
}

function applyFilters() {
    const user = document.getElementById('searchUser').value;
    const type = document.getElementById('deviceType').value;
    const status = document.getElementById('deviceStatus').value;
    
    // Apply filters logic here
    console.log('Applying filters:', { user, type, status });
}

function viewDeviceDetails(deviceId) {
    // Show device details modal
    alert('ดูรายละเอียดอุปกรณ์ ID: ' + deviceId);
}

function trustDevice(deviceId) {
    if (confirm('ไว้วางใจอุปกรณ์นี้?')) {
        // Trust device logic
        alert('อุปกรณ์ได้รับการไว้วางใจแล้ว');
    }
}

function suspectDevice(deviceId) {
    if (confirm('ทำเครื่องหมายอุปกรณ์นี้ว่าน่าสงสัย?')) {
        // Mark as suspicious logic
        alert('อุปกรณ์ถูกทำเครื่องหมายว่าน่าสงสัยแล้ว');
    }
}

function blockDevice(deviceId) {
    if (confirm('บล็อกอุปกรณ์นี้?')) {
        // Block device logic
        alert('อุปกรณ์ถูกบล็อกแล้ว');
    }
}

function revokeAllSuspiciousDevices() {
    if (confirm('เพิกถอนอุปกรณ์ที่น่าสงสัยทั้งหมด?')) {
        // Revoke all suspicious devices logic
        alert('เพิกถอนอุปกรณ์ที่น่าสงสัยทั้งหมดแล้ว');
    }
}

function forceLogoutAllDevices() {
    if (confirm('บังคับออกจากระบบทุกอุปกรณ์? การกระทำนี้จะส่งผลต่อผู้ใช้ทั้งหมด')) {
        // Force logout all devices logic
        alert('บังคับออกจากระบบทุกอุปกรณ์แล้ว');
    }
}

function cleanupOldDevices() {
    if (confirm('ล้างอุปกรณ์เก่าที่ไม่ใช้งานมานานแล้ว?')) {
        // Cleanup old devices logic
        alert('ล้างอุปกรณ์เก่าเสร็จสิ้น');
    }
}
</script>
@endsection
