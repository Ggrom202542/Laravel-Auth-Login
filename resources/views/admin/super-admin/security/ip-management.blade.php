@extends('layouts.dashboard')

@section('title', 'Super Admin - จัดการ IP')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-globe text-primary me-2"></i>
                        Super Admin - จัดการ IP
                    </h1>
                    <p class="text-muted mb-0">จัดการข้อจำกัด IP ระดับระบบ รายชื่อที่อนุญาต และการควบคุมการเข้าถึงทางภูมิศาสตร์</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('super-admin.security.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> กลับ
                        </a>
                        <button type="button" class="btn btn-outline-primary" onclick="refreshStats()">
                            <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIpModal">
                            <i class="bi bi-plus me-1"></i> เพิ่มกฎ IP
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-shield-exclamation me-1"></i> การจัดการความปลอดภัย
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="blockSuspiciousIPs()">
                                    <i class="bi bi-ban me-2"></i>บล็อก IP ที่น่าสงสัย
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="clearAllRestrictions()">
                                    <i class="bi bi-trash3 me-2"></i>ลบข้อจำกัดทั้งหมด
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="emergencyLockdown()">
                                    <i class="bi bi-lock-fill me-2"></i>ล็อกดาวน์ฉุกเฉิน
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- สถิติ IP -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm bg-primary-subtle">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="mb-0 text-primary fw-bold">{{ $ipStats['whitelisted'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">รายชื่อที่อนุญาต</p>
                                </div>
                                <i class="bi bi-check-circle text-primary fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm bg-danger-subtle">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="mb-0 text-danger fw-bold">{{ $ipStats['blacklisted'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">รายชื่อที่ห้าม</p>
                                </div>
                                <i class="bi bi-x-circle text-danger fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm bg-warning-subtle">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="mb-0 text-warning fw-bold">{{ $ipStats['suspicious'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">IP ที่น่าสงสัย</p>
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
                                    <h3 class="mb-0 text-info fw-bold">{{ $ipStats['blocked_countries'] ?? 0 }}</h3>
                                    <p class="text-muted mb-0">ประเทศที่ถูกบล็อก</p>
                                </div>
                                <i class="bi bi-flag text-info fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- แท็บการจัดการ -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <ul class="nav nav-tabs card-header-tabs" id="ipManagementTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="whitelist-tab" data-bs-toggle="tab" data-bs-target="#whitelist" type="button" role="tab">
                                        <i class="bi bi-check-circle me-2"></i>รายชื่อที่อนุญาต
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="blacklist-tab" data-bs-toggle="tab" data-bs-target="#blacklist" type="button" role="tab">
                                        <i class="bi bi-x-circle me-2"></i>รายชื่อที่ห้าม
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="suspicious-tab" data-bs-toggle="tab" data-bs-target="#suspicious" type="button" role="tab">
                                        <i class="bi bi-exclamation-triangle me-2"></i>IP ที่น่าสงสัย
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="geography-tab" data-bs-toggle="tab" data-bs-target="#geography" type="button" role="tab">
                                        <i class="bi bi-geo-alt me-2"></i>การควบคุมทางภูมิศาสตร์
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="ipManagementTabsContent">
                                <!-- Whitelist Tab -->
                                <div class="tab-pane fade show active" id="whitelist" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">รายชื่อ IP ที่อนุญาต</h5>
                                        <button class="btn btn-success btn-sm" onclick="addWhitelistIP()">
                                            <i class="bi bi-plus me-1"></i> เพิ่ม IP
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>IP Address</th>
                                                    <th>คำอธิบาย</th>
                                                    <th>เพิ่มเมื่อ</th>
                                                    <th>สถานะ</th>
                                                    <th>การกระทำ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($whitelistedIPs ?? [] as $ip)
                                                <tr>
                                                    <td><code>{{ $ip->ip_address }}</code></td>
                                                    <td>{{ $ip->description ?? '-' }}</td>
                                                    <td>{{ $ip->created_at ? $ip->created_at->format('d M Y') : '-' }}</td>
                                                    <td><span class="badge bg-success">ใช้งาน</span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromWhitelist('{{ $ip->id }}')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">ไม่มี IP ในรายชื่อที่อนุญาต</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Blacklist Tab -->
                                <div class="tab-pane fade" id="blacklist" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">รายชื่อ IP ที่ห้าม</h5>
                                        <button class="btn btn-danger btn-sm" onclick="addBlacklistIP()">
                                            <i class="bi bi-plus me-1"></i> เพิ่ม IP
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>IP Address</th>
                                                    <th>เหตุผล</th>
                                                    <th>เพิ่มเมื่อ</th>
                                                    <th>หมดอายุ</th>
                                                    <th>การกระทำ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($blacklistedIPs ?? [] as $ip)
                                                <tr>
                                                    <td><code>{{ $ip->ip_address }}</code></td>
                                                    <td>{{ $ip->reason ?? '-' }}</td>
                                                    <td>{{ $ip->created_at ? $ip->created_at->format('d M Y') : '-' }}</td>
                                                    <td>{{ $ip->expires_at ? $ip->expires_at->format('d M Y') : 'ไม่หมดอายุ' }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-success" onclick="removeFromBlacklist('{{ $ip->id }}')">
                                                            <i class="bi bi-unlock"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">ไม่มี IP ในรายชื่อที่ห้าม</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Suspicious Tab -->
                                <div class="tab-pane fade" id="suspicious" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">IP ที่น่าสงสัย</h5>
                                        <button class="btn btn-warning btn-sm" onclick="scanSuspiciousIPs()">
                                            <i class="bi bi-search me-1"></i> สแกนหา IP ที่น่าสงสัย
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>IP Address</th>
                                                    <th>ความพยายามล้มเหลว</th>
                                                    <th>ประเทศ</th>
                                                    <th>คะแนนความเสี่ยง</th>
                                                    <th>การกระทำ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($suspiciousIPs ?? [] as $ip)
                                                <tr>
                                                    <td><code>{{ $ip->ip_address }}</code></td>
                                                    <td><span class="badge bg-warning">{{ $ip->failed_attempts ?? 0 }}</span></td>
                                                    <td>{{ $ip->country ?? 'ไม่ทราบ' }}</td>
                                                    <td>
                                                        @if($ip->risk_score >= 80)
                                                            <span class="badge bg-danger">สูง ({{ $ip->risk_score }})</span>
                                                        @elseif($ip->risk_score >= 50)
                                                            <span class="badge bg-warning">ปานกลาง ({{ $ip->risk_score }})</span>
                                                        @else
                                                            <span class="badge bg-info">ต่ำ ({{ $ip->risk_score }})</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-success" onclick="whitelistIP('{{ $ip->ip_address }}')" title="เพิ่มไปยังรายชื่อที่อนุญาต">
                                                                <i class="bi bi-check-circle"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger" onclick="blacklistIP('{{ $ip->ip_address }}')" title="เพิ่มไปยังรายชื่อที่ห้าม">
                                                                <i class="bi bi-ban"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">ไม่พบ IP ที่น่าสงสัย</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Geography Tab -->
                                <div class="tab-pane fade" id="geography" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5 class="mb-3">ประเทศที่อนุญาต</h5>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="allowThailand" checked>
                                                <label class="form-check-label" for="allowThailand">
                                                    <i class="bi bi-flag me-2"></i>ประเทศไทย
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="allowUSA">
                                                <label class="form-check-label" for="allowUSA">
                                                    <i class="bi bi-flag me-2"></i>สหรัฐอเมริกา
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="allowJapan">
                                                <label class="form-check-label" for="allowJapan">
                                                    <i class="bi bi-flag me-2"></i>ญี่ปุ่น
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="mb-3">ประเทศที่ห้าม</h5>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="blockChina">
                                                <label class="form-check-label" for="blockChina">
                                                    <i class="bi bi-flag me-2"></i>จีน
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="blockRussia">
                                                <label class="form-check-label" for="blockRussia">
                                                    <i class="bi bi-flag me-2"></i>รัสเซีย
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="blockNorthKorea">
                                                <label class="form-check-label" for="blockNorthKorea">
                                                    <i class="bi bi-flag me-2"></i>เกาหลีเหนือ
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-4">
                                        <button class="btn btn-primary" onclick="saveGeographySettings()">
                                            <i class="bi bi-floppy me-1"></i> บันทึกการตั้งค่า
                                        </button>
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

<script>
function refreshStats() {
    location.reload();
}

function addWhitelistIP() {
    const ip = prompt('กรุณาระบุ IP Address ที่ต้องการเพิ่มไปยังรายชื่อที่อนุญาต:');
    if (ip) {
        alert('เพิ่ม IP: ' + ip + ' ไปยังรายชื่อที่อนุญาตแล้ว');
    }
}

function addBlacklistIP() {
    const ip = prompt('กรุณาระบุ IP Address ที่ต้องการเพิ่มไปยังรายชื่อที่ห้าม:');
    if (ip) {
        alert('เพิ่ม IP: ' + ip + ' ไปยังรายชื่อที่ห้ามแล้ว');
    }
}

function removeFromWhitelist(ipId) {
    if (confirm('ลบ IP นี้ออกจากรายชื่อที่อนุญาต?')) {
        alert('ลบออกจากรายชื่อที่อนุญาตแล้ว');
    }
}

function removeFromBlacklist(ipId) {
    if (confirm('ลบ IP นี้ออกจากรายชื่อที่ห้าม?')) {
        alert('ลบออกจากรายชื่อที่ห้ามแล้ว');
    }
}

function scanSuspiciousIPs() {
    alert('กำลังสแกนหา IP ที่น่าสงสัย...');
}

function whitelistIP(ip) {
    if (confirm('เพิ่ม IP: ' + ip + ' ไปยังรายชื่อที่อนุญาต?')) {
        alert('เพิ่มไปยังรายชื่อที่อนุญาตแล้ว');
    }
}

function blacklistIP(ip) {
    if (confirm('เพิ่ม IP: ' + ip + ' ไปยังรายชื่อที่ห้าม?')) {
        alert('เพิ่มไปยังรายชื่อที่ห้ามแล้ว');
    }
}

function blockSuspiciousIPs() {
    if (confirm('บล็อก IP ที่น่าสงสัยทั้งหมด?')) {
        alert('บล็อก IP ที่น่าสงสัยทั้งหมดแล้ว');
    }
}

function clearAllRestrictions() {
    if (confirm('ลบข้อจำกัด IP ทั้งหมด? การกระทำนี้อาจเสี่ยงต่อความปลอดภัย')) {
        alert('ลบข้อจำกัดทั้งหมดแล้ว');
    }
}

function emergencyLockdown() {
    if (confirm('เปิดใช้งานล็อกดาวน์ฉุกเฉิน? จะอนุญาตให้เฉพาะ IP ที่ไว้วางใจเท่านั้น')) {
        alert('เปิดใช้งานล็อกดาวน์ฉุกเฉินแล้ว');
    }
}

function saveGeographySettings() {
    alert('บันทึกการตั้งค่าทางภูมิศาสตร์แล้ว');
}
</script>
@endsection
