@extends        <h1 class="h3 mb-4 text-gray-800">
            <i class="bi bi-pencil-square"></i> แก้ไขข้อมูลผู้ใช้
        </h1>ayouts.dashboard')

@section('title', 'Super Admin - แก้ไขข้อมูลผู้ใช้')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> แก้ไขข้อมูลผู้ใช้
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('super-admin.users.index') }}">จัดการผู้ใช้</a></li>
                <li class="breadcrumb-item"><a href="{{ route('super-admin.users.show', $user->id) }}">{{ $user->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">แก้ไข</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- User Edit Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-person-square"></i> ข้อมูลผู้ใช้
                    </h6>
                </div>
                <div class="card-body">
                    <form id="editUserForm" action="{{ route('super-admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" value="{{ old('username', $user->username) }}" required>
                                <small class="form-text text-muted">ใช้สำหรับเข้าสู่ระบบ</small>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Section (Optional) -->
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <strong>หมายเหตุ:</strong> หากต้องการเปลี่ยนรหัสผ่าน กรุณากรอกรหัสผ่านใหม่ หากไม่ต้องการเปลี่ยนให้เว้นว่างไว้
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">รหัสผ่านใหม่ (เว้นว่างหากไม่เปลี่ยน)</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" minlength="8">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" minlength="8">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role & Status -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">บทบาท <span class="text-danger">*</span></label>
                                <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                                    <option value="">-- เลือกบทบาท --</option>
                                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>ผู้ใช้ทั่วไป</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">สถานะ <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">-- เลือกสถานะ --</option>
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>ใช้งานได้</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
                                    <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>ระงับการใช้งาน</option>
                                    <option value="pending" {{ old('status', $user->status) == 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
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
                                      id="address" name="address" rows="3" placeholder="ที่อยู่...">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current User Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle"></i> ข้อมูลปัจจุบัน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($user->profile_image)
                            <img class="rounded-circle" src="{{ asset('storage/profiles/'.$user->profile_image) }}" 
                                 style="width: 80px; height: 80px;" alt="{{ $user->name }}">
                        @else
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white mx-auto"
                                 style="width: 80px; height: 80px; font-size: 24px;">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                        @endif
                    </div>

                    <table class="table table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>สมาชิกเมื่อ:</strong></td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>เข้าสู่ระบบล่าสุด:</strong></td>
                            <td>
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('d/m/Y H:i') }}
                                @else
                                    ยังไม่เคยเข้าสู่ระบบ
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>เซสชันที่ใช้งาน:</strong></td>
                            <td>
                                @php
                                    $activeSessions = $user->adminSessions()->where('status', 'active')->count();
                                @endphp
                                <span class="badge badge-{{ $activeSessions > 0 ? 'success' : 'secondary' }}">
                                    {{ $activeSessions }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Super Admin Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs"></i> การตั้งค่าขั้นสูง
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Two-Factor Authentication -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="two_fa_enabled" name="two_fa_enabled" 
                                   value="1" {{ old('two_fa_enabled', $user->two_fa_enabled) ? 'checked' : '' }}>
                            <label class="form-check-label" for="two_fa_enabled">
                                <i class="fas fa-lock text-success"></i> เปิดใช้งาน Two-Factor Authentication
                            </label>
                        </div>
                        <small class="form-text text-muted">ผู้ใช้จะต้องตั้งค่า 2FA ในการเข้าสู่ระบบ</small>
                    </div>

                    <!-- IP Restrictions -->
                    <div class="mb-3">
                        <label for="ip_restrictions" class="form-label">จำกัด IP Address</label>
                        <textarea class="form-control" id="ip_restrictions" name="ip_restrictions" rows="3" 
                                  placeholder="192.168.1.1&#10;192.168.1.0/24&#10;203.154.*.* (หนึ่งบรรทัดต่อหนึ่ง IP)">{{ old('ip_restrictions', $user->ip_restrictions) }}</textarea>
                        <small class="form-text text-muted">เว้นว่างไว้หากไม่ต้องการจำกัด IP</small>
                    </div>

                    <!-- Session Timeout -->
                    <div class="mb-3">
                        <label for="session_timeout" class="form-label">Timeout เซสชัน (นาที)</label>
                        <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                               min="5" max="480" value="{{ old('session_timeout', $user->session_timeout ?? 60) }}">
                        <small class="form-text text-muted">5-480 นาที (8 ชั่วโมง)</small>
                    </div>

                    <!-- Allowed Login Methods -->
                    <div class="mb-3">
                        <label class="form-label">วิธีการเข้าสู่ระบบที่อนุญาต</label>
                        @php
                            $allowedMethods = $user->allowed_login_methods ? json_decode($user->allowed_login_methods, true) : ['password'];
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="login_password" 
                                   name="allowed_login_methods[]" value="password" 
                                   {{ in_array('password', $allowedMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="login_password">รหัสผ่าน</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="login_two_factor" 
                                   name="allowed_login_methods[]" value="two_factor"
                                   {{ in_array('two_factor', $allowedMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="login_two_factor">Two-Factor Authentication</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="login_social" 
                                   name="allowed_login_methods[]" value="social"
                                   {{ in_array('social', $allowedMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="login_social">Social Login</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Notes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-sticky-note"></i> หมายเหตุผู้ดูแลระบบ
                    </h6>
                </div>
                <div class="card-body">
                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4" 
                              placeholder="หมายเหตุสำหรับผู้ดูแลระบบ (ผู้ใช้จะไม่เห็น)">{{ old('admin_notes', $user->admin_notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <button type="submit" form="editUserForm" class="btn btn-primary btn-lg mx-2">
                        <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
                    </button>
                    <a href="{{ route('super-admin.users.show', $user->id) }}" class="btn btn-info btn-lg mx-2">
                        <i class="fas fa-eye"></i> ดูข้อมูล
                    </a>
                    <a href="{{ route('super-admin.users.index') }}" class="btn btn-secondary btn-lg mx-2">
                        <i class="fas fa-arrow-left"></i> กลับ
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
    
    // Form submission validation
    $('#editUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const passwordConfirm = $('#password_confirmation').val();
        
        // Only validate if passwords are entered
        if (password || passwordConfirm) {
            if (password !== passwordConfirm) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'รหัสผ่านไม่ตรงกัน',
                    text: 'กรุณาตรวจสอบรหัสผ่านที่กรอกใหม่'
                });
                return;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'รหัสผ่านสั้นเกินไป',
                    text: 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร'
                });
                return;
            }
        }
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
</script>
@endpush
