@extends('layouts.app')

@section('title', 'สถานะการสมัครสมาชิก')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    @if($approval->status === 'pending')
                        <!-- Pending Status -->
                        <div class="text-center mb-4">
                            <div class="display-1 text-warning mb-3">⏳</div>
                            <h2 class="text-warning">กำลังรอการอนุมัติ</h2>
                            <p class="text-muted">ทีมงานกำลังตรวจสอบข้อมูลการสมัครของคุณ</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-card bg-light p-3 rounded mb-3">
                                    <h5 class="mb-3"><i class="fas fa-user text-primary"></i> ข้อมูลผู้สมัคร</h5>
                                    <p><strong>ชื่อ:</strong> {{ $approval->user->name }}</p>
                                    <p><strong>อีเมล:</strong> {{ $approval->user->email }}</p>
                                    <p><strong>วันที่สมัคร:</strong> {{ $approval->created_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card bg-warning bg-opacity-10 p-3 rounded mb-3">
                                    <h5 class="mb-3"><i class="fas fa-clock text-warning"></i> สถานะปัจจุบัน</h5>
                                    <p><strong>สถานะ:</strong> <span class="badge bg-warning text-dark">รอการอนุมัติ</span></p>
                                    <p><strong>เวลาที่เหลือ:</strong> 
                                        @if($approval->token_expires_at > now())
                                            {{ $approval->token_expires_at->diffForHumans(null, true) }}
                                        @else
                                            <span class="text-danger">หมดอายุแล้ว</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="timeline mt-4">
                            <h5 class="mb-3"><i class="fas fa-history text-info"></i> ขั้นตอนการอนุมัติ</h5>
                            <div class="timeline-item completed">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6>ส่งข้อมูลการสมัคร</h6>
                                    <small class="text-muted">{{ $approval->created_at->format('d/m/Y H:i:s') }}</small>
                                </div>
                            </div>
                            <div class="timeline-item current">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6>ตรวจสอบข้อมูล</h6>
                                    <small class="text-muted">กำลังดำเนินการ</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <h6>แจ้งผลการพิจารณา</h6>
                                    <small class="text-muted">รอการดำเนินการ</small>
                                </div>
                            </div>
                        </div>

                    @elseif($approval->status === 'approved')
                        <!-- Approved Status -->
                        <div class="text-center mb-4">
                            <div class="display-1 text-success mb-3">✅</div>
                            <h2 class="text-success">อนุมัติแล้ว</h2>
                            <p class="text-muted">การสมัครสมาชิกของคุณได้รับการอนุมัติเรียบร้อยแล้ว</p>
                        </div>

                        <div class="bg-success bg-opacity-10 p-4 rounded mb-4">
                            <h5 class="text-success mb-3"><i class="fas fa-check-circle"></i> ข้อมูลการอนุมัติ</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ผู้อนุมัติ:</strong> {{ $approval->reviewer->name ?? 'ระบบ' }}</p>
                                    <p><strong>วันที่อนุมัติ:</strong> {{ $approval->approved_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>สถานะบัญชี:</strong> <span class="badge bg-success">ใช้งานได้</span></p>
                                    <p><strong>สิทธิ์:</strong> {{ ucfirst($approval->user->role) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="btn btn-success btn-lg">
                                <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
                            </a>
                        </div>

                    @elseif($approval->status === 'rejected')
                        <!-- Rejected Status -->
                        <div class="text-center mb-4">
                            <div class="display-1 text-danger mb-3">❌</div>
                            <h2 class="text-danger">ไม่อนุมัติ</h2>
                            <p class="text-muted">การสมัครสมาชิกของคุณไม่ได้รับการอนุมัติ</p>
                        </div>

                        <div class="bg-danger bg-opacity-10 p-4 rounded mb-4">
                            <h5 class="text-danger mb-3"><i class="fas fa-times-circle"></i> ข้อมูลการปฏิเสธ</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ผู้พิจารณา:</strong> {{ $approval->reviewer->name ?? 'ระบบ' }}</p>
                                    <p><strong>วันที่ปฏิเสธ:</strong> {{ $approval->rejected_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>สถานะ:</strong> <span class="badge bg-danger">ไม่อนุมัติ</span></p>
                                </div>
                            </div>
                            @if($approval->rejection_reason)
                                <div class="mt-3 p-3 bg-white rounded">
                                    <strong>เหตุผล:</strong>
                                    <p class="mb-0 mt-2">{{ $approval->rejection_reason }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="text-center">
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> สมัครสมาชิกใหม่
                            </a>
                        </div>

                    @elseif($approval->status === 'expired')
                        <!-- Expired Status -->
                        <div class="text-center mb-4">
                            <div class="display-1 text-secondary mb-3">⏰</div>
                            <h2 class="text-secondary">หมดอายุ</h2>
                            <p class="text-muted">คำขอสมัครสมาชิกนี้หมดอายุแล้ว</p>
                        </div>

                        <div class="bg-secondary bg-opacity-10 p-4 rounded mb-4">
                            <h5 class="text-secondary mb-3"><i class="fas fa-clock"></i> ข้อมูลคำขอ</h5>
                            <p><strong>วันที่สมัคร:</strong> {{ $approval->created_at->format('d/m/Y H:i:s') }}</p>
                            <p><strong>หมดอายุเมื่อ:</strong> {{ $approval->token_expires_at->format('d/m/Y H:i:s') }}</p>
                            <p><strong>เหตุผล:</strong> {{ $approval->rejection_reason }}</p>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> สมัครสมาชิกใหม่
                            </a>
                        </div>
                    @endif

                    <!-- Refresh Button -->
                    <div class="text-center mt-4">
                        <button onclick="window.location.reload()" class="btn btn-outline-secondary">
                            <i class="fas fa-sync-alt"></i> รีเฟรช
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-card {
    transition: transform 0.2s;
}
.info-card:hover {
    transform: translateY(-2px);
}
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
.timeline-item {
    position: relative;
    padding: 15px 0;
}
.timeline-marker {
    position: absolute;
    left: -25px;
    top: 20px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}
.timeline-item.completed .timeline-marker {
    background-color: #28a745;
    box-shadow: 0 0 0 2px #28a745;
}
.timeline-item.current .timeline-marker {
    background-color: #ffc107;
    box-shadow: 0 0 0 2px #ffc107;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 2px #ffc107, 0 0 0 4px rgba(255, 193, 7, 0.3); }
    70% { box-shadow: 0 0 0 2px #ffc107, 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 2px #ffc107, 0 0 0 4px rgba(255, 193, 7, 0); }
}
</style>
@endsection
