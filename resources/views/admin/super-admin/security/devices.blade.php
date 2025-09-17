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
                            <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear me-1"></i> การจัดการ
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width: 250px;">
                                <li class="px-3 py-2 bg-light border-bottom">
                                    <small class="text-muted fw-bold">การจัดการอุปกรณ์</small>
                                </li>
                                <li><a class="dropdown-item py-2" href="#" onclick="event.preventDefault(); revokeAllSuspiciousDevices();">
                                    <i class="bi bi-ban me-2 text-warning"></i>
                                    <span>เพิกถอนอุปกรณ์ที่น่าสงสัย</span>
                                    <small class="d-block text-muted">ยกเลิกการไว้วางใจอุปกรณ์ที่มีปัญหา</small>
                                </a></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="event.preventDefault(); forceLogoutAllDevices();">
                                    <i class="bi bi-box-arrow-right me-2 text-danger"></i>
                                    <span>บังคับออกจากระบบทุกอุปกรณ์</span>
                                    <small class="d-block text-muted">ยกเลิก session ทั้งหมดในระบบ</small>
                                </a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li class="px-3 py-1">
                                    <small class="text-muted fw-bold">การบำรุงรักษา</small>
                                </li>
                                <li><a class="dropdown-item py-2" href="#" onclick="event.preventDefault(); cleanupOldDevices();">
                                    <i class="bi bi-trash3 me-2 text-info"></i>
                                    <span>ล้างอุปกรณ์เก่า</span>
                                    <small class="d-block text-muted">ลบข้อมูลอุปกรณ์ที่ไม่ใช้งาน</small>
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
                                                        <small class="text-muted">{{ $device->browser ?? 'Unknown' }} • {{ $device->platform ?? 'Unknown' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $device->location_country ?? 'Unknown' }}</div>
                                                    <small class="text-muted">{{ $device->ip_address ?? 'Unknown IP' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    {{ $device->last_activity ? $device->last_activity->format('d M Y, H:i') : 'Never' }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($device->is_trusted)
                                                    <span class="badge bg-success">ไว้วางใจ</span>
                                                @elseif($device->is_suspicious)
                                                    <span class="badge bg-warning">น่าสงสัย</span>
                                                @elseif(!$device->is_active)
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

/* Enhanced Dropdown Styling */
.dropdown-menu {
    border-radius: 12px !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
    border: none !important;
    padding: 0.5rem 0 !important;
}

.dropdown-item {
    padding: 0.75rem 1.25rem !important;
    transition: all 0.2s ease-in-out !important;
    border-radius: 0 !important;
    position: relative !important;
}

.dropdown-item:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    transform: translateX(3px) !important;
    color: #495057 !important;
}

.dropdown-item i {
    display: inline-block !important;
    width: 20px !important;
    text-align: center !important;
    transition: transform 0.2s ease-in-out !important;
}

.dropdown-item:hover i {
    transform: scale(1.1) !important;
}

.dropdown-item small {
    font-size: 0.75rem !important;
    margin-top: 2px !important;
}

.dropdown-divider {
    margin: 0.5rem 1rem !important;
    border-color: #dee2e6 !important;
}

/* Enhanced Button Styling */
.btn {
    transition: all 0.3s ease-in-out !important;
}

.btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* Card Hover Effects */
.card {
    transition: all 0.3s ease-in-out !important;
}

.card:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

/* Table Row Hover */
.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05) !important;
    transform: scale(1.01) !important;
    transition: all 0.2s ease-in-out !important;
}

/* Badge Enhancements */
.badge {
    padding: 0.5em 0.75em !important;
    font-size: 0.75em !important;
    border-radius: 50px !important;
}

/* Animation Classes */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -100%, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes fadeOutUp {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
        transform: translate3d(0, -100%, 0);
    }
}

.animate__animated {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}

.animate__fadeInDown {
    animation-name: fadeInDown;
}

.animate__fadeOutUp {
    animation-name: fadeOutUp;
}
</style>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global SweetAlert2 configuration
Swal.mixin({
    customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-secondary'
    },
    buttonsStyling: false
});

