@extends('layouts.dashboard')

@section('title', 'ประวัติการแทนที่คำสั่ง')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-arrow-repeat me-2 text-warning"></i>ประวัติการแทนที่คำสั่ง
        </h1>
        <div class="d-flex gap-2">
            @if($isSuperAdmin)
                <a href="{{ route('admin.override.report') }}" class="btn btn-info btn-sm">
                    <i class="bi bi-file-earmark-text me-1"></i>รายงานรายละเอียด
                </a>
            @endif
            <a href="{{ route('admin.override.export', request()->query()) }}" class="btn btn-success btn-sm">
                <i class="bi bi-download me-1"></i>ส่งออก CSV
            </a>
        </div>
    </div>

    <!-- Warning Alert for Super Admin -->
    @if($isSuperAdmin && $stats['total_overrides'] > 0)
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>การแจ้งเตือนการตรวจสอบการแทนที่:</strong> 
            ตรวจพบการแทนที่คำสั่ง {{ $stats['total_overrides'] }} รายการในช่วงเวลาปัจจุบัน
            อัตราการแทนที่ที่สูงอาจบ่งบอกถึงปัญหาในกระบวนการอนุมัติ
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                การแทนที่ทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_overrides']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-repeat fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                เดือนนี้</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['overrides_this_month']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-month fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                อนุมัติการแทนที่</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['approved_overrides']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                ปฏิเสธการแทนที่</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['rejected_overrides']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-x-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-funnel me-2"></i>ตัวกรองข้อมูล
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.override.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="action" class="form-label">การกระทำแทนที่</label>
                    <select class="form-select" id="action" name="action">
                        <option value="">ทุกการกระทำ</option>
                        @foreach($overrideActions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ ucfirst(str_replace(['override_', '_'], ['', ' '], $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="overridden_by" class="form-label">ผู้ดำเนินการ</label>
                    <select class="form-select" id="overridden_by" name="overridden_by">
                        <option value="">ทุกผู้ใช้</option>
                        @foreach($overrideUsers as $user)
                            <option value="{{ $user->id }}" {{ request('overridden_by') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="original_reviewer" class="form-label">ผู้ตรวจสอบเดิม</label>
                    <select class="form-select" id="original_reviewer" name="original_reviewer">
                        <option value="">ทุกผู้ตรวจสอบ</option>
                        @foreach($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" {{ request('original_reviewer') == $reviewer->id ? 'selected' : '' }}>
                                {{ $reviewer->first_name }} {{ $reviewer->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="date_from" class="form-label">วันที่เริ่มต้น</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>ใช้ตัวกรอง
                    </button>
                    <a href="{{ route('admin.override.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>ล้างตัวกรอง
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Override History Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-list-ul me-2"></i>ประวัติการแทนที่คำสั่ง
                <span class="badge bg-warning ms-2">{{ $overrideLogs->total() }} รายการ</span>
            </h6>
        </div>
        <div class="card-body">
            @if($overrideLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>วันที่/เวลา</th>
                                <th>การกระทำแทนที่</th>
                                <th>ผู้ดำเนินการ</th>
                                <th>การตัดสินใจเดิม</th>
                                <th>รหัสคำขอ</th>
                                <th>เหตุผล</th>
                                <th>การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overrideLogs as $log)
                                <tr class="table-warning">
                                    <td>
                                        <small>
                                            {{ $log->performed_at->format('Y-m-d H:i:s') }}
                                            <br><span class="text-muted">{{ $log->performed_at->diffForHumans() }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-arrow-repeat me-1"></i>
                                            {{ ucfirst(str_replace(['override_', '_'], ['', ' '], $log->action)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->user)
                                            <div>
                                                <strong>{{ $log->user->first_name }} {{ $log->user->last_name }}</strong>
                                                <br><small class="text-muted">{{ ucfirst($log->user->role) }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">ไม่ทราบ</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->overriddenUser)
                                            <div>
                                                <strong>{{ $log->overriddenUser->first_name }} {{ $log->overriddenUser->last_name }}</strong>
                                                <br><small class="text-muted">{{ ucfirst($log->overriddenUser->role) }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">ระบบ</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.approvals.show', $log->registration_approval_id) }}" 
                                           class="text-decoration-none">
                                            #{{ $log->registration_approval_id }}
                                        </a>
                                        @if($log->registrationApproval && $log->registrationApproval->user)
                                            <br><small class="text-muted">
                                                {{ $log->registrationApproval->user->first_name }} 
                                                {{ $log->registrationApproval->user->last_name }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->reason)
                                            <span class="d-inline-block text-truncate" style="max-width: 200px;" 
                                                  data-bs-toggle="tooltip" title="{{ $log->reason }}">
                                                {{ $log->reason }}
                                            </span>
                                        @else
                                            <span class="text-muted">ไม่ได้ระบุเหตุผล</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.override.show', $log) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>รายละเอียด
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            แสดง {{ $overrideLogs->firstItem() }} ถึง {{ $overrideLogs->lastItem() }} 
                            จากทั้งหมด {{ $overrideLogs->total() }} รายการ
                        </small>
                    </div>
                    <div>
                        {{ $overrideLogs->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-shield-check fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">ไม่พบการแทนที่คำสั่ง</h5>
                    <p class="text-muted">
                        @if($isSuperAdmin)
                            นี่เป็นข่าวดี! ไม่มีการดำเนินการแทนที่คำสั่งในช่วงเวลาปัจจุบัน
                        @else
                            ไม่มีประวัติการแทนที่คำสั่งสำหรับบัญชีของคุณ
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}
.table-hover tbody tr:hover {
    background-color: rgba(255, 193, 7, 0.2) !important;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Auto-submit form on filter change
    $('.form-select').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // Highlight critical override count
    @if($stats['total_overrides'] > 10)
        $('.border-left-warning .h5').addClass('text-danger').removeClass('text-gray-800');
    @endif
});
</script>
@endpush