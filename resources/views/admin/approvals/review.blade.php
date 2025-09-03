@extends('layouts.dashboard')

@section('title', 'รายละเอียดการสมัครสมาชิก')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.approvals.index') }}">จัดการอนุมัติสมาชิก</a>
                    </li>
                    <li class="breadcrumb-item active">รายละเอียด</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-person-lines-fill me-2"></i>
                รายละเอียดการสมัครสมาชิก
            </h1>
        </div>
        <div class="d-flex gap-2">
            @if($approval->status === 'pending')
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="bi bi-check-circle me-1"></i>
                    อนุมัติ
                </button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle me-1"></i>
                    ปฏิเสธ
                </button>
            @endif
            <a href="{{ route('admin.approvals.index') }}" class="btn btn-secondary">
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
                        ข้อมูลผู้สมัคร
                    </h6>
                    <span class="badge bg-{{ $approval->status_badge_color }} fs-6">
                        {{ $approval->status_text }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-4">
                            @if($approval->user->profile_image)
                                <img src="{{ asset('storage/avatars/' . $approval->user->profile_image) }}" 
                                     class="rounded-circle img-thumbnail mb-3" 
                                     width="150" height="150"
                                     style="object-fit: cover;"
                                     alt="Profile Picture">
                            @else
                                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 150px; height: 150px;">
                                    <i class="bi bi-person-fill text-white" style="font-size: 4rem;"></i>
                                </div>
                            @endif
                            <div class="text-center">
                                <h5 class="mb-0">{{ $approval->user->prefix }}{{ $approval->user->first_name }} {{ $approval->user->last_name }}</h5>
                                <p class="text-muted">{{ $approval->user->username }}</p>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">คำนำหน้า</label>
                                    <div class="form-control-plaintext">{{ $approval->user->prefix }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ชื่อ</label>
                                    <div class="form-control-plaintext">{{ $approval->user->first_name }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">นามสกุล</label>
                                    <div class="form-control-plaintext">{{ $approval->user->last_name }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Username</label>
                                    <div class="form-control-plaintext">{{ $approval->user->username }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">เบอร์โทรศัพท์</label>
                                    <div class="form-control-plaintext">
                                        <i class="bi bi-telephone me-1"></i>{{ $approval->user->phone }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">อีเมล</label>
                                    <div class="form-control-plaintext">
                                        @if($approval->user->email)
                                            <i class="bi bi-envelope me-1"></i>{{ $approval->user->email }}
                                        @else
                                            <span class="text-muted">ไม่ได้ระบุ</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">บทบาท</label>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-primary">{{ ucfirst($approval->user->role) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">สถานะบัญชี</label>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-{{ $approval->user->status === 'active' ? 'success' : 'warning' }}">
                                            {{ $approval->user->status === 'active' ? 'ใช้งานได้' : 'ยังไม่เปิดใช้งาน' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clipboard-data me-2"></i>
                        ข้อมูลการสมัคร
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">วันที่สมัคร</label>
                            <div class="form-control-plaintext">
                                <i class="bi bi-calendar me-1"></i>
                                {{ $approval->created_at->format('d/m/Y H:i:s') }}
                                <br>
                                <small class="text-muted">{{ $approval->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">IP Address</label>
                            <div class="form-control-plaintext">
                                <i class="bi bi-geo-alt me-1"></i>{{ $approval->registration_ip }}
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">User Agent</label>
                            <div class="form-control-plaintext small">
                                <i class="bi bi-browser-chrome me-1"></i>{{ $approval->user_agent }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Token การอนุมัติ</label>
                            <div class="form-control-plaintext">
                                <code class="text-primary">{{ substr($approval->approval_token, 0, 20) }}...</code>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Token หมดอายุ</label>
                            <div class="form-control-plaintext">
                                {{ $approval->token_expires_at->format('d/m/Y H:i') }}
                                @if($approval->isTokenExpired())
                                    <br><span class="badge bg-danger">หมดอายุแล้ว</span>
                                @else
                                    <br><span class="badge bg-success">ยังไม่หมดอายุ</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($approval->additional_data)
                        <hr>
                        <h6 class="fw-bold">ข้อมูลเพิ่มเติม:</h6>
                        <div class="row">
                            @foreach($approval->additional_data as $key => $value)
                                <div class="col-md-6 mb-2">
                                    <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                                    <span class="ms-2">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval History Card -->
            @if($approval->status !== 'pending')
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>
                        ประวัติการอนุมัติ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ผู้ดำเนินการ</label>
                            <div class="form-control-plaintext">
                                @if($approval->reviewer)
                                    <div class="d-flex align-items-center">
                                        @if($approval->reviewer->profile_image)
                                            <img src="{{ asset('storage/avatars/' . $approval->reviewer->profile_image) }}" 
                                                 class="rounded-circle me-2" width="32" height="32"
                                                 style="object-fit: cover;" alt="Reviewer">
                                        @else
                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="bi bi-person text-white small"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $approval->reviewer->first_name }} {{ $approval->reviewer->last_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $approval->reviewer->username }} ({{ ucfirst($approval->reviewer->role) }})</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">ไม่ระบุ</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">วันที่ดำเนินการ</label>
                            <div class="form-control-plaintext">
                                @if($approval->reviewed_at)
                                    <i class="bi bi-calendar-check me-1"></i>
                                    {{ $approval->reviewed_at->format('d/m/Y H:i:s') }}
                                    <br>
                                    <small class="text-muted">{{ $approval->reviewed_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">ไม่ระบุ</span>
                                @endif
                            </div>
                        </div>
                        @if($approval->status === 'rejected' && $approval->rejection_reason)
                        <div class="col-12">
                            <label class="form-label fw-bold text-danger">เหตุผลการปฏิเสธ</label>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ $approval->rejection_reason }}
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
                    @if($approval->status === 'pending')
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="bi bi-check-circle me-2"></i>
                                อนุมัติการสมัคร
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle me-2"></i>
                                ปฏิเสธการสมัคร
                            </button>
                            <hr>
                        </div>
                    @else
                        <div class="alert alert-{{ $approval->status_badge_color }}" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            การสมัครนี้ได้{{ $approval->status_text }}แล้ว
                        </div>
                    @endif
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.approvals.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            กลับรายการ
                        </a>
                        
                        @if(auth()->user()->role === 'super_admin')
                            <button type="button" class="btn btn-outline-danger" 
                                    onclick="deleteModal({{ $approval->id }}, '{{ $approval->user->first_name }} {{ $approval->user->last_name }}')">
                                <i class="bi bi-trash me-2"></i>
                                ลบข้อมูล
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>
                        เส้นเวลา
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">สมัครสมาชิก</h6>
                                <p class="timeline-text">{{ $approval->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($approval->status !== 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $approval->status === 'approved' ? 'success' : 'danger' }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ $approval->status_text }}</h6>
                                <p class="timeline-text">
                                    {{ $approval->reviewed_at->format('d/m/Y H:i') }}
                                    @if($approval->reviewer)
                                        <br><small>โดย {{ $approval->reviewer->first_name }} {{ $approval->reviewer->last_name }}</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif
                        
                        @if($approval->status === 'approved' && $approval->user->approved_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">เปิดใช้งานบัญชี</h6>
                                <p class="timeline-text">{{ $approval->user->approved_at->format('d/m/Y H:i') }}</p>
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
@include('admin.approvals.modals.single-approve')
@include('admin.approvals.modals.single-reject')
@if(auth()->user()->role === 'super_admin')
    @include('admin.approvals.modals.delete')
@endif

@endsection

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

@push('scripts')
<script>
function deleteModal(id, name) {
    if(confirm(`คุณต้องการลบข้อมูลการสมัครของ ${name} หรือไม่?`)) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/approvals/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