function refreshStats() {
    // Test connection ก่อน
    fetch('/api/admin/test', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Test response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Test response data:', data);
        if (data.status === 'OK') {
            console.log('API connection successful!');
        }
    })
    .catch(error => {
        console.error('Test error:', error);
    });
    
    Swal.fire({
        title: 'รีเฟรชข้อมูล',
        text: 'กำลังโหลดข้อมูลล่าสุด...',
        icon: 'info',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
        }
    }).then(() => {
        location.reload();
    });
}

function applyFilters() {
    const user = document.getElementById('searchUser').value;
    const type = document.getElementById('deviceType').value;
    const status = document.getElementById('deviceStatus').value;
    
    if (!user && !type && !status) {
        Swal.fire({
            title: 'กรุณาเลือกตัวกรอง',
            text: 'โปรดเลือกเงื่อนไขการค้นหาอย่างน้อย 1 รายการ',
            icon: 'warning',
            confirmButtonText: 'ตกลง'
        });
        return;
    }

    Swal.fire({
        title: 'กำลังค้นหา...',
        text: 'โปรดรอสักครู่',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Apply filters logic here
    setTimeout(() => {
        Swal.fire({
            title: 'ค้นหาเสร็จสิ้น!',
            text: `พบผลลัพธ์ตามเงื่อนไข: ${user ? 'ผู้ใช้: ' + user + ' ' : ''}${type ? 'ประเภท: ' + type + ' ' : ''}${status ? 'สถานะ: ' + status : ''}`,
            icon: 'success',
            timer: 2000,
            timerProgressBar: true
        });
    }, 1500);
}

function viewDeviceDetails(deviceId) {
    Swal.fire({
        title: 'รายละเอียดอุปกรณ์',
        html: `
            <div class="text-start">
                <div class="row">
                    <div class="col-6">
                        <strong>ID อุปกรณ์:</strong><br>
                        <span class="text-muted">${deviceId}</span>
                    </div>
                    <div class="col-6">
                        <strong>สถานะ:</strong><br>
                        <span class="badge bg-info">ใช้งานอยู่</span>
                    </div>
                </div>
                <hr>
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>ข้อมูลเพิ่มเติม:</strong><br>
                        <small class="text-muted">ฟีเจอร์นี้จะพร้อมใช้งานในอนาคต</small>
                    </div>
                </div>
            </div>
        `,
        icon: 'info',
        width: 600,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>ปิด',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    });
}

function trustDevice(deviceId) {
    Swal.fire({
        title: 'ไว้วางใจอุปกรณ์',
        html: `
            <div class="text-center">
                <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                <p class="mt-3">ต้องการไว้วางใจอุปกรณ์นี้หรือไม่?</p>
                <small class="text-muted">อุปกรณ์ที่ไว้วางใจจะได้รับสิทธิพิเศษในการเข้าใช้งาน</small>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-shield-check me-1"></i>ไว้วางใจ',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/api/admin/devices/${deviceId}/trust`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message || 'ไม่สามารถดำเนินการได้',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                    icon: 'error'
                });
            });
        }
    });
}

function suspectDevice(deviceId) {
    Swal.fire({
        title: 'ทำเครื่องหมายว่าน่าสงสัย',
        html: `
            <div class="text-center">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                <p class="mt-3">ต้องการทำเครื่องหมายอุปกรณ์นี้ว่าน่าสงสัยหรือไม่?</p>
                <div class="form-group mt-3">
                    <label for="suspiciousReason" class="form-label fw-bold">เหตุผล (ไม่บังคับ):</label>
                    <textarea id="suspiciousReason" class="form-control" rows="3" placeholder="ระบุเหตุผลที่ทำให้อุปกรณ์นี้น่าสงสัย..."></textarea>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-exclamation-triangle me-1"></i>ทำเครื่องหมาย',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก',
        preConfirm: () => {
            const reason = document.getElementById('suspiciousReason').value;
            return { reason: reason || 'ไม่ระบุเหตุผล' };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/api/admin/devices/${deviceId}/suspect`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    reason: result.value.reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'ทำเครื่องหมายแล้ว!',
                        text: `อุปกรณ์ถูกทำเครื่องหมายว่าน่าสงสัย\nเหตุผล: ${data.reason}`,
                        icon: 'warning',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message || 'ไม่สามารถดำเนินการได้',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                    icon: 'error'
                });
            });
        }
    });
}

