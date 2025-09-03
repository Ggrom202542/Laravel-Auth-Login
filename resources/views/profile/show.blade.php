@extends('layouts.dashboard')

@section('title', 'โปรไฟล์ของฉัน')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-person-circle me-2"></i>
                    โปรไฟล์ของฉัน
                </h1>
                <div>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>
                        แก้ไขโปรไฟล์
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Profile Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-person-badge me-2"></i>
                        ข้อมูลส่วนตัว
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        @if($user->profile_image)
                            <img src="{{ asset('storage/avatars/' . $user->profile_image) }}" 
                                 alt="Profile Picture" 
                                 class="rounded-circle img-thumbnail shadow-sm" 
                                 width="150" height="150"
                                 style="object-fit: cover; width: 200px; height: 200px;">
                        @else
                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" 
                                 style="width: 150px; height: 150px;">
                                <i class="bi bi-person-fill text-white" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                    </div>
                    
                    <h4 class="mb-1">
                        {{ $user->prefix }}{{ $user->first_name }} {{ $user->last_name }}
                        @if($user->profile_completed)
                            <i class="bi bi-patch-check-fill text-success ms-1" title="โปรไฟล์ครบถ้วน"></i>
                        @endif
                    </h4>
                    
                    <p class="text-muted mb-3">
                        <i class="bi bi-shield-check me-1"></i>
                        {{ ucfirst($user->role) }}
                    </p>

                    @if($user->bio)
                        <p class="text-muted small">
                            "{{ $user->bio }}"
                        </p>
                    @endif

                    <div class="row text-center mt-4">
                        <div class="col">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <h6 class="mb-1 text-primary">สถานะ</h6>
                                    @if($user->status === 'active')
                                        <span class="badge bg-success">ใช้งานได้</span>
                                    @else
                                        <span class="badge bg-danger">ปิดใช้งาน</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <h6 class="mb-1 text-primary">เข้าร่วมเมื่อ</h6>
                                    <small class="text-muted">
                                        {{ $user->created_at->format('M Y') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Card -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-info-circle me-2"></i>
                        รายละเอียดข้อมูล
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Contact Information -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-telephone me-2"></i>
                                ข้อมูลติดต่อ
                            </h6>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small">อีเมล</label>
                                <div class="form-control-plaintext">
                                    <i class="bi bi-envelope me-2"></i>
                                    {{ $user->email }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small">เบอร์โทรศัพท์</label>
                                <div class="form-control-plaintext">
                                    <i class="bi bi-phone me-2"></i>
                                    {{ $user->phone ?: 'ไม่ได้ระบุ' }}
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-person me-2"></i>
                                ข้อมูลส่วนตัว
                            </h6>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small">วันเกิด</label>
                                <div class="form-control-plaintext">
                                    <i class="bi bi-calendar me-2"></i>
                                    {{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'ไม่ได้ระบุ' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small">เพศ</label>
                                <div class="form-control-plaintext">
                                    <i class="bi bi-gender-ambiguous me-2"></i>
                                    @if($user->gender)
                                        @switch($user->gender)
                                            @case('male')
                                                ชาย
                                                @break
                                            @case('female')
                                                หญิง
                                                @break
                                            @case('other')
                                                อื่นๆ
                                                @break
                                            @default
                                                ไม่ระบุ
                                        @endswitch
                                    @else
                                        ไม่ได้ระบุ
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        @if($user->address || $user->city || $user->state || $user->postal_code || $user->country)
                        <div class="col-12 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-geo-alt me-2"></i>
                                ที่อยู่
                            </h6>
                            
                            <div class="form-control-plaintext">
                                <i class="bi bi-house me-2"></i>
                                @php
                                    $addressParts = array_filter([
                                        $user->address,
                                        $user->city,
                                        $user->state,
                                        $user->postal_code,
                                        $user->country
                                    ]);
                                @endphp
                                {{ count($addressParts) > 0 ? implode(', ', $addressParts) : 'ไม่ได้ระบุ' }}
                            </div>
                        </div>
                        @endif

                        <!-- Preferences -->
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-gear me-2"></i>
                                การตั้งค่า
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-muted small">ธีม</label>
                                    <div class="form-control-plaintext">
                                        <i class="bi bi-palette me-2"></i>
                                        {{ $user->theme === 'dark' ? 'โหมดมืด' : 'โหมดสว่าง' }}
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-muted small">ภาษา</label>
                                    <div class="form-control-plaintext">
                                        <i class="bi bi-translate me-2"></i>
                                        {{ $user->language === 'en' ? 'English' : 'ภาษาไทย' }}
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-muted small">การแจ้งเตือน</label>
                                    <div class="form-control-plaintext">
                                        <i class="bi bi-bell me-2"></i>
                                        @if($user->email_notifications || $user->sms_notifications || $user->push_notifications)
                                            เปิดใช้งาน
                                        @else
                                            ปิดใช้งาน
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-1"></i>
                                    แก้ไขโปรไฟล์
                                </a>
                                <a href="{{ route('profile.settings') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-gear me-1"></i>
                                    ตั้งค่า
                                </a>
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="bi bi-key me-1"></i>
                                    เปลี่ยนรหัสผ่าน
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="bi bi-key me-2"></i>
                    เปลี่ยนรหัสผ่าน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.change-password') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                        <div class="form-text">รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">ยืนยันรหัสผ่านใหม่ <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        เปลี่ยนรหัสผ่าน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-control-plaintext {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e3e6f0;
    margin-bottom: 0.5rem;
}

.img-thumbnail {
    border-radius: 50% !important;
}

.card .card-body .row .col h6 {
    border-bottom: 2px solid #4e73df;
    display: inline-block;
    padding-bottom: 0.25rem;
}
</style>
@endpush
