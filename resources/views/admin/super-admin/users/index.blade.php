@extends('layouts.dashboard')

@section('title', 'Super Admin - จัดการผู้ใช้ระบบ')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-people-fill"></i> จัดการผู้ใช้ระบบ (Super Admin)
        </h1>
        <div>
            <a href="{{ route('super-admin.users.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="bi bi-plus-lg"></i> เพิ่มผู้ใช้ใหม่
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">ผู้ใช้ทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">ผู้ใช้ที่ใช้งานได้</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Admin</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['admin_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-badge fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Super Admin</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['super_admin_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-shield-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">2FA เปิดใช้งาน</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['two_fa_enabled']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-shield-lock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">เข้าสู่ระบบ 7 วันล่าสุด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['recent_logins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">เซสชันที่ใช้งาน</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_sessions']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-display fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">ตัวกรองและค้นหา</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('super-admin.users.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">ค้นหา</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="ชื่อ, อีเมล, ชื่อผู้ใช้..." 
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="role_filter" class="form-label">บทบาท</label>
                        <select class="form-control" id="role_filter" name="role_filter">
                            <option value="">ทั้งหมด</option>
                            <option value="user" {{ request('role_filter') == 'user' ? 'selected' : '' }}>ผู้ใช้</option>
                            <option value="admin" {{ request('role_filter') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="super_admin" {{ request('role_filter') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="status_filter" class="form-label">สถานะ</label>
                        <select class="form-control" id="status_filter" name="status_filter">
                            <option value="">ทั้งหมด</option>
                            <option value="active" {{ request('status_filter') == 'active' ? 'selected' : '' }}>ใช้งานได้</option>
                            <option value="inactive" {{ request('status_filter') == 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
                            <option value="suspended" {{ request('status_filter') == 'suspended' ? 'selected' : '' }}>ระงับการใช้งาน</option>
                            <option value="pending" {{ request('status_filter') == 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="two_fa_filter" class="form-label">2FA</label>
                        <select class="form-control" id="two_fa_filter" name="two_fa_filter">
                            <option value="">ทั้งหมด</option>
                            <option value="enabled" {{ request('two_fa_filter') == 'enabled' ? 'selected' : '' }}>เปิดใช้งาน</option>
                            <option value="disabled" {{ request('two_fa_filter') == 'disabled' ? 'selected' : '' }}>ปิดใช้งาน</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex" style="gap: 10px;">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="bi bi-search"></i> ค้นหา
                            </button>
                            <a href="{{ route('super-admin.users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> ล้าง
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">รายชื่อผู้ใช้</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">เรียงตามไฟล์:</div>
                    <a class="dropdown-item" href="?sort_by=name&sort_order=asc">ชื่อ (ก-ฮ)</a>
                    <a class="dropdown-item" href="?sort_by=name&sort_order=desc">ชื่อ (ฮ-ก)</a>
                    <a class="dropdown-item" href="?sort_by=created_at&sort_order=desc">วันที่สร้างล่าสุด</a>
                    <a class="dropdown-item" href="?sort_by=created_at&sort_order=asc">วันที่สร้างเก่าที่สุด</a>
                    <a class="dropdown-item" href="?sort_by=last_login_at&sort_order=desc">เข้าสู่ระบบล่าสุด</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow: visible;">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>อีเมล</th>
                            <th>บทบาท</th>
                            <th>สถานะ</th>
                            <th>2FA</th>
                            <th>เซสชัน</th>
                            <th>เข้าสู่ระบบล่าสุด</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td style="text-align: center;">{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        @if($user->profile_image)
                                            <img class="rounded-circle" src="{{ asset('storage/profiles/'.$user->profile_image) }}" 
                                                 style="width: 40px; height: 40px;" alt="{{ $user->name }}">
                                        @else
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                                 style="width: 40px; height: 40px; font-size: 14px;">
                                                {{ substr($user->name, 0, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td style="text-align: center;">
                                @if($user->role === 'super_admin')
                                    <span class="badge badge-danger">
                                        <i class="bi bi-shield-check"></i> Super Admin
                                    </span>
                                @elseif($user->role === 'admin')
                                    <span class="badge badge-warning">
                                        <i class="bi bi-person-badge"></i> Admin
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="bi bi-person"></i> ผู้ใช้
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @switch($user->status)
                                    @case('active')
                                        <span class="badge badge-success">
                                            <i class="bi bi-check-circle-fill"></i> ใช้งานได้
                                        </span>
                                        @break
                                    @case('inactive')
                                        <span class="badge badge-secondary">
                                            <i class="bi bi-pause-circle-fill"></i> ไม่ใช้งาน
                                        </span>
                                        @break
                                    @case('suspended')
                                        <span class="badge badge-danger">
                                            <i class="bi bi-x-circle-fill"></i> ระงับการใช้งาน
                                        </span>
                                        @break
                                    @case('pending')
                                        <span class="badge badge-info">
                                            <i class="bi bi-clock-fill"></i> รออนุมัติ
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                @if($user->google2fa_enabled)
                                    <span class="badge badge-success">
                                        <i class="bi bi-shield-lock-fill"></i> เปิด
                                    </span>
                                @else
                                    <span class="badge badge-light">
                                        <i class="bi bi-shield-slash"></i> ปิด
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $activeSessions = $user->adminSessions->where('status', 'active')->count();
                                @endphp
                                @if($activeSessions > 0)
                                    <span class="badge badge-success">{{ $activeSessions }}</span>
                                @else
                                    <span class="badge badge-light">0</span>
                                @endif
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    @php
                                        // แปลงเป็น Carbon object ถ้าเป็น string
                                        $lastLogin = is_string($user->last_login_at) 
                                            ? \Carbon\Carbon::parse($user->last_login_at) 
                                            : $user->last_login_at;
                                    @endphp
                                    <div class="text-sm">{{ $lastLogin->format('d/m/Y') }}</div>
                                    <div class="text-xs text-muted">{{ $lastLogin->format('H:i:s') }}</div>
                                @else
                                    <span class="text-muted">ยังไม่เคยเข้าสู่ระบบ</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                            data-bs-toggle="dropdown" aria-expanded="false" 
                                            data-bs-boundary="window"
                                            data-bs-auto-close="outside">
                                        <i class="bi bi-gear-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('super-admin.users.show', $user->id) }}">
                                                <i class="bi bi-eye-fill"></i> ดูข้อมูล
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('super-admin.users.edit', $user->id) }}">
                                                <i class="bi bi-pencil-square"></i> แก้ไข
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-warning" href="#" 
                                               onclick="showResetPasswordModal({{ $user->id }}, '{{ $user->name }}')">
                                                <i class="bi bi-key-fill"></i> รีเซ็ตรหัสผ่าน
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-info" href="#" 
                                               onclick="showStatusToggleModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->status }}')">
                                                <i class="bi bi-toggle-on"></i> เปลี่ยนสถานะ
                                            </a>
                                        </li>
                                        @if($user->role !== 'super_admin')
                                        <li>
                                            <a class="dropdown-item text-success" href="#" 
                                               onclick="showPromoteRoleModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->role }}')">
                                                <i class="bi bi-arrow-up-circle"></i> เปลี่ยนบทบาท
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item text-secondary" href="#" 
                                               onclick="terminateUserSessions({{ $user->id }}, '{{ $user->name }}')">
                                                <i class="bi bi-box-arrow-right"></i> ยกเลิกเซสชัน
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if($user->role !== 'super_admin' || $stats['super_admin_users'] > 1)
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" 
                                               onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                                <i class="bi bi-trash3-fill"></i> ลบผู้ใช้
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="py-4">
                                    <i class="bi bi-people" style="font-size: 3rem; color: #d1d3e2; margin-bottom: 1rem;"></i>
                                    <p class="text-gray-500">ไม่พบข้อมูลผู้ใช้</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    แสดงผล {{ $users->firstItem() }} - {{ $users->lastItem() }} จาก {{ $users->total() }} รายการ
                </div>
                <div>
                    {{ $users->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.super-admin.users.modals.password-reset-modal')
@include('admin.super-admin.users.modals.status-toggle-modal')
@include('admin.super-admin.users.modals.promote-role-modal')
@endsection

@push('styles')
<style>
/* Fix dropdown z-index issues and prevent overlap */
.table-responsive {
    overflow: visible !important;
}

.dropdown-menu {
    z-index: 1055 !important;
    box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    border-radius: 0.5rem !important;
    min-width: 200px !important;
    margin-top: 0.25rem !important;
}

.card {
    overflow: visible !important;
}

.card-body {
    overflow: visible !important;
}

/* Ensure dropdown shows in front of all content */
.dropdown {
    position: relative;
    z-index: 1050;
}

.dropdown.show {
    z-index: 1055;
}

.dropdown.show .dropdown-menu {
    display: block;
    z-index: 1055;
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
}

/* Fix table overflow for dropdown visibility */
.table {
    overflow: visible !important;
}

/* Ensure button dropdown is clickable and positioned correctly */
.btn-group .dropdown-toggle {
    z-index: 1051;
}

/* Dropdown menu positioning */
.dropdown-menu-end {
    --bs-position: end;
    right: 0 !important;
    left: auto !important;
}

/* Add smooth animation */
.dropdown-menu {
    transition: all 0.15s ease-in-out;
    transform: translateY(-10px);
    opacity: 0;
    visibility: hidden;
}

.dropdown-menu.show {
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
}

/* Improve dropdown item styling */
.dropdown-item {
    padding: 0.5rem 1rem !important;
    font-size: 0.875rem;
    transition: all 0.15s ease-in-out;
}

.dropdown-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
}

.dropdown-item i {
    width: 16px;
    margin-right: 0.5rem;
}

/* Color-coded dropdown items */
.dropdown-item.text-warning:hover {
    background-color: rgba(255, 193, 7, 0.1) !important;
    color: #856404 !important;
}

.dropdown-item.text-info:hover {
    background-color: rgba(13, 202, 240, 0.1) !important;
    color: #055160 !important;
}

.dropdown-item.text-success:hover {
    background-color: rgba(25, 135, 84, 0.1) !important;
    color: #0a3622 !important;
}

.dropdown-item.text-danger:hover {
    background-color: rgba(220, 53, 69, 0.1) !important;
    color: #58151c !important;
}

.dropdown-item.text-secondary:hover {
    background-color: rgba(108, 117, 125, 0.1) !important;
    color: #2c2f33 !important;
}

/* Ensure dropdown doesn't get cut off by table boundaries */
.table-responsive .dropdown {
    static: position;
}

/* Force dropdown to appear above everything */
.dropdown-menu {
    position: absolute !important;
    will-change: transform;
}

/* Prevent scrollbar issues */
body.dropdown-open {
    overflow: hidden;
}

/* Table cell dropdown container */
td .dropdown {
    position: static;
}

/* Responsive dropdown positioning */
@media (max-width: 768px) {
    .dropdown-menu {
        min-width: 180px !important;
        font-size: 0.8rem;
    }
    
    .dropdown-item {
        padding: 0.4rem 0.8rem !important;
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/admin/super-admin-users.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fix dropdown positioning and prevent overlap
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    let currentOpenDropdown = null;
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('show.bs.dropdown', function(e) {
            // Close any previously opened dropdown
            if (currentOpenDropdown && currentOpenDropdown !== this) {
                const prevDropdown = bootstrap.Dropdown.getInstance(currentOpenDropdown);
                if (prevDropdown) {
                    prevDropdown.hide();
                }
            }
            
            currentOpenDropdown = this;
            
            // Ensure parent containers don't clip the dropdown
            let parent = this.closest('.table-responsive');
            if (parent) {
                parent.style.overflow = 'visible';
                parent.style.position = 'static';
            }
            
            // Add class to body to prevent scroll issues
            document.body.classList.add('dropdown-open');
            
            // Set high z-index for dropdown menu
            const menu = this.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                menu.style.zIndex = '1055';
                menu.style.position = 'absolute';
                
                // Calculate optimal position
                setTimeout(() => {
                    const rect = this.getBoundingClientRect();
                    const menuRect = menu.getBoundingClientRect();
                    const windowHeight = window.innerHeight;
                    const windowWidth = window.innerWidth;
                    
                    // Adjust position if dropdown goes outside viewport
                    if (rect.bottom + menuRect.height > windowHeight) {
                        menu.classList.add('dropup');
                        menu.style.bottom = '100%';
                        menu.style.top = 'auto';
                    }
                    
                    if (rect.right + menuRect.width > windowWidth) {
                        menu.classList.add('dropdown-menu-end');
                    }
                }, 10);
            }
        });
        
        dropdown.addEventListener('hide.bs.dropdown', function() {
            currentOpenDropdown = null;
            
            // Reset overflow when dropdown closes
            let parent = this.closest('.table-responsive');
            if (parent) {
                parent.style.overflow = 'auto';
            }
            
            // Remove body class
            document.body.classList.remove('dropdown-open');
            
            // Clean up positioning classes
            const menu = this.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                menu.classList.remove('dropup', 'dropdown-menu-end');
                menu.style.bottom = '';
                menu.style.top = '';
            }
        });
        
        // Handle click outside to close dropdown
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (currentOpenDropdown && !e.target.closest('.dropdown')) {
            const dropdownInstance = bootstrap.Dropdown.getInstance(currentOpenDropdown);
            if (dropdownInstance) {
                dropdownInstance.hide();
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (currentOpenDropdown) {
            const dropdownInstance = bootstrap.Dropdown.getInstance(currentOpenDropdown);
            if (dropdownInstance) {
                dropdownInstance.hide();
            }
        }
    });
    
    // Handle scroll to reposition dropdowns
    window.addEventListener('scroll', function() {
        if (currentOpenDropdown) {
            const menu = currentOpenDropdown.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu') && menu.classList.contains('show')) {
                // Reposition on scroll
                const rect = currentOpenDropdown.getBoundingClientRect();
                const menuRect = menu.getBoundingClientRect();
                
                if (rect.top < 0 || rect.bottom > window.innerHeight) {
                    const dropdownInstance = bootstrap.Dropdown.getInstance(currentOpenDropdown);
                    if (dropdownInstance) {
                        dropdownInstance.hide();
                    }
                }
            }
        }
    });
});
</script>
@endpush