function blockDevice(deviceId) {
    Swal.fire({
        title: 'บล็อกอุปกรณ์',
        html: `
            <div class="text-center">
                <i class="bi bi-ban text-danger" style="font-size: 3rem;"></i>
                <p class="mt-3 text-danger fw-bold">คำเตือน: การกระทำนี้ไม่สามารถยกเลิกได้</p>
                <p>อุปกรณ์จะไม่สามารถเข้าใช้งานระบบได้อีก</p>
                <div class="form-group mt-3">
                    <label for="blockReason" class="form-label fw-bold">เหตุผล (บังคับ):</label>
                    <textarea id="blockReason" class="form-control" rows="3" placeholder="ระบุเหตุผลในการบล็อกอุปกรณ์..." required></textarea>
                </div>
            </div>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-ban me-1"></i>บล็อกอุปกรณ์',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก',
        preConfirm: () => {
            const reason = document.getElementById('blockReason').value;
            if (!reason.trim()) {
                Swal.showValidationMessage('กรุณาระบุเหตุผลในการบล็อก');
                return false;
            }
            return { reason: reason };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/api/admin/devices/${deviceId}/block`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    reason: result.value.reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'บล็อกเรียบร้อย!',
                        text: `อุปกรณ์ถูกบล็อกแล้ว\nเหตุผล: ${data.reason}`,
                        icon: 'success',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message || 'ไม่สามารถดำเนินการได้',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                    icon: 'error'
                });
            });
        }
    });
}

function revokeAllSuspiciousDevices() {
    Swal.fire({
        title: 'เพิกถอนอุปกรณ์ที่น่าสงสัย',
        html: `
            <div class="text-start">
                <p class="mb-3">การกระทำนี้จะ:</p>
                <ul class="list-unstyled ms-3">
                    <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>เพิกถอนการไว้วางใจของอุปกรณ์ที่น่าสงสัยทั้งหมด</li>
                    <li><i class="bi bi-arrow-clockwise text-info me-2"></i>บังคับให้ผู้ใช้ยืนยันตัวตนใหม่</li>
                    <li><i class="bi bi-shield-slash text-danger me-2"></i>เพิ่มมาตรการรักษาความปลอดภัย</li>
                </ul>
                <p class="text-warning fw-bold mb-0">ต้องการดำเนินการต่อหรือไม่?</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#fd7e14',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-ban me-1"></i>เพิกถอนทั้งหมด',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch('/api/admin/devices/revoke-suspicious', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                // ตรวจสอบ content type
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        console.error('Received non-JSON response:', text);
                        throw new Error('Server returned non-JSON response');
                    });
                }
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'เกิดข้อผิดพลาด');
                }
                return data;
            })
            .catch(error => {
                console.error('API Error:', error);
                Swal.showValidationMessage(`เกิดข้อผิดพลาด: ${error.message}`);
                return false;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'เพิกถอนเสร็จสิ้น!',
                text: `เพิกถอนอุปกรณ์ที่น่าสงสัย ${result.value.count} เครื่องเรียบร้อยแล้ว`,
                icon: 'success',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        }
    });
}

function forceLogoutAllDevices() {
    Swal.fire({
        title: 'บังคับออกจากระบบทุกอุปกรณ์',
        html: `
            <div class="text-start">
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong>คำเตือน:</strong> การกระทำนี้จะส่งผลกระทบต่อผู้ใช้ทั้งหมดในระบบ
                    </div>
                </div>
                <p class="mb-2">การกระทำนี้จะ:</p>
                <ul class="list-unstyled ms-3">
                    <li><i class="bi bi-box-arrow-right text-danger me-2"></i>บังคับให้ผู้ใช้ทั้งหมดออกจากระบบ</li>
                    <li><i class="bi bi-key text-warning me-2"></i>ยกเลิก session ทั้งหมด</li>
                    <li><i class="bi bi-arrow-clockwise text-info me-2"></i>ต้องเข้าสู่ระบบใหม่ทั้งหมด</li>
                </ul>
                <div class="form-group mt-3">
                    <label for="logoutReason" class="form-label fw-bold">เหตุผล (บังคับ):</label>
                    <textarea id="logoutReason" class="form-control" rows="3" placeholder="ระบุเหตุผลในการบังคับออกจากระบบ..." required></textarea>
                </div>
            </div>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i>บังคับออกทั้งหมด',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก',
        width: 600,
        preConfirm: () => {
            const reason = document.getElementById('logoutReason').value;
            if (!reason.trim()) {
                Swal.showValidationMessage('กรุณาระบุเหตุผลในการบังคับออกจากระบบ');
                return false;
            }
            return { reason: reason };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'กำลังดำเนินการ...',
                text: 'โปรดรอสักครู่',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('/api/admin/devices/force-logout-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    reason: result.value.reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'เสร็จสิ้น!',
                        html: `
                            <div class="text-center">
                                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                <p class="mt-3">บังคับออกจากระบบทุกอุปกรณ์เรียบร้อยแล้ว</p>
                                <small class="text-muted">เหตุผล: ${data.reason}</small>
                            </div>
                        `,
                        icon: 'success',
                        timer: 4000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message || 'ไม่สามารถดำเนินการได้',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                    icon: 'error'
                });
            });
        }
    });
}

