@extends('layouts.dashboard')

@section('title', 'แก้ไขโปรไฟล์')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-pencil-square me-2"></i>
                    แก้ไขโปรไฟล์
                </h1>
                <div>
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        กลับ
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>เกิดข้อผิดพลาด!</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Profile Image Upload -->
            <div class="col-xl-4 col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-image me-2"></i>
                            รูปโปรไฟล์
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <div id="avatar-preview">
                                @if($user->profile_image)
                                    <img src="{{ asset('storage/avatars/' . $user->profile_image) }}" 
                                         alt="Profile Picture" 
                                         class="rounded-circle img-thumbnail shadow-sm" 
                                         width="200" height="200"
                                         style="object-fit: cover; width: 200px; height: 200px;">
                                @else
                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" 
                                         style="width: 200px; height: 200px;">
                                        <i class="bi bi-person-fill text-white" style="font-size: 4rem;"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-primary btn-sm me-2" onclick="document.getElementById('avatar').click()">
                                <i class="bi bi-upload me-1"></i>
                                เลือกรูป
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="deleteAvatar" 
                                    style="display: {{ $user->profile_image ? 'inline-block' : 'none' }}">
                                <i class="bi bi-trash me-1"></i>
                                ลบรูป
                            </button>
                        </div>
                        
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            รองรับไฟล์ JPG, PNG, GIF<br>
                            ขนาดไม่เกิน 2MB
                        </div>
                    </div>
                </div>

                <!-- Profile Completion -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-list-check me-2"></i>
                            ความครบถ้วนของโปรไฟล์
                        </h6>
                        
                        @php
                            $requiredFields = ['first_name', 'last_name', 'email', 'phone'];
                            $completedFields = 0;
                            foreach($requiredFields as $field) {
                                if(!empty($user->{$field})) $completedFields++;
                            }
                            $percentage = ($completedFields / count($requiredFields)) * 100;
                        @endphp

                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-{{ $percentage == 100 ? 'success' : 'warning' }}" 
                                 role="progressbar" style="width: {{ $percentage }}%"></div>
                        </div>
                        
                        <small class="text-muted">
                            {{ $completedFields }}/{{ count($requiredFields) }} ฟิลด์ที่จำเป็น
                        </small>

                        @if($percentage < 100)
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    กรุณากรอกข้อมูลให้ครบถ้วน
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-xl-8 col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-person-lines-fill me-2"></i>
                            ข้อมูลส่วนตัว
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="prefix" class="form-label">คำนำหน้า</label>
                                    <select class="form-select" id="prefix" name="prefix">
                                        <option value="">เลือก</option>
                                        <option value="นาย" {{ $user->prefix === 'นาย' ? 'selected' : '' }}>นาย</option>
                                        <option value="นาง" {{ $user->prefix === 'นาง' ? 'selected' : '' }}>นาง</option>
                                        <option value="นางสาว" {{ $user->prefix === 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                        <option value="ดร." {{ $user->prefix === 'ดร.' ? 'selected' : '' }}>ดร.</option>
                                        <option value="ศาสตราจารย์" {{ $user->prefix === 'ศาสตราจารย์' ? 'selected' : '' }}>ศาสตราจารย์</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                                           placeholder="08x-xxx-xxxx">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Personal Details -->
                        <hr class="my-4">
                        <h6 class="text-secondary mb-3">
                            <i class="bi bi-person-badge me-2"></i>
                            ข้อมูลเพิ่มเติม
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">วันเกิด</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" name="date_of_birth" 
                                           value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">เพศ</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                        <option value="">เลือกเพศ</option>
                                        <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>ชาย</option>
                                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>หญิง</option>
                                        <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>อื่นๆ</option>
                                        <option value="prefer_not_to_say" {{ old('gender', $user->gender) === 'prefer_not_to_say' ? 'selected' : '' }}>ไม่ระบุ</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">เกี่ยวกับฉัน</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" 
                                      rows="4" placeholder="เขียนอะไรเกี่ยวกับตัวเอง...">{{ old('bio', $user->bio) }}</textarea>
                            <div class="form-text">สูงสุด 1,000 ตัวอักษร</div>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address Information -->
                        <hr class="my-4">
                        <h6 class="text-secondary mb-3">
                            <i class="bi bi-geo-alt me-2"></i>
                            ที่อยู่
                        </h6>

                        <div class="mb-3">
                            <label for="address" class="form-label">ที่อยู่</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" 
                                      rows="2" placeholder="เลขที่ หมู่ ตำบล อำเภอ...">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">เมือง/อำเภอ</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', $user->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="state" class="form-label">จังหวัด/รัฐ</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state', $user->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">รหัสไปรษณีย์</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="country" class="form-label">ประเทศ</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                   id="country" name="country" value="{{ old('country', $user->country) }}" 
                                   placeholder="ประเทศไทย">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-1"></i>
                                ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                <i class="bi bi-check-lg me-1"></i>
                                <span class="btn-text">บันทึกข้อมูล</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile image preview
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');

    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Preview image
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.innerHTML = `
                    <img src="${e.target.result}" 
                         alt="Profile Picture Preview" 
                         class="rounded-circle img-thumbnail shadow-sm" 
                         width="200" height="200"
                         style="object-fit: cover; width: 200px; height: 200px;">
                `;
            };
            reader.readAsDataURL(file);
            
            // Auto upload
            uploadAvatar(file);
        }
    });

    // Upload avatar function
    function uploadAvatar(file) {
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', '{{ csrf_token() }}');

        // Show uploading state
        const alert = document.createElement('div');
        alert.className = 'alert alert-info alert-dismissible fade show mt-3';
        alert.id = 'upload-alert';
        alert.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span>กำลังอัพโหลดรูปโปรไฟล์...</span>
            </div>
        `;
        const container = document.querySelector('.container-fluid');
        const existingAlert = document.getElementById('upload-alert');
        if (existingAlert) existingAlert.remove();
        container.insertBefore(alert, container.firstChild);

        fetch('{{ route("profile.upload-avatar") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
        })
        .then(response => response.json())
        .then(data => {
            // Remove uploading alert
            const uploadAlert = document.getElementById('upload-alert');
            if (uploadAlert) uploadAlert.remove();

            if (data.success) {
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show mt-3';
                successAlert.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                container.insertBefore(successAlert, container.firstChild);

                // Update avatar preview with uploaded image
                avatarPreview.innerHTML = `
                    <img src="${data.avatar_url}" 
                         alt="Profile Picture" 
                         class="rounded-circle img-thumbnail shadow-sm" 
                         width="200" height="200"
                         style="object-fit: cover; width: 200px; height: 200px;">
                `;

                // Show delete button if not already shown
                const deleteBtn = document.getElementById('deleteAvatar');
                if (deleteBtn) {
                    deleteBtn.style.display = 'inline-block';
                }

                // Auto-dismiss success alert after 3 seconds
                setTimeout(() => {
                    if (successAlert && successAlert.parentNode) {
                        successAlert.remove();
                    }
                }, 3000);
            } else {
                // Show error message
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show mt-3';
                errorAlert.innerHTML = `
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                container.insertBefore(errorAlert, container.firstChild);
            }
        })
        .catch(error => {
            // Remove uploading alert
            const uploadAlert = document.getElementById('upload-alert');
            if (uploadAlert) uploadAlert.remove();

            console.error('Error:', error);
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show mt-3';
            errorAlert.innerHTML = `
                <i class="bi bi-exclamation-triangle me-2"></i>
                เกิดข้อผิดพลาดในการอัพโหลดรูป
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            container.insertBefore(errorAlert, container.firstChild);
        });
    }

    // Delete avatar
    const deleteAvatarBtn = document.getElementById('deleteAvatar');
    if (deleteAvatarBtn) {
        deleteAvatarBtn.addEventListener('click', function() {
            if (confirm('คุณต้องการลบรูปโปรไฟล์หรือไม่?')) {
                fetch('{{ route("profile.delete-avatar") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        avatarPreview.innerHTML = `
                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" 
                                 style="width: 200px; height: 200px;">
                                <i class="bi bi-person-fill text-white" style="font-size: 4rem;"></i>
                            </div>
                        `;
                        deleteAvatarBtn.style.display = 'none';
                        
                        // Show success message
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show mt-3';
                        alert.innerHTML = `
                            <i class="bi bi-check-circle me-2"></i>
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        const container = document.querySelector('.container-fluid');
                        container.insertBefore(alert, container.firstChild);

                        // Auto-dismiss alert after 3 seconds
                        setTimeout(() => {
                            if (alert && alert.parentNode) {
                                alert.remove();
                            }
                        }, 3000);
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการลบรูป');
                });
            }
        });
    }

    // Form submission loading state
    const form = document.querySelector('form');
    const saveBtn = document.getElementById('saveProfileBtn');
    const btnText = saveBtn.querySelector('.btn-text');

    form.addEventListener('submit', function() {
        saveBtn.disabled = true;
        btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>กำลังบันทึก...';
    });

    // Character counter for bio
    const bioTextarea = document.getElementById('bio');
    const maxLength = 1000;
    
    if (bioTextarea) {
        const counterDiv = document.createElement('div');
        counterDiv.className = 'form-text text-end';
        bioTextarea.parentNode.appendChild(counterDiv);
        
        function updateCounter() {
            const remaining = maxLength - bioTextarea.value.length;
            counterDiv.textContent = `เหลือ ${remaining} ตัวอักษร`;
            counterDiv.className = `form-text text-end ${remaining < 50 ? 'text-warning' : ''}`;
        }
        
        bioTextarea.addEventListener('input', updateCounter);
        updateCounter();
    }
});
</script>
@endpush

@push('styles')
<style>
.form-control:focus, .form-select:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.img-thumbnail {
    border-radius: 50% !important;
}

.btn-loading {
    pointer-events: none;
}

.progress {
    border-radius: 10px;
}

.card-header h6 {
    border-bottom: 2px solid #4e73df;
    display: inline-block;
    padding-bottom: 0.25rem;
}
</style>
@endpush
