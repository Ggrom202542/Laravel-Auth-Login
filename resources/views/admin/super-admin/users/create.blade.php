@extends('layouts.dashboard')

@section('title', 'Super Admin - เพิ่มผู้ใช้ใหม่')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-person-plus-fill"></i> เพิ่มผู้ใช้ใหม่
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('super-admin.users.index') }}">จัดการผู้ใช้</a></li>
                <li class="breadcrumb-item active" aria-current="page">เพิ่มผู้ใช้ใหม่</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- User Creation Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-person-circle"></i> ข้อมูลผู้ใช้
                    </h6>
                </div>
                <div class="card-body">
                    <form id="createUserForm" action="{{ route('super-admin.users.store') }}" method="POST">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" value="{{ old('username') }}" required>
                                <small class="form-text text-muted">ใช้สำหรับเข้าสู่ระบบ (ไม่สามารถเปลี่ยนได้)</small>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required minlength="8">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required minlength="8">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Generator & Strength -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="generatePasswordBtn">
                                    <i class="bi bi-shuffle"></i> สร้างรหัสผ่านอัตโนมัติ
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div id="passwordStrength" style="display: none;">
                                    <label class="form-label small">ความแข็งแรง:</label>
                                    <div class="progress" style="height: 8px;">
                                        <div id="passwordStrengthBar" class="progress-bar" style="width: 0%"></div>
                                    </div>
                                    <small id="passwordStrengthText" class="form-text"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Role & Status -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">บทบาท <span class="text-danger">*</span></label>
                                <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                                    <option value="">-- เลือกบทบาท --</option>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>ผู้ใช้ทั่วไป</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">สถานะ <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">-- เลือกสถานะ --</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : 'selected' }}>ใช้งานได้</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">ที่อยู่</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" placeholder="ที่อยู่...">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Super Admin Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-gear-fill"></i> การตั้งค่าขั้นสูง
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Two-Factor Authentication -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="two_fa_enabled" name="two_fa_enabled" value="1">
                            <label class="form-check-label" for="two_fa_enabled">
                                <i class="bi bi-shield-lock-fill text-success"></i> เปิดใช้งาน Two-Factor Authentication
                            </label>
                        </div>
                        <small class="form-text text-muted">ผู้ใช้จะต้องตั้งค่า 2FA ในการเข้าสู่ระบบครั้งแรก</small>
                    </div>

                    <!-- IP Restrictions -->
                    <div class="mb-3">
                        <label for="ip_restrictions" class="form-label">จำกัด IP Address</label>
                        <textarea class="form-control" id="ip_restrictions" name="ip_restrictions" rows="3" 
                                  placeholder="192.168.1.1&#10;192.168.1.0/24&#10;203.154.*.* (หนึ่งบรรทัดต่อหนึ่ง IP)">{{ old('ip_restrictions') }}</textarea>
                        <small class="form-text text-muted">เว้นว่างไว้หากไม่ต้องการจำกัด IP</small>
                    </div>

                    <!-- Session Timeout -->
                    <div class="mb-3">
                        <label for="session_timeout" class="form-label">Timeout เซสชัน (นาที)</label>
                        <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                               min="5" max="480" value="{{ old('session_timeout', 60) }}">
                        <small class="form-text text-muted">5-480 นาที (8 ชั่วโมง)</small>
                    </div>

                    <!-- Allowed Login Methods -->
                    <div class="mb-3">
                        <label class="form-label">วิธีการเข้าสู่ระบบที่อนุญาต</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="login_password" name="allowed_login_methods[]" value="password" checked>
                            <label class="form-check-label" for="login_password">รหัสผ่าน</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="login_two_factor" name="allowed_login_methods[]" value="two_factor">
                            <label class="form-check-label" for="login_two_factor">Two-Factor Authentication</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="login_social" name="allowed_login_methods[]" value="social">
                            <label class="form-check-label" for="login_social">Social Login</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Notes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-sticky-fill"></i> หมายเหตุผู้ดูแลระบบ
                    </h6>
                </div>
                <div class="card-body">
                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4" 
                              placeholder="หมายเหตุสำหรับผู้ดูแลระบบ (ผู้ใช้จะไม่เห็น)">{{ old('admin_notes') }}</textarea>
                </div>
            </div>

            <!-- Role Permissions Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-info-circle-fill"></i> สิทธิ์ตามบทบาท
                    </h6>
                </div>
                <div class="card-body">
                    <div id="rolePermissionsInfo">
                        <p class="text-muted small">เลือกบทบาทเพื่อดูสิทธิ์ที่เกี่ยวข้อง</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <button type="submit" form="createUserForm" class="btn btn-primary btn-lg mx-2">
                        <i class="bi bi-person-plus-fill"></i> สร้างผู้ใช้
                    </button>
                    <a href="{{ route('super-admin.users.index') }}" class="btn btn-secondary btn-lg mx-2">
                        <i class="bi bi-x-lg"></i> ยกเลิก
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Password visibility toggle
    $('#togglePassword').click(function() {
        togglePasswordVisibility('#password', '#togglePassword');
    });
    
    $('#togglePasswordConfirm').click(function() {
        togglePasswordVisibility('#password_confirmation', '#togglePasswordConfirm');
    });
    
    // Generate password
    $('#generatePasswordBtn').click(function() {
        const password = generateSecurePassword();
        $('#password').val(password);
        $('#password_confirmation').val(password);
        checkPasswordStrength();
        
        // Show passwords temporarily
        $('#password, #password_confirmation').attr('type', 'text');
        setTimeout(() => {
            $('#password, #password_confirmation').attr('type', 'password');
        }, 3000);
    });
    
    // Password strength checker
    $('#password').on('input', checkPasswordStrength);
    
    // Role change handler
    $('#role').change(function() {
        showRolePermissions($(this).val());
    });
    
    // Form submission
    $('#createUserForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate passwords match
        if ($('#password').val() !== $('#password_confirmation').val()) {
            alert('รหัสผ่านไม่ตรงกัน กรุณาตรวจสอบใหม่');
            return;
        }
        
        // Submit form
        this.submit();
    });
});

