@extends('layouts.dashboard')

@section('title', 'Active Sessions - ทดสอบ')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Active Sessions</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">จัดการ Active Sessions</h1>
            <p class="text-muted">ติดตาม Sessions ของผู้ใช้ที่กำลังใช้งานระบบ</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="refreshSessions()">
                <i class="bi bi-arrow-clockwise me-2"></i>รีเฟรช
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-funnel text-primary me-2"></i>ตัวกรองการค้นหา
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('super-admin.users.sessions') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">ค้นหา</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="ชื่อ, Email, หรือ IP Address">
                </div>
                
                <div class="col-md-3">
                    <label for="user_role" class="form-label">บทบาท</label>
                    <select class="form-select" id="user_role" name="user_role">
                        <option value="">ทั้งหมด</option>
                        <option value="super_admin" {{ request('user_role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin" {{ request('user_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ request('user_role') == 'user' ? 'selected' : '' }}>User</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">สถานะ</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">ทั้งหมด</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>ใช้งานอยู่</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>หมดอายุ</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sessions Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Sessions ทั้งหมด
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sessions->total() ?? 0 }}
                            </div>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-laptop" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                ใช้งานอยู่
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sessions->filter(function($session) { return $session->status == 'active'; })->count() ?? 0 }}
                            </div>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-wifi" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Super Admin
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sessions->filter(function($session) { return isset($session->user) && $session->user->role == 'super_admin'; })->count() ?? 0 }}
                            </div>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-shield-check" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Admin
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sessions->filter(function($session) { return isset($session->user) && $session->user->role == 'admin'; })->count() ?? 0 }}
                            </div>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-person-badge" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-list-ul text-primary me-2"></i>รายการ Sessions
            </h5>
        </div>
        <div class="card-body">
            @if($sessions && $sessions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ผู้ใช้</th>
                                <th>บทบาท</th>
                                <th>IP Address</th>
                                <th>เบราว์เซอร์</th>
                                <th>สถานะ</th>
                                <th>เข้าสู่ระบบเมื่อ</th>
                                <th>กิจกรรมล่าสุด</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $session->user->profile_image ? asset('storage/avatars/'.$session->user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($session->user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                                 class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <div>
                                                <div class="fw-bold">{{ $session->user->name }}</div>
                                                <div class="text-muted small">{{ $session->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->user->role == 'super_admin')
                                            <span class="badge bg-danger">Super Admin</span>
                                        @elseif($session->user->role == 'admin')
                                            <span class="badge bg-warning">Admin</span>
                                        @else
                                            <span class="badge bg-primary">User</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code>{{ $session->ip_address }}</code>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-browser-chrome me-1 text-muted"></i>
                                            <span class="small">{{ Str::limit($session->user_agent ?? 'Unknown', 30) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->status == 'active')
                                            <span class="badge bg-success">
                                                <i class="bi bi-wifi me-1"></i>ใช้งานอยู่
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-wifi-off me-1"></i>ไม่ใช้งาน
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            {{ $session->created_at ? $session->created_at->format('d/m/Y H:i') : '-' }}
                                            <div class="text-muted">{{ $session->created_at ? $session->created_at->diffForHumans() : '-' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            {{ $session->last_activity ? \Carbon\Carbon::parse($session->last_activity)->format('d/m/Y H:i') : '-' }}
                                            <div class="text-muted">{{ $session->last_activity ? \Carbon\Carbon::parse($session->last_activity)->diffForHumans() : '-' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-info" title="ดูรายละเอียด">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if($session->status == 'active')
                                                <button type="button" class="btn btn-outline-danger" title="ยุติ Session" 
                                                        onclick="terminateSession('{{ $session->id }}')">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $sessions->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-laptop text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">ไม่พบ Sessions</h5>
                    <p class="text-muted">ไม่มี Sessions ที่ตรงกับเกณฑ์การค้นหา</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Terminate Session Modal -->
<div class="modal fade" id="terminateSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการยุติ Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณแน่ใจหรือไม่ที่จะยุติ Session นี้? ผู้ใช้จะถูกบังคับออกจากระบบทันที</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" onclick="confirmTerminateSession()">
                    <i class="bi bi-x-circle me-1"></i>ยุติ Session
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let sessionToTerminate = null;

function refreshSessions() {
    window.location.reload();
}

function terminateSession(sessionId) {
    sessionToTerminate = sessionId;
    const modal = new bootstrap.Modal(document.getElementById('terminateSessionModal'));
    modal.show();
}

function confirmTerminateSession() {
    if (!sessionToTerminate) return;
    
    // สำหรับตอนนี้แค่ alert
    alert('ฟีเจอร์ยุติ Session จะพัฒนาในขั้นตอนต่อไป');
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('terminateSessionModal'));
    modal.hide();
    sessionToTerminate = null;
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto refresh every 30 seconds
    setInterval(function() {
        // Optional: Could implement AJAX refresh here
    }, 30000);
});
</script>
@endpush