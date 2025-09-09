@extends('layouts.dashboard')

@section('title', 'รายละเอียดความปลอดภัยผู้ใช้')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-person-gear text-primary me-2"></i>
                        รายละเอียดความปลอดภัยผู้ใช้
                    </h1>
                    <p class="text-muted mb-0">จัดการและดูรายละเอียดความปลอดภัยของ {{ $user->username ?? $user->email }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.security.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> กลับ
                    </a>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-person text-primary me-2"></i>
                                ข้อมูลผู้ใช้
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ชื่อผู้ใช้:</strong> {{ $user->username }}</p>
                                    <p><strong>อีเมล:</strong> {{ $user->email }}</p>
                                    <p><strong>บทบาท:</strong> 
                                        <span class="badge bg-primary">{{ $user->role ?? 'user' }}</span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>สถานะ:</strong> 
                                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                            {{ $user->status }}
                                        </span>
                                    </p>
                                    <p><strong>เข้าสู่ระบบล่าสุด:</strong> 
                                        {{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'ยังไม่เคยเข้าสู่ระบบ' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-shield-check text-success me-2"></i>
                                การตั้งค่าความปลอดภัย
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($securityData['security_settings']))
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="ipRestriction" 
                                               {{ $securityData['security_settings']['ip_restriction'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ipRestriction">
                                            จำกัดการเข้าถึงตาม IP
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="deviceVerification" 
                                               {{ $securityData['security_settings']['device_verification'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="deviceVerification">
                                            ตรวจสอบอุปกรณ์
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @else
                            <p class="text-muted">ไม่มีข้อมูลการตั้งค่าความปลอดภัย</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-gear text-warning me-2"></i>
                                การจัดการ
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                @if($user->status === 'active')
                                    <form action="{{ route('admin.security.lock', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning" 
                                                onclick="return confirm('ล็อกบัญชีผู้ใช้นี้?')">
                                            <i class="bi bi-lock me-1"></i> ล็อกบัญชี
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.security.unlock', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" 
                                                onclick="return confirm('ปลดล็อกบัญชีผู้ใช้นี้?')">
                                            <i class="bi bi-unlock me-1"></i> ปลดล็อกบัญชี
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('admin.security.reset-attempts', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-info">
                                        <i class="bi bi-arrow-clockwise me-1"></i> รีเซ็ตความพยายามล้มเหลว
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
