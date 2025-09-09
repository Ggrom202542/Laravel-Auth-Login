@extends('layouts.dashboard')

@section('title', 'จัดการ Sessions - Admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-people me-2"></i>จัดการ Sessions
                    </h4>
                    <div class="btn-group">
                        <a href="{{ route('admin.sessions.report') }}" class="btn btn-info">
                            <i class="bi bi-bar-chart me-1"></i>รายงาน
                        </a>
                        <button type="button" class="btn btn-warning" onclick="cleanupExpired()">
                            <i class="bi bi-trash me-1"></i>ล้าง Sessions หมดอายุ
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- สถิติ Sessions -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center py-3">
                                    <h6>{{ $statistics['total_sessions'] }}</h6>
                                    <small>Total Sessions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center py-3">
                                    <h6>{{ $statistics['active_sessions'] }}</h6>
                                    <small>Active Sessions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center py-3">
                                    <h6>{{ $statistics['unique_users'] }}</h6>
                                    <small>Unique Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center py-3">
                                    <h6>{{ $statistics['online_users'] }}</h6>
                                    <small>Online Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center py-3">
                                    <h6>{{ $statistics['trusted_devices'] }}</h6>
                                    <small>Trusted Devices</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center py-3">
                                    <h6>{{ $statistics['suspicious_sessions'] }}</h6>
                                    <small>Suspicious</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ตัวกรอง -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="search" 
                                           value="{{ $search }}" placeholder="ค้นหาผู้ใช้หรือ IP">
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="days" class="form-select">
                                        <option value="1" {{ $days == 1 ? 'selected' : '' }}>1 วัน</option>
                                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 วัน</option>
                                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 วัน</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> ค้นหา
                                    </button>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="{{ route('admin.sessions.export', ['format' => 'csv', 'days' => $days]) }}" 
                                       class="btn btn-outline-success">
                                        <i class="bi bi-download me-1"></i>Export CSV
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ตาราง Sessions -->
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th style="color: #FFF">ผู้ใช้</th>
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
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $session->user->avatar ?? asset('images/default-avatar.png') }}" 
                                                 class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                            <div>
                                                <strong>{{ $session->user->username }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $session->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi {{ $session->getDeviceIcon() }} me-2"></i>
                                            <div>
                                                <small>{{ $session->device_info }}</small>
                                                <br>
                                                <small class="text-muted">{{ $session->platform }} - {{ $session->browser }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->location)
                                            <i class="bi bi-geo-alt me-1"></i>
                                            <small>{{ $session->location }}</small>
                                        @else
                                            <small class="text-muted">ไม่ทราบ</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-monospace small">{{ $session->ip_address }}</span>
                                    </td>
                                    <td>
                                        <small title="{{ $session->login_at->format('d/m/Y H:i:s') }}">
                                            {{ $session->login_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        <small title="{{ $session->last_activity->format('d/m/Y H:i:s') }}">
                                            {{ $session->last_activity->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($session->isOnline())
                                                <span class="badge bg-success mb-1">Online</span>
                                            @elseif($session->is_active)
                                                <span class="badge bg-warning mb-1">Idle</span>
                                            @else
                                                <span class="badge bg-secondary mb-1">Offline</span>
                                            @endif
                                            
                                            @if($session->is_trusted)
                                                <span class="badge bg-success">Trusted</span>
                                            @else
                                                <span class="badge bg-warning">Untrusted</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.sessions.show', $session->user) }}" 
                                               class="btn btn-outline-info" title="ดูรายละเอียด">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($session->is_active)
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="terminateSession('{{ $session->session_id }}')" 
                                                    title="ปิด Session">
                                                <i class="bi bi-box-arrow-right"></i>
                                            </button>
                                            @endif
                                            <button type="button" class="btn btn-outline-warning" 
                                                    onclick="forceLogout({{ $session->user->id }})" 
                                                    title="บังคับออกจากระบบทั้งหมด">
                                                <i class="bi bi-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">ไม่พบ sessions ตามเงื่อนไขที่ระบุ</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($sessions->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $sessions->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: ปิด Session -->
<div class="modal fade" id="terminateSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ปิด Session</h5>
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
                    <i class="bi bi-box-arrow-right me-1"></i>ปิด Session
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: บังคับออกจากระบบ -->
<div class="modal fade" id="forceLogoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">บังคับออกจากระบบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    การดำเนินการนี้จะทำให้ผู้ใช้ออกจากระบบในทุกอุปกรณ์
                </div>
                <form id="forceLogoutForm">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="mb-3">
                        <label for="logout_reason" class="form-label">เหตุผลในการบังคับออกจากระบบ</label>
                        <textarea class="form-control" id="logout_reason" name="reason" rows="3" required
                                  placeholder="ระบุเหตุผลในการบังคับออกจากระบบ..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" onclick="submitForceLogout()">
                    <i class="bi bi-ban me-1"></i>บังคับออกจากระบบ
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let terminateModal, forceLogoutModal;

document.addEventListener('DOMContentLoaded', function() {
    terminateModal = new bootstrap.Modal(document.getElementById('terminateSessionModal'));
    forceLogoutModal = new bootstrap.Modal(document.getElementById('forceLogoutModal'));
});

function terminateSession(sessionId) {
    document.getElementById('session_id').value = sessionId;
    terminateModal.show();
}

function forceLogout(userId) {
    document.getElementById('user_id').value = userId;
    forceLogoutModal.show();
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

function submitForceLogout() {
    const form = document.getElementById('forceLogoutForm');
    const formData = new FormData(form);
    const userId = document.getElementById('user_id').value;
    
    fetch(`/admin/users/${userId}/force-logout`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            forceLogoutModal.hide();
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

function cleanupExpired() {
    if (confirm('คุณต้องการล้าง sessions ที่หมดอายุทั้งหมดหรือไม่?')) {
        fetch('{{ route("admin.sessions.cleanup") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
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

// Auto refresh every 30 seconds
setInterval(function() {
    // Only refresh if no modal is open
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
</script>
@endpush

@push('styles')
<style>
.table-responsive {
    font-size: 0.85rem;
}

.font-monospace {
    font-family: 'Courier New', monospace;
}

.badge {
    font-size: 0.7rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn-group-sm .btn {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.table td {
    vertical-align: middle;
}

.table th {
    font-weight: 600;
    font-size: 0.8rem;
}

.card-body.py-3 {
    padding: 0.75rem 1rem;
}
</style>
@endpush
