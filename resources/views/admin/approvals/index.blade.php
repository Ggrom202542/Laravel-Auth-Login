@extends('layouts.dashboard')

@section('title', 'จัดการอนุมัติสมาชิก')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-person-check me-2"></i>
            จัดการอนุมัติสมาชิก
        </h1>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                รอการอนุมัติ
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['pending'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                ปฏิเสธแล้ว
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['rejected'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-x-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                วันนี้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['today'] }}
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
            <form method="GET" action="{{ route('admin.approvals.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">สถานะ</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>ปฏิเสธแล้ว</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">ค้นหา</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="ชื่อ, Username, อีเมล, เบอร์โทร" 
                               value="{{ request('search') }}">
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
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>ค้นหา
                            </button>
                            <a href="{{ route('admin.approvals.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>รีเซ็ต
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Action Bar (Hidden by default) -->
    <div id="bulkActionBar" class="card border-primary mb-4" style="display: none;">
        <div class="card-body bg-primary bg-opacity-10">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 text-primary">
                        <i class="bi bi-check-square me-2"></i>
                        เลือกแล้ว <span id="selectedCount" class="fw-bold">0</span> รายการ
                    </h6>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success btn-sm" id="bulkApproveBtn">
                        <i class="bi bi-check-circle me-1"></i>อนุมัติที่เลือก
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkRejectBtn">
                        <i class="bi bi-x-circle me-1"></i>ปฏิเสธที่เลือก
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="cancelSelection">
                        <i class="bi bi-x me-1"></i>ยกเลิก
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approvals List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                รายการการสมัครสมาชิก ({{ $approvals->total() }} รายการ)
            </h6>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll">
                <label class="form-check-label" for="selectAll">เลือกทั้งหมด</label>
            </div>
        </div>
        <div class="card-body p-0">
            @if($approvals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllHeader">
                                    </div>
                                </th>
                                <th>ข้อมูลผู้สมัคร</th>
                                <th>วันที่สมัคร</th>
                                <th>สถานะ</th>
                                <th>ผู้อนุมัติ/ปฏิเสธ</th>
                                <th width="200">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvals as $approval)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input approval-checkbox" 
                                               type="checkbox" 
                                               name="approvals[]" 
                                               value="{{ $approval->id }}"
                                               data-approval-id="{{ $approval->id }}"
                                               {{ $approval->status !== 'pending' ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            @if($approval->user->profile_image)
                                                <img src="{{ asset('storage/avatars/' . $approval->user->profile_image) }}" 
                                                     class="rounded-circle" width="40" height="40"
                                                     alt="Avatar">
                                            @else
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person text-white"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold">
                                                {{ $approval->user->prefix }}{{ $approval->user->first_name }} {{ $approval->user->last_name }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-person me-1"></i>{{ $approval->user->username }}
                                                @if($approval->user->email)
                                                    <br><i class="bi bi-envelope me-1"></i>{{ $approval->user->email }}
                                                @endif
                                                <br><i class="bi bi-telephone me-1"></i>{{ $approval->user->phone }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm">
                                        {{ $approval->created_at->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $approval->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $approval->status_badge_color }}">
                                        {{ $approval->status_text }}
                                    </span>
                                    @if($approval->isTokenExpired() && $approval->status === 'pending')
                                        <br><small class="text-danger">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Token หมดอายุ
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($approval->reviewer)
                                        <div class="text-sm">
                                            <strong>{{ $approval->reviewer->first_name }} {{ $approval->reviewer->last_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $approval->reviewed_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.approvals.show', $approval) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        @if($approval->status === 'pending')
                                            <button type="button" class="btn btn-success btn-sm approve-btn" 
                                                    data-approval-id="{{ $approval->id }}"
                                                    data-user-name="{{ $approval->user->first_name }} {{ $approval->user->last_name }}">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm reject-btn"
                                                    data-approval-id="{{ $approval->id }}"
                                                    data-user-name="{{ $approval->user->first_name }} {{ $approval->user->last_name }}">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        @endif
                                        
                                        @if(auth()->user()->role === 'super_admin')
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                    onclick="deleteModal({{ $approval->id }}, '{{ $approval->user->first_name }} {{ $approval->user->last_name }}')">
                                                <i class="bi bi-trash"></i>
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
                <div class="d-flex justify-content-between align-items-center p-3">
                    <div class="text-muted">
                        แสดง {{ $approvals->firstItem() }} - {{ $approvals->lastItem() }} 
                        จากทั้งหมด {{ $approvals->total() }} รายการ
                    </div>
                    {{ $approvals->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">ไม่พบรายการการสมัครสมาชิก</p>
                    <a href="{{ route('admin.approvals.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-clockwise me-1"></i>รีเฟรช
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('admin.approvals.single-approve')
@include('admin.approvals.single-reject')
@include('admin.approvals.bulk-modals')

@endsection

@push('scripts')
<script src="{{ asset('js/approvals.js') }}"></script>
@endpush

@push('styles')
<style>
.avatar img, .avatar div {
    object-fit: cover;
}

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
@endpush