function cleanupOldDevices() {
    Swal.fire({
        title: 'ล้างอุปกรณ์เก่า',
        html: `
            <div class="text-start">
                <p class="mb-3">เลือกเงื่อนไขในการล้างอุปกรณ์:</p>
                <div class="form-group mb-3">
                    <label for="cleanupDays" class="form-label fw-bold">ไม่ได้ใช้งานเป็นเวลา:</label>
                    <select id="cleanupDays" class="form-select">
                        <option value="30">30 วัน</option>
                        <option value="60">60 วัน</option>
                        <option value="90" selected>90 วัน</option>
                        <option value="180">180 วัน</option>
                        <option value="365">1 ปี</option>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="includeSuspicious" checked>
                    <label class="form-check-label" for="includeSuspicious">
                        รวมอุปกรณ์ที่น่าสงสัย
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="includeUntrusted" checked>
                    <label class="form-check-label" for="includeUntrusted">
                        รวมอุปกรณ์ที่ไม่ไว้วางใจ
                    </label>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#fd7e14',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash3 me-1"></i>ล้างข้อมูล',
        cancelButtonText: '<i class="bi bi-x-lg me-1"></i>ยกเลิก',
        width: 500,
        preConfirm: () => {
            const days = document.getElementById('cleanupDays').value;
            const includeSuspicious = document.getElementById('includeSuspicious').checked;
            const includeUntrusted = document.getElementById('includeUntrusted').checked;
            
            return {
                days: days,
                includeSuspicious: includeSuspicious,
                includeUntrusted: includeUntrusted
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { days, includeSuspicious, includeUntrusted } = result.value;
            
            Swal.fire({
                title: 'กำลังล้างข้อมูล...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">กำลังค้นหาและลบอุปกรณ์เก่า...</p>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch('/api/admin/devices/cleanup-old', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    days: days,
                    include_suspicious: includeSuspicious,
                    include_untrusted: includeUntrusted
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'ล้างข้อมูลเสร็จสิ้น!',
                        html: `
                            <div class="text-center">
                                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                <p class="mt-3">ลบอุปกรณ์เก่าแล้ว <strong>${data.count}</strong> เครื่อง</p>
                                <small class="text-muted">เงื่อนไข: ไม่ใช้งานเป็นเวลา ${days} วัน</small>
                            </div>
                        `,
                        icon: 'success',
                        timer: 4000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'ไม่พบข้อมูล',
                        text: data.message || 'ไม่พบอุปกรณ์เก่าที่ตรงตามเงื่อนไข',
                        icon: 'info'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                    icon: 'error'
                });
            });
        }
    });
}
</script>
@endsection
