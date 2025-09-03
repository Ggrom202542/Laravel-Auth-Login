@extends('layouts.dashboard')

@section('title', 'รายละเอียดผู้ใช้')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.users.index') }}">จัดการผู้ใช้</a>
                    </li>
                    <li class="breadcrumb-item active">รายละเอียด</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-person-lines-fill me-2"></i>
                รายละเอียดผู้ใช้
            </h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i>
                แก้ไข
            </a>
            <button type="button" class="btn btn-secondary" 
                    onclick="toggleStatus({{ $user->id }}, '{{ $user->status }}')">
                <i class="bi bi-{{ $user->status === 'active' ? 'person-x' : 'person-check' }} me-1"></i>
                {{ $user->status === 'active' ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-person-card me-2"></i>
                        ข้อมูลส่วนตัว
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'inactive' ? 'warning' : 'danger') }} fs-6">
                            {{ $user->status === 'active' ? 'ใช้งานได้' : ($user->status === 'inactive' ? 'ไม่ใช้งาน' : 'ถูกระงับ') }}
                        </span>
                        <span class="badge bg-{{ $user->approval_status === 'approved' ? 'success' : ($user->approval_status === 'pending' ? 'warning' : 'danger') }} fs-6">
                            {{ $user->approval_status === 'approved' ? 'อนุมัติแล้ว' : ($user->approval_status === 'pending' ? 'รอการอนุมัติ' : 'ปฏิเสธแล้ว') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-4">
                            @if($user->profile_image)
                                <img src="{{ asset('storage/avatars/' . $user->profile_image) }}" 
                                     class="rounded-circle img-thumbnail mb-3" 
                                     width="150" height="150"
                                     style="object-fit: cover; width: 150px; height: 150px;"
                                     alt="Profile Picture">
                            @else
                                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 150px; height: 150px;">
                                    <i class="bi bi-person-fill text-white" style="font-size: 4rem;"></i>
                                </div>
                            @endif
                            <div class="text-center">
                                <h5 class="mb-0">{{ $user->prefix }}{{ $user->first_name }} {{ $user->last_name }}</h5>
                                <p class="text-muted">{{ $user->username }}</p>
                                <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">คำนำหน้า</label>
                                    <div class="form-control-plaintext">{{ $user->prefix }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ชื่อ</label>
                                    <div class="form-control-plaintext">{{ $user->first_name }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">นามสกุล</label>
                                    <div class="form-control-plaintext">{{ $user->last_name }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Username</label>
                                    <div class="form-control-plaintext">{{ $user->username }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">เบอร์โทรศัพท์</label>
                                    <div class="form-control-plaintext">
                                        <i class="bi bi-telephone me-1"></i>{{ $user->phone }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">อีเมล</label>
                                    <div class="form-control-plaintext">
                                        @if($user->email)
                                            <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                        @else
                                            <span class="text-muted">ไม่ได้ระบุ</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-shield-check me-2"></i>
                        ข้อมูลบัญชี
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">วันที่สมัครสมาชิก</label>
                            <div class="form-control-plaintext">
                                <i class="bi bi-calendar me-1"></i>
                                {{ $user->created_at->format('d/m/Y H:i:s') }}
                                <br>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">เข้าสู่ระบบล่าสุด</label>
                            <div class="form-control-plaintext">
                                @if($user->last_login_at)
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $user->last_login_at->format('d/m/Y H:i:s') }}
                                    <br>
                                    <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">ยังไม่เคยเข้าสู่ระบบ</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">จำนวนครั้งที่เข้าสู่ระบบ</label>
                            <div class="form-control-plaintext">
                                <i class="bi bi-graph-up me-1"></i>{{ $user->login_count ?? 0 }} ครั้ง
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">IP Address ล่าสุด</label>
                            <div class="form-control-plaintext">
                                @if($user->last_ip_address)
                                    <i class="bi bi-geo-alt me-1"></i>{{ $user->last_ip_address }}
                                @else
                                    <span class="text-muted">ไม่มีข้อมูล</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">วันที่ยืนยันบัญชี</label>
                            <div class="form-control-plaintext">
                                @if($user->account_verified_at)
                                    <i class="bi bi-patch-check me-1"></i>
                                    {{ $user->account_verified_at->format('d/m/Y H:i:s') }}
                                @else
                                    <span class="text-muted">ยังไม่ได้ยืนยัน</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">สถานะบัญชีล็อค</label>
                            <div class="form-control-plaintext">
                                @if($user->locked_until && $user->locked_until > now())
                                    <span class="badge bg-danger">
                                        <i class="bi bi-lock me-1"></i>
                                        ล็อคจนถึง {{ $user->locked_until->format('d/m/Y H:i') }}
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-unlock me-1"></i>ไม่ถูกล็อค
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($user->user_agent)
                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-bold">User Agent ล่าสุด</label>
                            <div class="form-control-plaintext">
                                <small class="text-muted">{{ $user->user_agent }}</small>
                            </div>
                        </div>
                    @endif

                    @if($user->admin_notes)
                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-bold">หมายเหตุจากแอดมิน</label>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                {{ $user->admin_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Registration Approval Details -->
            @if($user->registrationApproval)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clipboard-check me-2"></i>
                        ข้อมูลการอนุมัติ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">วันที่สมัคร</label>
                            <div class="form-control-plaintext">
                                {{ $user->registrationApproval->created_at->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">สถานะการอนุมัติ</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-{{ $user->registrationApproval->status === 'approved' ? 'success' : ($user->registrationApproval->status === 'pending' ? 'warning' : 'danger') }} fs-6">
                                    {{ $user->registrationApproval->status_text }}
                                </span>
                            </div>
                        </div>
                        @if($user->registrationApproval->reviewer)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ผู้อนุมัติ</label>
                            <div class="form-control-plaintext">
                                {{ $user->registrationApproval->reviewer->first_name }} {{ $user->registrationApproval->reviewer->last_name }}
                                <br>
                                <small class="text-muted">{{ $user->registrationApproval->reviewed_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                        @if($user->registrationApproval->rejection_reason)
                        <div class="col-12">
                            <label class="form-label fw-bold text-danger">เหตุผลการปฏิเสธ</label>
                            <div class="alert alert-danger">
                                {{ $user->registrationApproval->rejection_reason }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Action Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-lightning me-2"></i>
                        การดำเนินการ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-2"></i>
                            แก้ไขข้อมูล
                        </a>
                        <button type="button" class="btn btn-{{ $user->status === 'active' ? 'secondary' : 'success' }}" 
                                onclick="toggleStatus({{ $user->id }}, '{{ $user->status }}')">
                            <i class="bi bi-{{ $user->status === 'active' ? 'person-x' : 'person-check' }} me-2"></i>
                            {{ $user->status === 'active' ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}
                        </button>
                        <button type="button" class="btn btn-danger" 
                                onclick="resetPassword({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                            <i class="bi bi-key me-2"></i>
                            รีเซ็ตรหัสผ่าน
                        </button>
                        <hr>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            กลับรายการ
                        </a>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>
                        เส้นเวลากิจกรรม
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">สมัครสมาชิก</h6>
                                <p class="timeline-text">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($user->registrationApproval && $user->registrationApproval->status !== 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $user->registrationApproval->status === 'approved' ? 'success' : 'danger' }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ $user->registrationApproval->status_text }}</h6>
                                <p class="timeline-text">
                                    {{ $user->registrationApproval->reviewed_at->format('d/m/Y H:i') }}
                                    @if($user->registrationApproval->reviewer)
                                        <br><small>โดย {{ $user->registrationApproval->reviewer->first_name }}</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif
                        
                        @if($user->last_login_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">เข้าสู่ระบบล่าสุด</h6>
                                <p class="timeline-text">{{ $user->last_login_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
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
.timeline {
    position: relative;
    padding: 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 15px;
    height: 100%;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    padding-left: 40px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 5px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #4e73df;
}

.timeline-title {
    margin-bottom: 5px;
    color: #5a5c69;
}

.timeline-text {
    margin: 0;
    color: #858796;
    font-size: 0.875rem;
}
</style>
@endpush
