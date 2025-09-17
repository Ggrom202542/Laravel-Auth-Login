@extends('layouts.dashboard')

@section('title', 'จัดการบทบาทและสิทธิ์')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-shield-check me-2"></i>จัดการบทบาทและสิทธิ์
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.roles.permissions') }}" class="btn btn-info">
                <i class="bi bi-list-check me-1"></i>สิทธิ์การใช้งาน
            </a>
            <a href="{{ route('super-admin.roles.history') }}" class="btn btn-secondary">
                <i class="bi bi-clock-history me-1"></i>ประวัติการเปลี่ยนแปลง
            </a>
        </div>
    </div>

    <!-- Role Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Super Admin
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $roleStats['super_admin'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-shield-fill-exclamation fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Admin
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $roleStats['admin'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-badge fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                User
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $roleStats['user'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                รวมทั้งหมด
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ array_sum($roleStats) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-lines-fill fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-people me-2"></i>รายการผู้ใช้งาน
            </h6>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ผู้ใช้</th>
                                <th>อีเมล</th>
                                <th>บทบาทปัจจุบัน</th>
                                <th>วันที่สมัคร</th>
                                <th>การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="rounded-circle me-2" 
                                                 src="{{ $user->profile_image ? asset('storage/avatars/'.$user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($user->first_name.' '.$user->last_name).'&color=7F9CF5&background=EBF4FF' }}" 
                                                 alt="{{ $user->first_name }} {{ $user->last_name }}" 
                                                 style="width: 40px; height: 40px;">
                                            <div>
                                                <div class="font-weight-bold">
                                                    {{ $user->prefix }}{{ $user->first_name }} {{ $user->last_name }}
                                                    @if($user->id === $currentUser->id)
                                                        <span class="badge bg-info ms-1">คุณ</span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted">ID: {{ $user->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @php
                                            $roleColor = match($user->role) {
                                                'super_admin' => 'danger',
                                                'admin' => 'primary',
                                                'user' => 'success',
                                                default => 'secondary'
                                            };
                                            $roleName = match($user->role) {
                                                'super_admin' => 'Super Admin',
                                                'admin' => 'Admin',
                                                'user' => 'User',
                                                default => $user->role
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $roleColor }}">{{ $roleName }}</span>
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($user->id !== $currentUser->id)
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="openRoleModal({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}', '{{ $user->role }}')">
                                                <i class="bi bi-pencil me-1"></i>แก้ไข
                                            </button>
                                        @else
                                            <span class="text-muted small">ไม่สามารถแก้ไขตนเองได้</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $users->appends(request()->query())->links() }}
            @else
                <div class="text-center py-4">
                    <i class="bi bi-people fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">ไม่พบผู้ใช้งาน</h5>
                    <p class="text-muted">ลองเปลี่ยนเงื่อนไขการค้นหา</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Role Change Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="roleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>เปลี่ยนบทบาทผู้ใช้
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>ผู้ใช้:</strong></label>
                        <div id="userName" class="form-control-plaintext"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>บทบาทปัจจุบัน:</strong></label>
                        <div id="currentRole" class="form-control-plaintext"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newRole" class="form-label">บทบาทใหม่ <span class="text-danger">*</span></label>
                        <select class="form-control" id="newRole" name="role" required>
                            <option value="">เลือกบทบาท</option>
                            <option value="user">User (ผู้ใช้งาน)</option>
                            <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                            <option value="super_admin">Super Admin (ผู้ดูแลระบบสูงสุด)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">เหตุผลในการเปลี่ยนแปลง <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" 
                                  placeholder="กรอกเหตุผลในการเปลี่ยนบทบาท..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i>ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Simple modal management
function openRoleModal(userId, userName, currentRole) {
    console.log('🔧 Opening modal for user:', userName);
    
    // Set form data
    document.getElementById('userName').textContent = userName;
    document.getElementById('currentRole').innerHTML = getRoleBadge(currentRole);
    document.getElementById('roleForm').action = `/super-admin/roles/${userId}/update-role`;
    document.getElementById('newRole').value = '';
    document.getElementById('reason').value = '';
    
    // Show modal using Bootstrap 5
    const modal = new bootstrap.Modal(document.getElementById('roleModal'));
    modal.show();
    console.log('✅ Modal shown');
}

function getRoleBadge(role) {
    const badges = {
        'super_admin': '<span class="badge bg-danger">Super Admin</span>',
        'admin': '<span class="badge bg-primary">Admin</span>',
        'user': '<span class="badge bg-success">User</span>'
    };
    return badges[role] || role;
}

// Form validation
document.getElementById('roleForm').addEventListener('submit', function(e) {
    const newRole = document.getElementById('newRole').value;
    const reason = document.getElementById('reason').value;
    
    if (!newRole || !reason.trim()) {
        e.preventDefault();
        alert('กรุณากรอกข้อมูลให้ครบถ้วน');
        return;
    }
    
    if (!confirm('คุณแน่ใจหรือไม่ที่จะเปลี่ยนบทบาทของผู้ใช้นี้?')) {
        e.preventDefault();
    }
});
</script>

@endsection