function togglePasswordVisibility(inputSelector, buttonSelector) {
    const input = $(inputSelector);
    const button = $(buttonSelector);
    const icon = button.find('i');
    
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        input.attr('type', 'password');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
}

function generateSecurePassword() {
    const length = 12;
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*()';
    
    let password = '';
    
    // Ensure at least one character from each set
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += symbols[Math.floor(Math.random() * symbols.length)];
    
    // Fill remaining characters
    const allChars = lowercase + uppercase + numbers + symbols;
    for (let i = password.length; i < length; i++) {
        password += allChars[Math.floor(Math.random() * allChars.length)];
    }
    
    // Shuffle password
    return password.split('').sort(() => Math.random() - 0.5).join('');
}

function checkPasswordStrength() {
    const password = $('#password').val();
    if (password.length === 0) {
        $('#passwordStrength').hide();
        return;
    }
    
    let strength = 0;
    let feedback = [];
    
    if (password.length >= 8) strength += 25;
    else feedback.push('8+ ตัวอักษร');
    
    if (/[a-z]/.test(password)) strength += 25;
    else feedback.push('ตัวเล็ก');
    
    if (/[A-Z]/.test(password)) strength += 25;
    else feedback.push('ตัวใหญ่');
    
    if (/[0-9]/.test(password)) strength += 15;
    else feedback.push('ตัวเลข');
    
    if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
    else feedback.push('สัญลักษณ์');
    
    const strengthBar = $('#passwordStrengthBar');
    const strengthText = $('#passwordStrengthText');
    
    strengthBar.css('width', strength + '%');
    
    if (strength < 40) {
        strengthBar.removeClass().addClass('progress-bar bg-danger');
        strengthText.text('อ่อน: ต้องการ ' + feedback.join(', '));
    } else if (strength < 60) {
        strengthBar.removeClass().addClass('progress-bar bg-warning');
        strengthText.text('ปานกลาง: ' + feedback.join(', '));
    } else if (strength < 80) {
        strengthBar.removeClass().addClass('progress-bar bg-info');
        strengthText.text('ดี: ' + feedback.join(', '));
    } else {
        strengthBar.removeClass().addClass('progress-bar bg-success');
        strengthText.text('แข็งแรง');
    }
    
    $('#passwordStrength').show();
}

function showRolePermissions(role) {
    const permissions = {
        'user': [
            'เข้าสู่ระบบและใช้งานฟีเจอร์พื้นฐาน',
            'จัดการโปรไฟล์ส่วนตัว',
            'ดูข้อมูลที่ได้รับอนุญาต'
        ],
        'admin': [
            'จัดการผู้ใช้ทั่วไป (ดู, สร้าง, แก้ไข)',
            'อนุมัติการสมัครสมาชิก',
            'ดูรายงานและสถิติ',
            'จัดการเนื้อหาและประกาศ'
        ],
        'super_admin': [
            'สิทธิ์ทั้งหมดของ Admin',
            'จัดการผู้ดูแลระบบอื่น ๆ',
            'เปลี่ยนบทบาทของผู้ใช้',
            'จัดการการตั้งค่าระบบ',
            'เข้าถึงข้อมูลความปลอดภัยขั้นสูง'
        ]
    };
    
    const infoDiv = $('#rolePermissionsInfo');
    
    if (!role || !permissions[role]) {
        infoDiv.html('<p class="text-muted small">เลือกบทบาทเพื่อดูสิทธิ์ที่เกี่ยวข้อง</p>');
        return;
    }
    
    let html = '<ul class="small">';
    permissions[role].forEach(permission => {
        html += `<li>${permission}</li>`;
    });
    html += '</ul>';
    
    infoDiv.html(html);
}
</script>
@endpush
