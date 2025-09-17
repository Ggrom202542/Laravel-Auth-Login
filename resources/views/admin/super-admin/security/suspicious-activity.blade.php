@extends('layouts.dashboard')

@section('title', 'ตรวจสอบกิจกรรมน่าสงสัย')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-warning bg-soft rounded me-3">
                        <i class="bi bi-shield-exclamation text-warning fs-4"></i>
                    </div>
                    <div>
                        <h4 class="page-title mb-1">ตรวจสอบกิจกรรมน่าสงสัย</h4>
                        <p class="text-muted mb-0">ระบบการตรวจสอบและวิเคราะห์กิจกรรมที่น่าสงสัย</p>
                    </div>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                                หน้าหลัก
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('super-admin.security.index') }}" class="text-decoration-none">
                                ความปลอดภัย
                            </a>
                        </li>
                        <li class="breadcrumb-item active">กิจกรรมน่าสงสัย</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Development Environment Warning -->
    @if(app()->environment('local') || request()->ip() === '127.0.0.1')
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-warning bg-soft rounded-circle me-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">⚠️ คำเตือนสำหรับสภาพแวดล้อมการพัฒนา</h6>
                        <p class="mb-0">
                            <strong>IP Address ปัจจุบัน:</strong> 
                            <code class="bg-light px-2 py-1 rounded ms-1">{{ request()->ip() }}</code>
                        </p>
                        <small class="text-muted">
                            ในสภาพแวดล้อม localhost ทุก request จะแสดง IP เป็น 127.0.0.1 
                            หากบล็อก IP นี้จะทำให้<strong>เว็บไซต์ทั้งหมดไม่สามารถเข้าถึงได้</strong> 
                            ระบบจึงข้าม localhost และ private IPs โดยอัตโนมัติ
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Statistics Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-lg bg-danger bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 fw-medium">ความเสี่ยงสูง</h6>
                            <h2 class="mb-0 text-danger fw-bold">{{ number_format($suspiciousStats['high_risk_attempts'] ?? 0) }}</h2>
                            <small class="text-muted">
                                <i class="bi bi-graph-up-arrow me-1"></i>ครั้งล็อกอิน
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-lg bg-warning bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-exclamation-circle-fill text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 fw-medium">ความเสี่ยงปานกลาง</h6>
                            <h2 class="mb-0 text-warning fw-bold">{{ number_format($suspiciousStats['medium_risk_attempts'] ?? 0) }}</h2>
                            <small class="text-muted">
                                <i class="bi bi-graph-up me-1"></i>ครั้งล็อกอิน
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-lg bg-info bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-person-fill-exclamation text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 fw-medium">ผู้ใช้ต้องสงสัย</h6>
                            <h2 class="mb-0 text-info fw-bold">{{ number_format($suspiciousStats['flagged_users'] ?? 0) }}</h2>
                            <small class="text-muted">
                                <i class="bi bi-people me-1"></i>ผู้ใช้
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-lg bg-secondary bg-soft rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-shield-x text-secondary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 fw-medium">บล็อกอัตโนมัติ</h6>
                            <h2 class="mb-0 text-secondary fw-bold">{{ number_format($suspiciousStats['automated_blocks'] ?? 0) }}</h2>
                            <small class="text-muted">
                                <i class="bi bi-ban me-1"></i>IP ที่ถูกบล็อก
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspicious Activities Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary bg-soft rounded me-3">
                                <i class="bi bi-clock-history text-primary"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">กิจกรรมน่าสงสัยล่าสุด</h5>
                                <p class="text-muted mb-0 small">รายการความพยายามล็อกอินที่น่าสงสัยและเหตุการณ์ด้านความปลอดภัยล่าสุด</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshData()">
                                <i class="bi bi-arrow-clockwise me-1"></i>รีเฟรช
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-funnel me-1"></i>ตัวกรอง
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>ความเสี่ยงสูง</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-exclamation-circle me-2 text-warning"></i>ความเสี่ยงปานกลาง</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-check-circle me-2 text-success"></i>ความเสี่ยงต่ำ</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-list me-2"></i>ทั้งหมด</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(isset($recentSuspicious) && $recentSuspicious->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light border-bottom">
                                    <tr>
                                        <th class="border-0 ps-4">
                                            <i class="bi bi-clock me-2 text-muted"></i>เวลา
                                        </th>
                                        <th class="border-0">
                                            <i class="bi bi-person me-2 text-muted"></i>ผู้ใช้
                                        </th>
                                        <th class="border-0">
                                            <i class="bi bi-geo-alt me-2 text-muted"></i>IP Address
                                        </th>
                                        <th class="border-0">
                                            <i class="bi bi-speedometer2 me-2 text-muted"></i>ระดับความเสี่ยง
                                        </th>
                                        <th class="border-0">
                                            <i class="bi bi-info-circle me-2 text-muted"></i>เหตุผล
                                        </th>
                                        <th class="border-0">
                                            <i class="bi bi-check-circle me-2 text-muted"></i>สถานะ
                                        </th>
                                        <th class="border-0 pe-4 text-center">
                                            <i class="bi bi-gear me-2 text-muted"></i>การดำเนินการ
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSuspicious as $attempt)
                                        <tr class="border-bottom">
                                            <td class="ps-4">
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold small">{{ $attempt->attempted_at ? $attempt->attempted_at->format('d/m/Y') : 'ไม่ระบุ' }}</span>
                                                    <small class="text-muted">{{ $attempt->attempted_at ? $attempt->attempted_at->format('H:i:s') : '' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($attempt->user)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary bg-soft rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                            <span class="text-primary fw-bold small">{{ substr($attempt->user->first_name ?? 'U', 0, 1) }}</span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-semibold">{{ $attempt->user->first_name }} {{ $attempt->user->last_name }}</h6>
                                                            <small class="text-muted">{{ $attempt->user->email ?? $attempt->username_attempted }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-secondary bg-soft rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                            <i class="bi bi-person-x text-secondary"></i>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted fw-medium">{{ $attempt->username_attempted ?? 'ไม่ทราบผู้ใช้' }}</span>
                                                            <br><small class="text-muted">ผู้ใช้ไม่ถูกต้อง</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <code class="bg-light px-3 py-2 rounded small border">{{ $attempt->ip_address ?? 'ไม่ระบุ' }}</code>
                                                    @if($attempt->country_name)
                                                        <small class="text-muted mt-1">
                                                            <i class="bi bi-flag me-1"></i>{{ $attempt->country_name }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $riskScore = $attempt->risk_score ?? 0;
                                                    $riskClass = $riskScore > 80 ? 'danger' : ($riskScore > 50 ? 'warning' : 'success');
                                                    $riskText = $riskScore > 80 ? 'สูงมาก' : ($riskScore > 50 ? 'ปานกลาง' : 'ต่ำ');
                                                    $riskIcon = $riskScore > 80 ? 'exclamation-triangle-fill' : ($riskScore > 50 ? 'exclamation-circle-fill' : 'check-circle-fill');
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-{{ $riskClass }} me-2 px-3 py-2">
                                                        <i class="bi bi-{{ $riskIcon }} me-1"></i>{{ $riskScore }}%
                                                    </span>
                                                    <small class="text-{{ $riskClass }} fw-medium">{{ $riskText }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $attempt->failure_reason ?? 'ตรวจพบรูปแบบที่น่าสงสัย' }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $status = $attempt->status ?? 'failed';
                                                    $statusClass = $status === 'success' ? 'success' : 'danger';
                                                    $statusText = $status === 'success' ? 'สำเร็จ' : 'ล้มเหลว';
                                                    $statusIcon = $status === 'success' ? 'check-circle-fill' : 'x-circle-fill';
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }} px-3 py-2">
                                                    <i class="bi bi-{{ $statusIcon }} me-1"></i>{{ $statusText }}
                                                </span>
                                            </td>
                                            <td class="pe-4">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" 
                                                            title="ดูรายละเอียด" onclick="viewDetails({{ $attempt->id }})">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    @if($attempt->ip_address)
                                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" 
                                                                title="บล็อก IP" onclick="blockIP('{{ $attempt->ip_address }}')">
                                                            <i class="bi bi-shield-x"></i>
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
                        @if(method_exists($recentSuspicious, 'links'))
                            <div class="d-flex justify-content-center p-4 border-top bg-light">
                                {{ $recentSuspicious->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-success bg-soft rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center">
                                <i class="bi bi-shield-check text-success" style="font-size: 2.5rem;"></i>
                            </div>
                            <h4 class="fw-bold text-success mb-2">ไม่มีกิจกรรมน่าสงสัย</h4>
                            <p class="text-muted mb-0">ไม่พบความพยายามล็อกอินที่น่าสงสัยในช่วงเวลาที่ผ่านมา</p>
                            <p class="text-muted small">ระบบความปลอดภัยทำงานได้ดี</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Attack Patterns Analysis -->
    @if(isset($attackPatterns) && count($attackPatterns) > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-warning bg-soft rounded me-3">
                            <i class="bi bi-graph-up text-warning"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">การวิเคราะห์รูปแบบการโจมตี</h5>
                            <p class="text-muted mb-0 small">รูปแบบการโจมตีที่พบบ่อยและแนวโน้มการโจมตี</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($attackPatterns as $pattern)
                            <div class="col-lg-4 col-md-6">
                                <div class="border rounded-4 p-4 h-100 hover-lift bg-light bg-gradient">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm bg-warning bg-soft rounded-circle me-3">
                                            <i class="bi bi-exclamation-triangle text-warning"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $pattern['type'] ?? 'รูปแบบไม่ทราบ' }}</h6>
                                    </div>
                                    <p class="text-muted mb-3 small">{{ $pattern['description'] ?? 'ไม่มีคำอธิบาย' }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted fw-medium">
                                            <i class="bi bi-bar-chart-line me-2"></i>จำนวนครั้ง:
                                        </small>
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <i class="bi bi-hash me-1"></i>{{ number_format($pattern['count'] ?? 0) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
// ฟังก์ชันดูรายละเอียด
function viewDetails(attemptId) {
    Swal.fire({
        title: '<i class="bi bi-info-circle me-2"></i>รายละเอียดการพยายามล็อกอิน',
        html: `
            <div class="text-start">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                </div>
                <p class="text-muted">กำลังโหลดข้อมูลของ ID: <code>${attemptId}</code></p>
            </div>
        `,
        icon: 'info',
        showConfirmButton: true,
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>ตกลง',
        confirmButtonColor: '#0d6efd',
        customClass: {
            popup: 'border-0 shadow-lg',
            header: 'border-bottom pb-3',
            title: 'h5 mb-0'
        }
    });
}

// ฟังก์ชันบล็อก IP
function blockIP(ipAddress) {
    // ตรวจสอบว่าเป็น localhost หรือ private IP
    const isLocalhost = ipAddress === '127.0.0.1' || ipAddress === '::1' || ipAddress === 'localhost';
    const isPrivateIP = /^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.)/.test(ipAddress);
    
    if (isLocalhost || isPrivateIP) {
        Swal.fire({
            title: '<i class="bi bi-exclamation-triangle me-2 text-warning"></i>คำเตือน!',
            html: `
                <div class="text-start">
                    <div class="alert alert-warning border-0 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>IP Address:</strong> <code class="ms-2">${ipAddress}</code>
                        </div>
                    </div>
                    <div class="alert alert-danger border-0 mb-3">
                        <h6 class="alert-heading"><i class="bi bi-x-circle me-2"></i>ไม่สามารถบล็อก IP นี้ได้</h6>
                        <ul class="mb-0 ps-3">
                            <li>IP นี้เป็น localhost หรือ private network</li>
                            <li>การบล็อกจะทำให้เว็บไซต์ไม่สามารถเข้าถึงได้</li>
                            <li>ระบบป้องกันการบล็อกโดยอัตโนมัติ</li>
                        </ul>
                    </div>
                </div>
            `,
            icon: 'warning',
            confirmButtonColor: '#ffc107',
            confirmButtonText: '<i class="bi bi-check-circle me-1"></i>เข้าใจแล้ว',
            customClass: {
                popup: 'border-0 shadow-lg',
                header: 'border-bottom pb-3'
            }
        });
        return;
    }

    Swal.fire({
        title: '<i class="bi bi-shield-exclamation me-2 text-danger"></i>ยืนยันการบล็อก IP',
        html: `
            <div class="text-start">
                <div class="alert alert-info border-0 mb-3">
                    <h6 class="alert-heading mb-2">IP Address ที่จะบล็อก:</h6>
                    <div class="text-center">
                        <code class="bg-dark text-white px-4 py-3 rounded fs-5 fw-bold">${ipAddress}</code>
                    </div>
                </div>
                <div class="alert alert-danger border-0">
                    <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>คำเตือนสำคัญ</h6>
                    <p class="mb-0">การบล็อก IP นี้จะป้องกันไม่ให้ผู้ใช้จาก IP นี้เข้าถึงเว็บไซต์ได้ทั้งหมด การดำเนินการนี้ไม่สามารถยกเลิกได้ง่าย</p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-shield-x me-1"></i>ยืนยันการบล็อก',
        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>ยกเลิก',
        reverseButtons: true,
        customClass: {
            popup: 'border-0 shadow-lg',
            header: 'border-bottom pb-3'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // แสดง loading
            Swal.fire({
                title: '<i class="bi bi-gear me-2"></i>กำลังดำเนินการ...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">กำลังบล็อก IP...</span>
                        </div>
                        <p class="text-muted">กำลังบล็อก IP: <code>${ipAddress}</code></p>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                customClass: {
                    popup: 'border-0 shadow-lg'
                }
            });

            // ส่งคำขอบล็อก IP ไปยัง server
            fetch('/api/admin/block-ip', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    ip_address: ipAddress,
                    reason: 'Blocked from suspicious activity monitoring'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '<i class="bi bi-check-circle me-2 text-success"></i>บล็อกสำเร็จ!',
                        html: `
                            <div class="alert alert-success border-0">
                                <h6 class="alert-heading">การดำเนินการสำเร็จ</h6>
                                <p class="mb-0">IP Address <code>${ipAddress}</code> ถูกบล็อกเรียบร้อยแล้ว</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>รีโหลดหน้า',
                        customClass: {
                            popup: 'border-0 shadow-lg'
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: '<i class="bi bi-x-circle me-2 text-danger"></i>เกิดข้อผิดพลาด!',
                    html: `
                        <div class="alert alert-danger border-0">
                            <h6 class="alert-heading">ไม่สามารถบล็อก IP ได้</h6>
                            <p class="mb-0">${error.message}</p>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: '<i class="bi bi-check-circle me-1"></i>ตกลง',
                    customClass: {
                        popup: 'border-0 shadow-lg'
                    }
                });
            });
        }
    });
}

// ฟังก์ชันรีเฟรชข้อมูล
function refreshData() {
    Swal.fire({
        title: '<i class="bi bi-arrow-clockwise me-2"></i>กำลังรีเฟรชข้อมูล...',
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">กำลังโหลด...</span>
                </div>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        timer: 1500,
        customClass: {
            popup: 'border-0 shadow-lg'
        }
    }).then(() => {
        location.reload();
    });
}

// Enhanced CSS Styles
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        /* Hover Effects */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        /* Background Soft Colors */
        .bg-soft {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }
        .bg-danger.bg-soft { background-color: rgba(220, 53, 69, 0.1) !important; }
        .bg-warning.bg-soft { background-color: rgba(255, 193, 7, 0.1) !important; }
        .bg-info.bg-soft { background-color: rgba(13, 202, 240, 0.1) !important; }
        .bg-secondary.bg-soft { background-color: rgba(108, 117, 125, 0.1) !important; }
        .bg-success.bg-soft { background-color: rgba(25, 135, 84, 0.1) !important; }

        /* Avatar Sizes */
        .avatar-sm { width: 2.25rem; height: 2.25rem; }
        .avatar-lg { width: 3.5rem; height: 3.5rem; }
        .avatar-xl { width: 4.5rem; height: 4.5rem; }

        /* Custom Table Styles */
        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
            border-bottom-width: 1px;
        }
        .table tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }

        /* Card Enhancements */
        .card {
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .card-header {
            border-radius: 0.75rem 0.75rem 0 0 !important;
        }

        /* Button Enhancements */
        .btn-sm.rounded-pill {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        /* Badge Improvements */
        .badge {
            font-weight: 500;
            letter-spacing: 0.025em;
        }

        /* Loading Animation */
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .pulse { animation: pulse 2s infinite; }
    `;
    document.head.appendChild(style);
});
</script>

<!-- Required Libraries -->
@if(!isset($scriptLoaded))
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@php $scriptLoaded = true; @endphp
@endif

@endpush
@endsection