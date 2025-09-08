@extends('layouts.app')

@section('title', 'สถานะรหัสผ่าน')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-primary text-white border-0">
                    <h4 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>
                        สถานะรหัสผ่านของคุณ
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <i class="bi bi-person-circle me-2 text-primary"></i>
                                <strong>ผู้ใช้:</strong> {{ $user->email }}
                            </div>
                            <div class="mb-3">
                                <i class="bi bi-toggle2-on me-2 text-success"></i>
                                <strong>เปิดใช้งาน Password Expiration:</strong> 
                                @if($user->password_expiration_enabled)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>เปิดใช้งาน
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i>ปิดใช้งาน
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($user->password_expires_at)
                                <div class="mb-3">
                                    <i class="bi bi-calendar-x me-2 text-warning"></i>
                                    <strong>รหัสผ่านหมดอายุ:</strong> 
                                    <span class="text-warning fw-bold">{{ $user->password_expires_at->format('d/m/Y H:i:s') }}</span>
                                </div>
                            @endif
                            @if($user->password_changed_at)
                                <div class="mb-3">
                                    <i class="bi bi-clock-history me-2 text-info"></i>
                                    <strong>เปลี่ยนรหัสผ่านล่าสุด:</strong> 
                                    <span class="text-info">{{ $user->password_changed_at->format('d/m/Y H:i:s') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Expiration Countdown Display -->
            @include('components.password-expiration-countdown')

            <!-- Testing Controls (Admin/Development) -->
            @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-tools me-2"></i>
                        เครื่องมือทดสอบ (สำหรับ Admin)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>คำแนะนำ:</strong> ใช้คำสั่งด้านล่างใน Terminal เพื่อทดสอบ Password Expiration
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">
                                <i class="bi bi-terminal me-2"></i>
                                คำสั่งทดสอบพื้นฐาน:
                            </h6>
                            <pre class="bg-dark text-light p-3 rounded"><code># ดูสถานะปัจจุบัน
php artisan test:password-expiration {{ $user->id }}

# ตั้งรหัสผ่านให้หมดอายุแล้ว
php artisan test:password-expiration {{ $user->id }} --expired

# ตั้งรหัสผ่านให้หมดอายุใน 3 วัน
php artisan test:password-expiration {{ $user->id }} --expiring</code></pre>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">
                                <i class="bi bi-gear me-2"></i>
                                คำสั่งทดสอบขั้นสูง:
                            </h6>
                            <pre class="bg-dark text-light p-3 rounded"><code># ตั้งให้หมดอายุใน 5 นาที (เห็นผลทันที)
php artisan test:password-expiration {{ $user->id }} --minutes=5

# ตั้งให้หมดอายุใน 1 นาที
php artisan test:password-expiration {{ $user->id }} --minutes=1

# รีเซ็ตกลับสู่ปกติ (90 วัน)
php artisan test:password-expiration {{ $user->id }} --reset</code></pre>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="text-info">
                            <i class="bi bi-list-ol me-2"></i>
                            ขั้นตอนการทดสอบ:
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <ol class="mb-0">
                                <li>เรียกใช้คำสั่ง <code class="bg-warning text-dark px-2 rounded">--minutes=5</code> เพื่อให้รหัสผ่านหมดอายุใน 5 นาที</li>
                                <li>รีเฟรชหน้านี้เพื่อดู countdown timer แบบเรียลไทม์</li>
                                <li>รอจนกว่า countdown จะถึง 0 เพื่อเห็นการเปลี่ยนแปลง</li>
                                <li>ลองเข้าหน้าอื่นเพื่อทดสอบ middleware redirect</li>
                                <li>ใช้ <code class="bg-success text-white px-2 rounded">--reset</code> เพื่อกลับสู่สถานะปกติ</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Live Status (Auto-refresh) -->
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-header bg-success text-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        สถานะแบบเรียลไทม์
                        <span class="badge bg-light text-dark ms-2" id="lastUpdated">กำลังโหลด...</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div id="liveStatus">
                        <div class="text-center text-muted">
                            <i class="bi bi-hourglass-split me-2"></i>
                            กำลังโหลดข้อมูล...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="text-center mt-4">
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('password.change') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-key me-2"></i>
                        เปลี่ยนรหัสผ่าน
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>
                        กลับไป Dashboard
                    </a>
                    <button class="btn btn-info btn-lg" onclick="refreshStatus()">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        รีเฟรชสถานะ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshStatus() {
    // แสดง loading state
    document.getElementById('liveStatus').innerHTML = 
        '<div class="text-center text-muted">' +
            '<i class="bi bi-hourglass-split me-2"></i>' +
            'กำลังโหลดข้อมูล...' +
        '</div>';
    
    fetch('/api/password/status', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            updateLiveStatus(data);
            document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString('th-TH');
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('liveStatus').innerHTML = 
                '<div class="alert alert-danger border-0">' +
                    '<i class="bi bi-exclamation-triangle me-2"></i>' +
                    'เกิดข้อผิดพลาดในการโหลดข้อมูล' +
                '</div>';
        });
}

function updateLiveStatus(data) {
    var statusHtml = 
        '<div class="row">' +
            '<div class="col-md-6">' +
                '<div class="card border-0 bg-light">' +
                    '<div class="card-body">' +
                        '<h6 class="card-title text-primary">' +
                            '<i class="bi bi-info-circle me-2"></i>' +
                            'สถานะระบบ' +
                        '</h6>' +
                        '<table class="table table-sm table-borderless">' +
                            '<tr>' +
                                '<td><i class="bi bi-toggle2-on me-2 text-success"></i><strong>Password Expiration Enabled:</strong></td>' +
                                '<td>' + (data.password_expiration_enabled ? 
                                    '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Yes</span>' : 
                                    '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>No</span>') + '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td><i class="bi bi-exclamation-triangle me-2 text-danger"></i><strong>Is Expired:</strong></td>' +
                                '<td>' + (data.is_expired ? 
                                    '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Yes</span>' : 
                                    '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>No</span>') + '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td><i class="bi bi-bell me-2 text-warning"></i><strong>Should Show Warning:</strong></td>' +
                                '<td>' + (data.should_show_warning ? 
                                    '<span class="badge bg-warning"><i class="bi bi-exclamation-triangle me-1"></i>Yes</span>' : 
                                    '<span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i>No</span>') + '</td>' +
                            '</tr>' +
                        '</table>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-md-6">' +
                '<div class="card border-0 bg-light">' +
                    '<div class="card-body">' +
                        '<h6 class="card-title text-info">' +
                            '<i class="bi bi-calendar-event me-2"></i>' +
                            'ข้อมูลเวลา' +
                        '</h6>' +
                        '<table class="table table-sm table-borderless">' +
                            '<tr>' +
                                '<td><i class="bi bi-hourglass me-2 text-primary"></i><strong>Days Until Expiration:</strong></td>' +
                                '<td><span class="badge bg-primary">' + (data.days_until_expiration !== null ? 
                                    data.days_until_expiration + ' วัน' : 'N/A') + '</span></td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td><i class="bi bi-calendar-x me-2 text-warning"></i><strong>Expires At:</strong></td>' +
                                '<td><small class="text-muted">' + (data.password_expires_at ? 
                                    new Date(data.password_expires_at).toLocaleString('th-TH') : 'ไม่ได้กำหนด') + '</small></td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td><i class="bi bi-clock-history me-2 text-info"></i><strong>Changed At:</strong></td>' +
                                '<td><small class="text-muted">' + (data.password_changed_at ? 
                                    new Date(data.password_changed_at).toLocaleString('th-TH') : 'ไม่ได้กำหนด') + '</small></td>' +
                            '</tr>' +
                        '</table>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    
    document.getElementById('liveStatus').innerHTML = statusHtml;
}

// Auto-refresh every 10 seconds
setInterval(refreshStatus, 10000);

// Initial load
document.addEventListener('DOMContentLoaded', refreshStatus);
</script>

<style>
/* Custom styling for password status page */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
}

pre {
    font-size: 0.85rem;
    border-radius: 8px !important;
}

.table td {
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
}

.gap-2 > * {
    margin: 0.25rem;
}

@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
    }
    
    .btn-lg {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

/* Loading animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.bi-hourglass-split {
    animation: spin 2s linear infinite;
}

/* Alert enhancements */
.alert {
    border-radius: 10px;
    border: none;
}
</style>
@endsection
