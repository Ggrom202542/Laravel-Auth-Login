@extends('layouts.dashboard')

@section('title', 'รายละเอียด Sessions - ' . $user->username)

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">รายละเอียด Sessions ของ {{ $user->username }}</h4>
                    <p class="text-muted mb-0">{{ $user->email }} - Role: {{ ucfirst($user->role) }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.sessions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>กลับ
                    </a>
                    <button type="button" class="btn btn-danger" onclick="forceLogoutAll()">
                        <i class="bi bi-slash-circle me-1"></i>บังคับออกจากระบบทั้งหมด
                    </button>
                </div>
            </div>

            <!-- สถิติ Sessions -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-pc-display fs-1 mb-2"></i>
                            <h5>{{ $statistics['total_sessions'] }}</h5>
                            <small>Total Sessions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-wifi fs-1 mb-2"></i>
                            <h5>{{ $statistics['online_sessions'] }}</h5>
                            <small>Online Sessions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-shield-check fs-1 mb-2"></i>
                            <h5>{{ $statistics['trusted_devices'] }}</h5>
                            <small>Trusted Devices</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                            <h5>{{ $statistics['suspicious_sessions'] }}</h5>
                            <small>Suspicious Sessions</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="sessionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" 
                                    data-bs-target="#active" type="button" role="tab">
                                <i class="bi bi-pc-display me-1"></i>Active Sessions ({{ $activeSessions->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" 
                                    data-bs-target="#history" type="button" role="tab">
                                <i class="bi bi-clock-history me-1"></i>History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" 
                                    data-bs-target="#logs" type="button" role="tab">
                                <i class="bi bi-list-ul me-1"></i>Session Logs
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="sessionTabContent">
                        <!-- Active Sessions Tab -->
                        <div class="tab-pane fade show active" id="active" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>อุปกรณ์</th>
                                            <th>ตำแหน่ง</th>
                                            <th>IP Address</th>
                                            <th>เข้าสู่ระบบ</th>
                                            <th>กิจกรรมล่าสุด</th>
                                            <th>สถานะ</th>
                                            <th>การดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activeSessions as $session)
                                        <tr>
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
                                                    @if($session->isOnline())
                                                        <span class="badge bg-success mb-1">Online</span>
                                                    @else
                                                        <span class="badge bg-warning mb-1">Idle</span>
                                                    @endif
                                                    
                                                    @if($session->is_trusted)
                                                        <span class="badge bg-success">Trusted</span>
                                                    @else
                                                        <span class="badge bg-warning">Untrusted</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="terminateSession('{{ $session->session_id }}')">
                                                        <i class="bi bi-box-arrow-right"></i> Terminate
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-pc-display display-4 text-muted mb-3"></i>
                                                <p class="text-muted">ไม่มี active sessions</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- History Tab -->
                        <div class="tab-pane fade" id="history" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>อุปกรณ์</th>
                                            <th>IP Address</th>
                                            <th>เข้าสู่ระบบ</th>
                                            <th>ออกจากระบบ</th>
                                            <th>ระยะเวลา</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sessionHistory as $session)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi {{ $session->getDeviceIcon() }} me-2"></i>
                                                    <div>
                                                        <span>{{ $session->device_info }}</span>
                                                        <br>
                                                        <small class="text-muted">{{ $session->platform }} - {{ $session->browser }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="font-monospace">{{ $session->ip_address }}</span>
                                            </td>
                                            <td>
                                                {{ $session->login_at->format('d/m/Y H:i:s') }}
                                            </td>
                                            <td>
                                                @if($session->logout_at)
                                                    {{ $session->logout_at->format('d/m/Y H:i:s') }}
                                                @else
                                                    <span class="text-muted">ยังไม่ได้ออกจากระบบ</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($session->logout_at)
                                                    {{ $session->logout_at->diffInMinutes($session->login_at) }} นาที
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($session->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Closed</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="bi bi-clock-history display-4 text-muted mb-3"></i>
                                                <p class="text-muted">ไม่มีประวัติ sessions</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Logs Tab -->
                        <div class="tab-pane fade" id="logs" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>เวลา</th>
                                            <th>กิจกรรม</th>
                                            <th>IP Address</th>
                                            <th>รายละเอียด</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sessionLogs as $log)
                                        <tr>
                                            <td>
                                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                                            </td>
                                            <td>
                                                <span class="badge {{ $log->getActionBadgeClass() }}">
                                                    {{ $log->getActionLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-monospace">{{ $log->ip_address }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $log->details }}</small>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i class="bi bi-list-ul display-4 text-muted mb-3"></i>
                                                <p class="text-muted">ไม่มี session logs</p>
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

<!-- Modal: Terminate Session -->
<div class="modal fade" id="terminateSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terminate Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="terminateSessionForm">
                    @csrf
                    <input type="hidden" id="session_id" name="session_id">
                    <div class="mb-3">
                        <label for="reason" class="form-label">เหตุผลในการปิด Session</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" 
                                  placeholder="ระบุเหตุผลในการปิด session นี้..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" onclick="submitTerminateSession()">
                    <i class="bi bi-box-arrow-right me-1"></i>Terminate Session
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Force Logout All -->
<div class="modal fade" id="forceLogoutAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">บังคับออกจากระบบทั้งหมด</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    การดำเนินการนี้จะทำให้ผู้ใช้ออกจากระบบในทุกอุปกรณ์ทันที
                </div>
                <form id="forceLogoutAllForm">
                    @csrf
                    <div class="mb-3">
                        <label for="logout_reason" class="form-label">เหตุผลในการบังคับออกจากระบบ</label>
                        <textarea class="form-control" id="logout_reason" name="reason" rows="3" required
                                  placeholder="ระบุเหตุผลในการบังคับออกจากระบบ..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" onclick="submitForceLogoutAll()">
                    <i class="bi bi-slash-circle me-1"></i>บังคับออกจากระบบทั้งหมด
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let terminateModal, forceLogoutAllModal;

document.addEventListener('DOMContentLoaded', function() {
    terminateModal = new bootstrap.Modal(document.getElementById('terminateSessionModal'));
    forceLogoutAllModal = new bootstrap.Modal(document.getElementById('forceLogoutAllModal'));
});

function terminateSession(sessionId) {
    document.getElementById('session_id').value = sessionId;
    terminateModal.show();
}

function forceLogoutAll() {
    forceLogoutAllModal.show();
}

function submitTerminateSession() {
    const form = document.getElementById('terminateSessionForm');
    const formData = new FormData(form);
    
    fetch('{{ route("admin.sessions.terminate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            terminateModal.hide();
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

function submitForceLogoutAll() {
    const form = document.getElementById('forceLogoutAllForm');
    const formData = new FormData(form);
    
    fetch('{{ route("admin.sessions.force-logout", $user) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            forceLogoutAllModal.hide();
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

// Auto refresh every 30 seconds
setInterval(function() {
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
</script>
@endpush

@push('styles')
<style>
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

.nav-tabs .nav-link {
    color: #495057;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    font-weight: 600;
}
</style>
@endpush
