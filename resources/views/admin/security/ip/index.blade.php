@extends('layouts.dashboard')

@section('title', 'ระบบจัดการ IP')

@section('head')
<style>
    .spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-globe text-primary me-2"></i>
                        ระบบจัดการ IP
                    </h1>
                    <p class="text-muted mb-0">จัดการข้อจำกัด IP รายชื่อที่อนุญาต และการควบคุมการเข้าถึงทางภูมิศาสตร์</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="refreshStats()">
                            <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIpModal">
                            <i class="bi bi-plus me-1"></i> เพิ่มกฎ IP
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-download me-1"></i> ส่งออก
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportIpRules('csv')">
                                    <i class="bi bi-file-earmark-text me-2"></i> ส่งออก CSV
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportIpRules('pdf')">
                                    <i class="bi bi-file-earmark-pdf me-2"></i> ส่งออก PDF
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-danger-subtle border-danger-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-shield-x text-danger fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['blocked_count'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">IP ที่ถูกบล็อก</p>
                            <small class="text-danger">
                                <i class="bi bi-arrow-up me-1"></i>{{ $statistics['blocked_today'] ?? 0 }} วันนี้
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-success-subtle border-success-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-shield-check text-success fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['allowed_count'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">IP ใน Whitelist</p>
                            <small class="text-success">
                                <i class="bi bi-shield-check me-1"></i>การเข้าถึงที่ปลอดภัย
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-warning-subtle border-warning-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-clock text-warning fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['temporary_count'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">การบล็อกชั่วคราว</p>
                            <small class="text-warning">
                                <i class="bi bi-hourglass-split me-1"></i>หมดอายุอัตโนมัติ
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-globe text-info fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['countries_blocked'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">ประเทศที่ถูกบล็อก</p>
                            <small class="text-info">
                                <i class="bi bi-flag me-1"></i>การควบคุมทางภูมิศาสตร์
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-funnel text-primary me-2"></i>
                                ตัวกรอง & ค้นหา
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                                <i class="bi bi-x me-1"></i> ล้างตัวกรอง
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" id="filterForm">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">ค้นหาที่อยู่ IP</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="192.168.1.1 หรือ 192.168.*"
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">สถานะ</label>
                                <select name="type" class="form-select">
                                    <option value="">ทุกประเภท</option>
                                    <option value="blocked" {{ request('type') == 'blocked' ? 'selected' : '' }}>ถูกบล็อก</option>
                                    <option value="allowed" {{ request('type') == 'allowed' ? 'selected' : '' }}>อนุญาต</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">ประเทศ</label>
                                <select name="country" class="form-select">
                                    <option value="">ทุกประเทศ</option>
                                    @foreach($countries ?? [] as $country)
                                    <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                                        {{ $country }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label fw-semibold">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search me-1"></i> ค้นหา
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- IP Rules Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-list text-primary me-2"></i>
                            ข้อจำกัด IP ({{ $ipRestrictions->total() ?? 0 }} รายการ)
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-warning" onclick="cleanupExpiredIPs()">
                                <i class="bi bi-trash me-1"></i> ล้างที่หมดอายุ
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        data-bs-toggle="dropdown">
                                    <i class="bi bi-gear me-1"></i> การดำเนินการแบบกลุ่ม
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="bulkDelete()">
                                        <i class="bi bi-trash me-2"></i> ลบที่เลือก
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkBlock()">
                                        <i class="bi bi-shield-x me-2"></i> บล็อกที่เลือก
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkAllow()">
                                        <i class="bi bi-shield-check me-2"></i> อนุญาตที่เลือก
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($ipRestrictions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th class="border-0">ที่อยู่ IP</th>
                                        <th class="border-0">ประเภท</th>
                                        <th class="border-0">ตำแหน่ง</th>
                                        <th class="border-0">เหตุผล</th>
                                        <th class="border-0">วันที่สร้าง</th>
                                        <th class="border-0">หมดอายุ</th>
                                        <th class="border-0">การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ipRestrictions as $ip)
                                    <tr>
                                        <td class="ps-4">
                                            <input type="checkbox" class="form-check-input ip-checkbox" 
                                                   value="{{ $ip->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm {{ $ip->type === 'blocked' ? 'bg-danger-subtle' : 'bg-success-subtle' }} rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="bi {{ $ip->type === 'blocked' ? 'bi-shield-x text-danger' : 'bi-shield-check text-success' }}"></i>
                                                </div>
                                                <div>
                                                    <code class="fw-bold">{{ $ip->ip_address }}</code>
                                                    @if($ip->is_range)
                                                        <span class="badge bg-info-subtle text-info ms-2">ช่วง</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($ip->type === 'blocked')
                                                <span class="badge bg-danger">ถูกบล็อก</span>
                                            @else
                                                <span class="badge bg-success">อนุญาต</span>
                                            @endif
                                            @if($ip->is_temporary)
                                                <span class="badge bg-warning-subtle text-warning ms-1">ชั่วคราว</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ip->country || $ip->city)
                                                <div class="d-flex align-items-center">
                                                    @if($ip->country)
                                                        <img src="https://flagcdn.com/16x12/{{ strtolower($ip->country_code ?? 'xx') }}.png" 
                                                             class="me-2" alt="{{ $ip->country }}">
                                                    @endif
                                                    <div>
                                                        <small class="fw-semibold">{{ $ip->country ?? 'ไม่ทราบ' }}</small>
                                                        @if($ip->city)
                                                            <br><small class="text-muted">{{ $ip->city }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">ไม่ทราบ</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted" title="{{ $ip->reason }}">
                                                {{ Str::limit($ip->reason ?? 'ไม่ได้ระบุเหตุผล', 30) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $ip->created_at->format('M d, H:i') }}
                                                <br>{{ $ip->created_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($ip->expires_at)
                                                @if($ip->expires_at->isPast())
                                                    <span class="badge bg-secondary">หมดอายุแล้ว</span>
                                                @else
                                                    <small class="text-warning">
                                                        {{ $ip->expires_at->format('M d, H:i') }}
                                                        <br>{{ $ip->expires_at->diffForHumans() }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge bg-primary">ถาวร</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($ip->type === 'blocked')
                                                    <form action="{{ route('admin.security.ip.allow', $ip) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                title="อนุญาต IP">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.security.ip.block', $ip) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                title="บล็อก IP">
                                                            <i class="bi bi-shield-x"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewIpDetails('{{ $ip->id }}')"
                                                        title="ดูรายละเอียด">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" 
                                                        onclick="editIp('{{ $ip->id }}')"
                                                        title="แก้ไข">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('admin.security.ip.destroy', $ip) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('ลบข้อจำกัด IP นี้หรือไม่?')"
                                                            title="ลบ">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($ipRestrictions->hasPages())
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            แสดง {{ $ipRestrictions->firstItem() }} ถึง {{ $ipRestrictions->lastItem() }} 
                                            จาก {{ $ipRestrictions->total() }} ผลลัพธ์
                                        </small>
                                    </div>
                                    <div>
                                        {{ $ipRestrictions->links() }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-globe text-primary fs-2"></i>
                            </div>
                            <h5 class="text-dark fw-bold mb-2">ไม่มีข้อจำกัด IP</h5>
                            <p class="text-muted mb-4">เริ่มปกป้องแอปพลิเคชันของคุณด้วยการเพิ่มข้อจำกัด IP</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIpModal">
                                <i class="bi bi-plus me-2"></i> เพิ่มกฎ IP แรก
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add IP Modal -->
<div class="modal fade" id="addIpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle text-primary me-2"></i>
                    เพิ่ม IP Restriction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.security.ip.store') }}" method="POST" id="addIpForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">ที่อยู่ IP <span class="text-danger">*</span></label>
                            <input type="text" name="ip_address" class="form-control" 
                                   placeholder="192.168.1.1 หรือ 192.168.1.0/24" required>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                รองรับ IP เดี่ยว, CIDR notation, หรือ wildcards (192.168.1.*)
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">ประเภท <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">เลือกประเภท</option>
                                <option value="blacklist">บล็อก (ปฏิเสธการเข้าถึง)</option>
                                <option value="whitelist">อนุญาต (Whitelist)</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">เหตุผล <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" 
                                      placeholder="เหตุผลสำหรับการจำกัด IP นี้..." required></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">คำอธิบายเพิ่มเติม</label>
                            <textarea name="description" class="form-control" rows="2" 
                                      placeholder="คำอธิบายเพิ่มเติม (ไม่บังคับ)"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_temporary" class="form-check-input" id="isTemporary">
                                <label class="form-check-label" for="isTemporary">
                                    จำกัดเวลา (Temporary)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3" id="expiresAtField" style="display: none;">
                            <label class="form-label fw-semibold">หมดอายุเมื่อ</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i> ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> เพิ่ม IP Restriction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    รายละเอียด IP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ipDetailsContent">
                <!-- Content loaded via AJAX -->
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

.border-danger-subtle {
    border-color: rgba(220, 53, 69, 0.2) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle expires field based on temporary checkbox
    const isTemporaryCheckbox = document.getElementById('isTemporary');
    const expiresAtField = document.getElementById('expiresAtField');
    
    isTemporaryCheckbox.addEventListener('change', function() {
        if (this.checked) {
            expiresAtField.style.display = 'block';
            // Set default expiry to 24 hours from now
            const now = new Date();
            now.setHours(now.getHours() + 24);
            const expires = now.toISOString().slice(0, 16);
            document.querySelector('input[name="expires_at"]').value = expires;
        } else {
            expiresAtField.style.display = 'none';
            document.querySelector('input[name="expires_at"]').value = '';
        }
    });

    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const ipCheckboxes = document.querySelectorAll('.ip-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        ipCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

function refreshStats() {
    location.reload();
}

function clearFilters() {
    const form = document.getElementById('filterForm');
    form.reset();
    form.submit();
}

function cleanupExpiredIPs() {
    if (confirm('คุณแน่ใจว่าต้องการล้างข้อจำกัด IP ที่หมดอายุทั้งหมดหรือไม่?')) {
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
                alert(`การล้างข้อมูลเสร็จสิ้น: ลบข้อจำกัดที่หมดอายุ ${data.count} รายการ`);
                location.reload();
            } else {
                alert('การล้างข้อมูลล้มเหลว');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการล้างข้อมูล');
        });
    }
}

function viewIpDetails(ipId) {
    const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
    const content = document.getElementById('ipDetailsContent');
    
    content.innerHTML = '<div class="text-center"><i class="bi bi-arrow-clockwise spin"></i> กำลังโหลด...</div>';
    modal.show();
    
    fetch(`{{ route('admin.security.ip.show', ':id') }}`.replace(':id', ipId))
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        });
}

function editIp(ipId) {
    // Implement edit functionality
    alert('ฟีเจอร์แก้ไขกำลังพัฒนา');
}

function exportIpRules(format) {
    window.open(`{{ route('admin.security.ip.export') }}?format=${format}`, '_blank');
}

function getSelectedIps() {
    const checkboxes = document.querySelectorAll('.ip-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function bulkDelete() {
    const selected = getSelectedIps();
    if (selected.length === 0) {
        alert('กรุณาเลือกข้อจำกัด IP ที่ต้องการลบ');
        return;
    }
    
    if (confirm(`ลบข้อจำกัด IP ${selected.length} รายการที่เลือกหรือไม่?`)) {
        // Implement bulk delete
        alert('ฟีเจอร์ลบแบบกลุ่มกำลังพัฒนา');
    }
}

function bulkBlock() {
    const selected = getSelectedIps();
    if (selected.length === 0) {
        alert('กรุณาเลือกข้อจำกัด IP ที่ต้องการบล็อก');
        return;
    }
    
    if (confirm(`บล็อก IP ${selected.length} รายการที่เลือกหรือไม่?`)) {
        // Implement bulk block
        alert('ฟีเจอร์บล็อกแบบกลุ่มกำลังพัฒนา');
    }
}

function bulkAllow() {
    const selected = getSelectedIps();
    if (selected.length === 0) {
        alert('กรุณาเลือกข้อจำกัด IP ที่ต้องการอนุญาต');
        return;
    }
    
    if (confirm(`อนุญาต IP ${selected.length} รายการที่เลือกหรือไม่?`)) {
        // Implement bulk allow
        alert('ฟีเจอร์อนุญาตแบบกลุ่มกำลังพัฒนา');
    }
}
</script>
@endsection
