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
            <a href="{{ route('admin.roles.permissions') }}" class="btn btn-info">
                <i class="bi bi-list-check me-1"></i>สิทธิ์การใช้งาน
            </a>
            <a href="{{ route('admin.roles.history') }}" class="btn btn-secondary">
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

    <!-- Available Roles -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-info-circle me-2"></i>บทบาทและสิทธิ์ที่มีในระบบ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($availableRoles as $roleKey => $role)
                            <div class="col-md-4 mb-3">
                                <div class="card border-{{ $role['color'] }} h-100">
                                    <div class="card-header bg-{{ $role['color'] }} text-white">
                                        <h6 class="mb-0">
                                            <i class="bi bi-shield-check me-2"></i>{{ $role['name'] }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-3">{{ $role['description'] }}</p>
                                        <h6 class="text-{{ $role['color'] }} mb-2">สิทธิ์การใช้งาน:</h6>
                                        <ul class="list-unstyled small">
                                            @foreach($role['permissions'] as $permission)
                                                <li class="mb-1">
                                                    <i class="bi bi-check-circle text-success me-1"></i>{{ $permission }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-search me-2"></i>ค้นหาและจัดการผู้ใช้
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.roles.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">ค้นหาผู้ใช้</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" 
                           placeholder="ชื่อ, นามสกุล, หรืออีเมล">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">กรองตามบทบาท</label>
                    <select class="form-control" id="role" name="role">
                        <option value="">ทั้งหมด</option>
                        <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>รีเซ็ต
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-people me-2"></i>รายการผู้ใช้งาน
            </h6>
            <button class="btn btn-sm btn-warning" onclick="openBulkUpdateModal()">
                <i class="bi bi-pencil-square me-1"></i>แก้ไขหลายคน
            </button>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <form id="bulkForm">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
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
                                            @if($user->id !== $currentUser->id)
                                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                                       class="form-check-input user-checkbox">
                                            @endif
                                        </td>
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
                </form>

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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkModal" tabindex="-1" aria-labelledby="bulkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkUpdateForm" method="POST" action="{{ route('admin.roles.bulk-update') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>แก้ไขบทบาทหลายคน
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>ผู้ใช้ที่เลือก:</strong></label>
                        <div id="selectedUsers" class="form-control-plaintext border rounded p-2 bg-light"></div>
                    </div>
                    <div class="mb-3">
                        <label for="bulkRole" class="form-label">บทบาทใหม่ <span class="text-danger">*</span></label>
                        <select class="form-control" id="bulkRole" name="role" required>
                            <option value="">เลือกบทบาท</option>
                            <option value="user">User (ผู้ใช้งาน)</option>
                            <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                            <option value="super_admin">Super Admin (ผู้ดูแลระบบสูงสุด)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bulkReason" class="form-label">เหตุผลในการเปลี่ยนแปลง <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="bulkReason" name="reason" rows="3" 
                                  placeholder="กรอกเหตุผลในการเปลี่ยนบทบาท..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-1"></i>อัปเดตทั้งหมด
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Role Modal Management
function openRoleModal(userId, userName, currentRole) {
    document.getElementById('userName').textContent = userName;
    document.getElementById('currentRole').innerHTML = getRoleBadge(currentRole);
    document.getElementById('roleForm').action = `/admin/roles/${userId}/update-role`;
    document.getElementById('newRole').value = '';
    document.getElementById('reason').value = '';
    
    new bootstrap.Modal(document.getElementById('roleModal')).show();
}

function getRoleBadge(role) {
    const badges = {
        'super_admin': '<span class="badge bg-danger">Super Admin</span>',
        'admin': '<span class="badge bg-primary">Admin</span>',
        'user': '<span class="badge bg-success">User</span>'
    };
    return badges[role] || role;
}

// Bulk Selection Management
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

function openBulkUpdateModal() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        alert('กรุณาเลือกผู้ใช้อย่างน้อย 1 คน');
        return;
    }
    
    // Get selected user names
    const selectedNames = [];
    const selectedIds = [];
    
    checkedBoxes.forEach(checkbox => {
        selectedIds.push(checkbox.value);
        const row = checkbox.closest('tr');
        const userName = row.querySelector('td:nth-child(2) .font-weight-bold').textContent.trim();
        selectedNames.push(userName);
    });
    
    // Add hidden inputs for selected user IDs
    const bulkForm = document.getElementById('bulkUpdateForm');
    const existingInputs = bulkForm.querySelectorAll('input[name="user_ids[]"]');
    existingInputs.forEach(input => input.remove());
    
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_ids[]';
        input.value = id;
        bulkForm.appendChild(input);
    });
    
    // Display selected users
    document.getElementById('selectedUsers').innerHTML = selectedNames.join('<br>');
    
    // Reset form
    document.getElementById('bulkRole').value = '';
    document.getElementById('bulkReason').value = '';
    
    new bootstrap.Modal(document.getElementById('bulkModal')).show();
}

// Form Validation
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

document.getElementById('bulkUpdateForm').addEventListener('submit', function(e) {
    const bulkRole = document.getElementById('bulkRole').value;
    const bulkReason = document.getElementById('bulkReason').value;
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    
    if (!bulkRole || !bulkReason.trim()) {
        e.preventDefault();
        alert('กรุณากรอกข้อมูลให้ครบถ้วน');
        return;
    }
    
    if (!confirm(`คุณแน่ใจหรือไม่ที่จะเปลี่ยนบทบาทของผู้ใช้ ${checkedBoxes.length} คน?`)) {
        e.preventDefault();
    }
});
</script>
@endpush

@endsection