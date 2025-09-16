@extends('layouts.dashboard')

@section('title', 'บันทึกตรวจสอบการอนุมัติ')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-clipboard-check me-2"></i>บันทึกตรวจสอบการอนุมัติ
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.audit.statistics') }}" class="btn btn-info btn-sm">
                <i class="bi bi-graph-up me-1"></i>สถิติ
            </a>
            <a href="{{ route('admin.audit.export', request()->query()) }}" class="btn btn-success btn-sm">
                <i class="bi bi-download me-1"></i>ส่งออก CSV
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                รวมบันทึกตรวจสอบ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_logs']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clipboard-data fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                การยกเลิกทั้งหมด</div>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                ผู้ใช้ที่ใช้งาน</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['unique_users']) }}</div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                กิจกรรมวันนี้</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['actions_today']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
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
                <i class="bi bi-funnel me-2"></i>ตัวกรอง
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="action" class="form-label">ประเภทการดำเนินการ</label>
                    <select class="form-select" id="action" name="action">
                        <option value="">การดำเนินการทั้งหมด</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="user_id" class="form-label">ผู้ใช้</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">ผู้ใช้ทั้งหมด</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_from" class="form-label">วันที่เริ่มต้น</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_to" class="form-label">วันที่สิ้นสุด</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2">
                    <label for="is_override" class="form-label">การยกเลิกเท่านั้น</label>
                    <select class="form-select" id="is_override" name="is_override">
                        <option value="">ทั้งหมด</option>
                        <option value="1" {{ request('is_override') === '1' ? 'selected' : '' }}>การยกเลิกเท่านั้น</option>
                        <option value="0" {{ request('is_override') === '0' ? 'selected' : '' }}>ปกติเท่านั้น</option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>ใช้ตัวกรอง
                    </button>
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>ล้างตัวกรอง
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-list-ul me-2"></i>รายการบันทึกตรวจสอบ
                <span class="badge bg-primary ms-2">{{ $auditLogs->total() }} รายการ</span>
            </h6>
        </div>
        <div class="card-body">
            @if($auditLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>วันที่/เวลา</th>
                                <th>การดำเนินการ</th>
                                <th>ผู้ใช้</th>
                                <th>รหัสอนุมัติ</th>
                                <th>การเปลี่ยนสถานะ</th>
                                <th>การยกเลิก</th>
                                <th>รายละเอียด</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLogs as $log)
                                <tr class="{{ $log->is_override ? 'table-warning' : '' }}">
                                    <td>
                                        <small class="text-muted">
                                            {{ $log->performed_at->format('Y-m-d') }}<br>
                                            {{ $log->performed_at->format('H:i:s') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $log->is_override ? 'warning' : 'info' }}">
                                            {{ $log->action_description }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->user)
                                            <div>
                                                <strong>{{ $log->user->first_name }} {{ $log->user->last_name }}</strong>
                                                <br><small class="text-muted">{{ ucfirst($log->user->role) }}</small>
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
                                        @if($log->old_status && $log->new_status)
                                            <span class="badge bg-secondary">{{ $log->old_status }}</span>
                                            <i class="bi bi-arrow-right mx-1"></i>
                                            <span class="badge bg-primary">{{ $log->new_status }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($log->is_override)
                                            <span class="badge bg-warning">
                                                <i class="bi bi-arrow-repeat me-1"></i>ยกเลิก
                                            </span>
                                            @if($log->overriddenUser)
                                                <br><small class="text-muted">
                                                    {{ $log->overriddenUser->first_name }} {{ $log->overriddenUser->last_name }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.audit.show', $log) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>ดู
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
                            แสดง {{ $auditLogs->firstItem() }} ถึง {{ $auditLogs->lastItem() }} 
                            จาก {{ $auditLogs->total() }} รายการ
                        </small>
                    </div>
                    <div>
                        {{ $auditLogs->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">ไม่พบบันทึกตรวจสอบ</h5>
                    <p class="text-muted">ลองปรับเปลี่ยนตัวกรองหรือตรวจสอบอีกครั้งในภายหลัง</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
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
});
</script>
@endpush