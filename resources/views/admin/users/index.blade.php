@extends('layouts.dashboard')

@section('title', 'จัดการผู้ใช้')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-people me-2"></i>
            จัดการผู้ใช้ระบบ
        </h1>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-gear me-1"></i>
                เครื่องมือ
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportUsers()">
                    <i class="bi bi-download me-2"></i>ส่งออกข้อมูล
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                    <i class="bi bi-check-square me-2"></i>การดำเนินการหลายรายการ
                </a></li>
            </ul>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                ผู้ใช้ทั้งหมด
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                ใช้งานอยู่
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                ไม่ใช้งาน
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['inactive'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-x fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                รอการอนุมัติ
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['pending_approval'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                อนุมัติแล้ว
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['approved'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                วันนี้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['today_registrations'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Search Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-funnel me-2"></i>
                ตัวกรองและค้นหา
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">ค้นหา</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="ชื่อ, Username, อีเมล, เบอร์โทร" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">สถานะ</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>ใช้งานได้</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>ถูกระงับ</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="approval_status" class="form-label">การอนุมัติ</label>
                        <select name="approval_status" id="approval_status" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>รอการอนุมัติ</option>
                            <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                            <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>ปฏิเสธแล้ว</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_from" class="form-label">วันที่เริ่มต้น</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_to" class="form-label">วันที่สิ้นสุด</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                รายการผู้ใช้ระบบ ({{ $users->total() }} รายการ)
            </h6>
        </div>
        <div class="card-body p-0">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="70">รูปภาพ</th>
                                <th>ข้อมูลผู้ใช้</th>
                                <th>สถานะ</th>
                                <th>การอนุมัติ</th>
                                <th>เข้าสู่ระบบล่าสุด</th>
                                <th width="150">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td style="text-align: center;">
                                    @if($user->profile_image)
                                        <img src="{{ asset('storage/avatars/' . $user->profile_image) }}" 
                                             class="rounded-circle" width="40" height="40"
                                             style="object-fit: cover;" alt="Avatar">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold">
                                            {{ $user->prefix }}{{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bi bi-person me-1"></i>{{ $user->username }}
                                            @if($user->email)
                                                <br><i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                            @endif
                                            <br><i class="bi bi-telephone me-1"></i>{{ $user->phone }}
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'inactive' ? 'warning' : 'danger') }}">
                                        {{ $user->status === 'active' ? 'ใช้งานได้' : ($user->status === 'inactive' ? 'ไม่ใช้งาน' : 'ถูกระงับ') }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge bg-{{ $user->approval_status === 'approved' ? 'success' : ($user->approval_status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ $user->approval_status === 'approved' ? 'อนุมัติแล้ว' : ($user->approval_status === 'pending' ? 'รอการอนุมัติ' : 'ปฏิเสธแล้ว') }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->last_login_at)
                                        <div class="text-sm">
                                            {{ $user->last_login_at->format('d/m/Y H:i') }}
                                            <br>
                                            <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">ยังไม่เคยเข้าสู่ระบบ</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="btn btn-info btn-sm" title="ดูรายละเอียด">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-warning btn-sm" title="แก้ไข">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                onclick="toggleStatus({{ $user->id }}, '{{ $user->status }}')" 
                                                title="{{ $user->status === 'active' ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}">
                                            <i class="bi bi-{{ $user->status === 'active' ? 'person-x' : 'person-check' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="resetPassword({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}', '{{ $user->phone }}', '{{ $user->email }}')" 
                                                title="รีเซ็ตรหัสผ่าน">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-3">
                    <div class="text-muted">
                        แสดง {{ $users->firstItem() }} - {{ $users->lastItem() }} 
                        จากทั้งหมด {{ $users->total() }} รายการ
                    </div>
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-people fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">ไม่พบผู้ใช้ในระบบ</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-clockwise me-1"></i>รีเฟรช
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('admin.users.modals.status-toggle')
@include('admin.users.modals.password-reset')

@endsection

@push('scripts')
<script src="{{ asset('js/admin-users.js') }}"></script>
@endpush

@push('styles')
<style>
.table td {
    vertical-align: middle;
}

.btn-group .btn {
    border-radius: 4px !important;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>

<!-- Include Modals -->
@include('admin.users.partials.status-toggle-modal')
@include('admin.users.partials.password-reset-modal')

@endpush
