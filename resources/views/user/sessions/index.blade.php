@extends('layouts.dashboard')

@section('title', 'จัดการ Sessions')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-laptop me-2"></i>Sessions ของคุณ
                    </h4>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#logoutOtherDevicesModal">
                        <i class="bi bi-box-arrow-right me-1"></i>ออกจากอุปกรณ์อื่น
                    </button>
                </div>
                <div class="card-body">
                    <!-- สถิติ Session -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="bi bi-laptop fa-2x mb-2"></i>
                                    <h5>{{ $statistics['total_sessions'] }}</h5>
                                    <small>Total Sessions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="bi bi-wifi fa-2x mb-2"></i>
                                    <h5>{{ $statistics['online_devices'] }}</h5>
                                    <small>Online Devices</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="bi bi-shield-check fa-2x mb-2"></i>
                                    <h5>{{ $statistics['trusted_devices'] }}</h5>
                                    <small>Trusted Devices</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="bi bi-exclamation-triangle fa-2x mb-2"></i>
                                    <h5>{{ $statistics['suspicious_sessions'] }}</h5>
                                    <small>Suspicious Sessions</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- รายการ Sessions -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th style="color: #FFF">อุปกรณ์</th>
                                    <th style="color: #FFF">ตำแหน่ง</th>
                                    <th style="color: #FFF">IP Address</th>
                                    <th style="color: #FFF">เข้าสู่ระบบ</th>
                                    <th style="color: #FFF">กิจกรรมล่าสุด</th>
                                    <th style="color: #FFF">สถานะ</th>
                                    <th style="color: #FFF">การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                <tr class="{{ $session->session_id === session()->getId() ? 'table-success' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi {{ $session->getDeviceIcon() }} me-2"></i>
                                            <div>
                                                <strong>{{ $session->device_info }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $session->platform }} - {{ $session->browser }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->location)
                                            <i class="bi bi-geo-alt me-1"></i>{{ $session->location }}
                                        @else
                                            <span class="text-muted">ไม่ทราบ</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-monospace">{{ $session->ip_address }}</span>
                                    </td>
                                    <td>
                                        <span title="{{ $session->login_at->format('d/m/Y H:i:s') }}">
                                            {{ $session->login_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span title="{{ $session->last_activity->format('d/m/Y H:i:s') }}">
                                            {{ $session->last_activity->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($session->session_id === session()->getId())
                                                <span class="badge bg-success mb-1">Session ปัจจุบัน</span>
                                            @elseif($session->isOnline())
                                                <span class="badge bg-info mb-1">Online</span>
                                            @else
                                                <span class="badge bg-secondary mb-1">Offline</span>
                                            @endif
                                            
                                            @if($session->is_trusted)
                                                <span class="badge bg-success">อุปกรณ์ที่เชื่อถือ</span>
                                            @else
                                                <span class="badge bg-warning">อุปกรณ์ที่ไม่เชื่อถือ</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->session_id !== session()->getId())
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="terminateSession('{{ $session->session_id }}')">
                                                    <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                                                </button>
                                                @if(!$session->is_trusted)
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="trustDevice('{{ $session->session_id }}')">
                                                    <i class="bi bi-shield-check"></i> เชื่อถืออุปกรณ์
                                                </button>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">Session ปัจจุบัน</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-laptop fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">ไม่พบ sessions ที่ใช้งาน</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($sessions->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $sessions->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: ออกจากอุปกรณ์อื่น -->
<div class="modal fade" id="logoutOtherDevicesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ออกจากอุปกรณ์อื่นทั้งหมด</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>คำเตือน:</strong> การดำเนินการนี้จะทำให้คุณออกจากระบบในอุปกรณ์อื่นทั้งหมด
                </div>
                <form id="logoutOtherDevicesForm">
                    @csrf
                    <div class="mb-3">
                        <label for="password" class="form-label">ยืนยันรหัสผ่านเพื่อดำเนินการ</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" onclick="submitLogoutOtherDevices()">
                    <i class="bi bi-box-arrow-right me-1"></i>ออกจากอุปกรณ์อื่น
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function terminateSession(sessionId) {
    if (confirm('คุณต้องการออกจากระบบ session นี้หรือไม่?')) {
        fetch('{{ route("user.sessions.terminate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                session_id: sessionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        });
    }
}

function trustDevice(sessionId) {
    if (confirm('คุณต้องการเชื่อถืออุปกรณ์นี้หรือไม่?')) {
        fetch('{{ route("user.sessions.trust") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                session_id: sessionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        });
    }
}

function submitLogoutOtherDevices() {
    const form = document.getElementById('logoutOtherDevicesForm');
    const formData = new FormData(form);
    
    fetch('{{ route("user.sessions.logout-others") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('logoutOtherDevicesModal')).hide();
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}

// Auto refresh every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endpush

@push('styles')
<style>
.table-responsive {
    font-size: 0.9rem;
}

.font-monospace {
    font-family: 'Courier New', monospace;
}

.badge {
    font-size: 0.75rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn-group-sm .btn {
    font-size: 0.75rem;
}

.table td {
    vertical-align: middle;
}
</style>
@endpush
