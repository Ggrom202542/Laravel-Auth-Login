@extends('layouts.dashboard')

@section('title', 'แก้ไขข้อมูลผู้ใช้')

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
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.users.show', $user) }}">รายละเอียด</a>
                    </li>
                    <li class="breadcrumb-item active">แก้ไข</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-pencil-square me-2"></i>
                แก้ไขข้อมูลผู้ใช้
            </h1>
            <p class="mb-0 text-muted">แก้ไขข้อมูล {{ $user->first_name }} {{ $user->last_name }}</p>
        </div>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" id="editUserForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Information Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-person-card me-2"></i>
                            ข้อมูลส่วนตัว
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Profile Image -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">รูปประจำตัว</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="profile-preview">
                                        @if($user->profile_image)
                                            <img id="current-avatar" 
                                                 src="{{ asset('storage/avatars/' . $user->profile_image) }}" 
                                                 class="rounded-circle img-thumbnail" 
                                                 width="100" height="100"
                                                 style="object-fit: cover; width: 100px; height: 100px;"
                                                 alt="Current Profile Picture">
                                        @else
                                            <div id="default-avatar" class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                                 style="width: 100px; height: 100px;">
                                                <i class="bi bi-person-fill text-white" style="font-size: 2.5rem;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control @error('profile_image') is-invalid @enderror" 
                                               id="profile_image" name="profile_image" accept="image/*">
                                        <div class="form-text">
                                            รูปแบบที่รองรับ: JPEG, PNG, JPG ขนาดไม่เกิน 2MB
                                        </div>
                                        @error('profile_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="prefix" class="form-label fw-bold">คำนำหน้า <span class="text-danger">*</span></label>
                                <select class="form-select @error('prefix') is-invalid @enderror" id="prefix" name="prefix" required>
                                    <option value="">เลือกคำนำหน้า</option>
                                    <option value="นาย" {{ old('prefix', $user->prefix) === 'นาย' ? 'selected' : '' }}>นาย</option>
                                    <option value="นาง" {{ old('prefix', $user->prefix) === 'นาง' ? 'selected' : '' }}>นาง</option>
                                    <option value="นางสาว" {{ old('prefix', $user->prefix) === 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                </select>
                                @error('prefix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="first_name" class="form-label fw-bold">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                       id="first_name" name="first_name" 
                                       value="{{ old('first_name', $user->first_name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="last_name" class="form-label fw-bold">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                       id="last_name" name="last_name" 
                                       value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" 
                                       value="{{ old('username', $user->username) }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-bold">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" 
                                       value="{{ old('phone', $user->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label fw-bold">อีเมล</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" 
                                       value="{{ old('email', $user->email) }}">
                                <div class="form-text">สามารถเว้นว่างได้</div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Management Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-gear me-2"></i>
                            จัดการบัญชี
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-bold">สถานะบัญชี <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>
                                        ใช้งานได้
                                    </option>
                                    <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>
                                        ไม่ใช้งาน
                                    </option>
                                    <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>
                                        ถูกระงับ
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="approval_status" class="form-label fw-bold">สถานะการอนุมัติ <span class="text-danger">*</span></label>
                                <select class="form-select @error('approval_status') is-invalid @enderror" id="approval_status" name="approval_status" required>
                                    <option value="pending" {{ old('approval_status', $user->approval_status) === 'pending' ? 'selected' : '' }}>
                                        รอการอนุมัติ
                                    </option>
                                    <option value="approved" {{ old('approval_status', $user->approval_status) === 'approved' ? 'selected' : '' }}>
                                        อนุมัติแล้ว
                                    </option>
                                    <option value="rejected" {{ old('approval_status', $user->approval_status) === 'rejected' ? 'selected' : '' }}>
                                        ปฏิเสธ
                                    </option>
                                </select>
                                @error('approval_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="admin_notes" class="form-label fw-bold">หมายเหตุจากแอดมิน</label>
                                <textarea class="form-control @error('admin_notes') is-invalid @enderror" 
                                          id="admin_notes" name="admin_notes" rows="3"
                                          placeholder="เพิ่มหมายเหตุสำหรับผู้ใช้นี้...">{{ old('admin_notes', $user->admin_notes) }}</textarea>
                                <div class="form-text">หมายเหตุนี้จะไม่แสดงให้ผู้ใช้เห็น</div>
                                @error('admin_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Account Lock Settings -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="lock_account" name="lock_account" 
                                           {{ ($user->locked_until && $user->locked_until > now()) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="lock_account">
                                        ล็อคบัญชีชั่วคราว
                                    </label>
                                </div>
                                <div class="form-text">เมื่อเปิดใช้งาน ผู้ใช้จะไม่สามารถเข้าสู่ระบบได้</div>
                            </div>
                        </div>

                        <div id="lock-datetime-section" class="row" style="{{ ($user->locked_until && $user->locked_until > now()) ? '' : 'display: none;' }}">
                            <div class="col-md-12 mb-3">
                                <label for="locked_until" class="form-label fw-bold">ล็อคจนถึงเวลา</label>
                                <input type="datetime-local" class="form-control @error('locked_until') is-invalid @enderror" 
                                       id="locked_until" name="locked_until" 
                                       value="{{ old('locked_until', $user->locked_until ? $user->locked_until->format('Y-m-d\TH:i') : '') }}">
                                <div class="form-text">หากไม่ระบุ จะล็อคไปเรื่อยๆ จนกว่าจะยกเลิก</div>
                                @error('locked_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Action Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-check2-square me-2"></i>
                            บันทึกการเปลี่ยนแปลง
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-2"></i>
                                บันทึกข้อมูล
                            </button>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                                <i class="bi bi-x me-2"></i>
                                ยกเลิก
                            </a>
                            <hr>
                            <button type="button" class="btn btn-warning" 
                                    onclick="resetPassword({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                                <i class="bi bi-key me-2"></i>
                                รีเซ็ตรหัสผ่าน
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User Info Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="bi bi-info-circle me-2"></i>
                            ข้อมูลผู้ใช้
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if($user->profile_image)
                                <img src="{{ asset('storage/avatars/' . $user->profile_image) }}" 
                                     class="rounded-circle img-thumbnail mb-2" 
                                     width="80" height="80"
                                     style="object-fit: cover; width: 80px; height: 80px;"
                                     alt="Profile Picture">
                            @else
                                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                     style="width: 80px; height: 80px;">
                                    <i class="bi bi-person-fill text-white" style="font-size: 2rem;"></i>
                                </div>
                            @endif
                            <h6 class="mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>
                            <small class="text-muted">{{ $user->username }}</small>
                        </div>
                        
                        <div class="border-top pt-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h5 mb-0">{{ $user->login_count ?? 0 }}</div>
                                    <div class="small text-muted">Login</div>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0">
                                        @if($user->created_at)
                                            {{ $user->created_at->diffInDays() }}
                                        @else
                                            0
                                        @endif
                                    </div>
                                    <div class="small text-muted">วัน</div>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0">
                                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'inactive' ? 'warning' : 'danger') }}">
                                            {{ $user->status === 'active' ? 'Active' : ($user->status === 'inactive' ? 'Inactive' : 'Suspended') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="bi bi-lightbulb me-2"></i>
                            คำแนะนำ
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <small>ตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก</small>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                <small>การล็อคบัญชีจะป้องกันการเข้าสู่ระบบ</small>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-shield-check text-info me-2"></i>
                                <small>เฉพาะ Admin เท่านั้นที่แก้ไขข้อมูลได้</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Include Modals -->
@include('admin.users.modals.password-reset')

@endsection

@push('scripts')
<script src="{{ asset('js/admin-users.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile image preview
    const profileImageInput = document.getElementById('profile_image');
    const currentAvatar = document.getElementById('current-avatar');
    const defaultAvatar = document.getElementById('default-avatar');
    
    profileImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (currentAvatar) {
                    currentAvatar.src = e.target.result;
                } else if (defaultAvatar) {
                    // Replace default avatar with image preview
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'rounded-circle img-thumbnail';
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    defaultAvatar.parentNode.replaceChild(img, defaultAvatar);
                }
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Lock account toggle
    const lockAccountCheckbox = document.getElementById('lock_account');
    const lockDatetimeSection = document.getElementById('lock-datetime-section');
    
    lockAccountCheckbox.addEventListener('change', function() {
        if (this.checked) {
            lockDatetimeSection.style.display = 'block';
        } else {
            lockDatetimeSection.style.display = 'none';
            document.getElementById('locked_until').value = '';
        }
    });
    
    // Form validation
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = ['prefix', 'first_name', 'last_name', 'username', 'phone', 'status', 'approval_status'];
        
        requiredFields.forEach(function(fieldName) {
            const field = document.getElementById(fieldName);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
        }
    });
});
</script>
@endpush
