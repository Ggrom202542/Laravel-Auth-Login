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
                        <div class="d-flex">
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
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
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
            <div class="table-responsive">
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
                            <td>{{ $user->id }}</td>
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
                            <td>
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
                            <td>
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
                                @if($user->two_fa_enabled)
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
                                    <div class="text-sm">{{ $user->last_login_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-muted">{{ $user->last_login_at->format('H:i:s') }}</div>
                                @else
                                    <span class="text-muted">ยังไม่เคยเข้าสู่ระบบ</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                            data-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
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

@push('scripts')
<script src="{{ asset('js/admin/super-admin-users.js') }}"></script>
@endpush
