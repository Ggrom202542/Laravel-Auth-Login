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
                        จัดการอุปกรณ์
                    </h1>
                    <p class="text-muted mb-0">จัดการอุปกรณ์ที่เชื่อถือและการตั้งค่าความปลอดภัย</p>
                </div>
                <div>
                    <a href="{{ route('user.security.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i> กลับไปยังความปลอดภัย
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshDevices()">
                        <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                    </button>
                </div>
            </div>

            <!-- Device Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-primary-subtle border-primary-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-phone text-primary fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $deviceStats['total_devices'] }}</h3>
                            <p class="text-muted mb-0">อุปกรณ์ทั้งหมด</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-success-subtle border-success-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-shield-check text-success fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $deviceStats['trusted_devices'] }}</h3>
                            <p class="text-muted mb-0">อุปกรณ์ที่เชื่อถือ</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-wifi text-info fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $deviceStats['active_devices'] }}</h3>
                            <p class="text-muted mb-0">อุปกรณ์ที่ใช้งาน</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devices List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-list-ul text-primary me-2"></i>
                        อุปกรณ์ของคุณ
                    </h6>
                </div>
                <div class="card-body">
                    @if($userDevices->count() > 0)
                        @foreach($userDevices as $device)
                        <div class="device-card border rounded p-3 mb-3" data-device-id="{{ $device->id }}">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-lg bg-{{ $device->is_trusted ? 'success' : 'secondary' }}-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="bi bi-{{ $device->device_type === 'mobile' ? 'phone' : ($device->device_type === 'tablet' ? 'tablet' : 'laptop') }} text-{{ $device->is_trusted ? 'success' : 'secondary' }} fs-3"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold">{{ $device->device_name ?? $device->browser_name }}</h6>
                                            <div class="text-muted small mb-1">
                                                {{ $device->operating_system }} • {{ $device->browser_name }} {{ $device->browser_version }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ $device->location ?? 'ตำแหน่งไม่ทราบ' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="mb-2">
                                        @if($device->is_trusted)
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="bi bi-shield-check me-1"></i>เชื่อถือได้
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">
                                                <i class="bi bi-exclamation-triangle me-1"></i>ยังไม่ยืนยัน
                                            </span>
                                        @endif
                                        
                                        @if($device->is_active)
                                            <span class="badge bg-info-subtle text-info">
                                                <i class="bi bi-circle-fill me-1"></i>ใช้งานอยู่
                                            </span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <div>เห็นครั้งแรก: {{ $device->first_seen_at?->format('j M, Y') }}</div>
                                        <div>เห็นล่าสุด: {{ $device->last_seen_at?->diffForHumans() }}</div>
                                        <div>จำนวนการเข้าสู่ระบบ: {{ $device->login_count ?? 0 }}</div>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-{{ $device->is_trusted ? 'warning' : 'success' }}" 
                                                onclick="toggleTrust({{ $device->id }}, {{ $device->is_trusted ? 'false' : 'true' }})">
                                            <i class="bi bi-{{ $device->is_trusted ? 'shield-slash' : 'shield-check' }} me-1"></i>
                                            {{ $device->is_trusted ? 'ยกเลิกการเชื่อถือ' : 'เชื่อถือ' }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmRemoveDevice({{ $device->id }}, '{{ $device->device_name ?? $device->browser_name }}')">
                                            <i class="bi bi-trash me-1"></i>ลบ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $userDevices->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-phone text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">ไม่มีอุปกรณ์ที่ลงทะเบียน</h5>
                            <p class="text-muted">อุปกรณ์ของคุณจะปรากฏที่นี่เมื่อคุณเข้าสู่ระบบจากอุปกรณ์ต่าง ๆ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Remove Device Modal -->
<div class="modal fade" id="removeDeviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ลบอุปกรณ์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณแน่ใจหรือไม่ว่าต้องการลบอุปกรณ์นี้?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>คำเตือน:</strong> การลบอุปกรณ์นี้จะต้องมีการยืนยันใหม่หากคุณเข้าสู่ระบบจากอุปกรณ์นี้อีกครั้ง
                </div>
                <div id="deviceInfo" class="bg-light p-3 rounded"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">
                    <i class="bi bi-trash me-1"></i>ลบอุปกรณ์
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .device-card {
        transition: all 0.2s ease-in-out;
        border: 1px solid #e9ecef !important;
    }
    .device-card:hover {
        border-color: #0d6efd !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .hover-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    .avatar-lg {
        width: 60px;
        height: 60px;
    }
</style>
@endpush

@push('scripts')
<script>
let deviceToRemove = null;

function refreshDevices() {
    location.reload();
}

function toggleTrust(deviceId, trusted) {
    fetch(`{{ route('user.security.devices') }}/${deviceId}/trust`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ trusted: trusted })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('เกิดข้อผิดพลาดในการอัปเดตสถานะความเชื่อถือของอุปกรณ์', 'error');
    });
}

function confirmRemoveDevice(deviceId, deviceName) {
    deviceToRemove = deviceId;
    document.getElementById('deviceInfo').innerHTML = `
        <strong>อุปกรณ์:</strong> ${deviceName}<br>
        <strong>ID อุปกรณ์:</strong> ${deviceId}
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('removeDeviceModal'));
    modal.show();
}

document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
    if (deviceToRemove) {
        fetch(`{{ route('user.security.devices') }}/${deviceToRemove}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('removeDeviceModal'));
                modal.hide();
                // Remove device card from DOM
                const deviceCard = document.querySelector(`[data-device-id="${deviceToRemove}"]`);
                if (deviceCard) {
                    deviceCard.remove();
                }
                deviceToRemove = null;
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('เกิดข้อผิดพลาดในการลบอุปกรณ์', 'error');
        });
    }
});

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
    }, 3000);
}
</script>
@endpush
@endsection
