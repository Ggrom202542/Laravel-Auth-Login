@extends('layouts.dashboard')

@section('title', 'เขียนข้อความใหม่')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square text-primary me-2"></i>
            เขียนข้อความใหม่
        </h1>
        <a href="{{ route('messages.index') }}" class="btn btn-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> กลับ
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-envelope me-2"></i>ส่งข้อความใหม่
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('messages.store') }}" method="POST">
                        @csrf
                        
                        <!-- ผู้รับ -->
                        <div class="mb-3">
                            <label for="recipient_id" class="form-label">
                                <i class="bi bi-person me-1"></i>ผู้รับ <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('recipient_id') is-invalid @enderror" 
                                    id="recipient_id" name="recipient_id" required>
                                <option value="">เลือกผู้รับข้อความ</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('recipient_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }} 
                                        ({{ ucfirst($user->role) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('recipient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- หัวข้อ -->
                        <div class="mb-3">
                            <label for="subject" class="form-label">
                                <i class="bi bi-tag me-1"></i>หัวข้อ <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" 
                                   name="subject" 
                                   value="{{ old('subject') }}" 
                                   placeholder="ใส่หัวข้อข้อความ" 
                                   required maxlength="255">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ระดับความสำคัญ -->
                        <div class="mb-3">
                            <label for="priority" class="form-label">
                                <i class="bi bi-exclamation-triangle me-1"></i>ระดับความสำคัญ
                            </label>
                            <select class="form-select @error('priority') is-invalid @enderror" 
                                    id="priority" name="priority" required>
                                <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>
                                    <i class="bi bi-circle-fill text-primary"></i> ปกติ
                                </option>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>
                                    <i class="bi bi-circle-fill text-muted"></i> ต่ำ
                                </option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>
                                    <i class="bi bi-circle-fill text-warning"></i> สูง
                                </option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>
                                    <i class="bi bi-circle-fill text-danger"></i> ด่วน
                                </option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- เนื้อหาข้อความ -->
                        <div class="mb-4">
                            <label for="body" class="form-label">
                                <i class="bi bi-chat-text me-1"></i>เนื้อหาข้อความ <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('body') is-invalid @enderror" 
                                      id="body" 
                                      name="body" 
                                      rows="8" 
                                      placeholder="พิมพ์ข้อความของคุณที่นี่..." 
                                      required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                คุณสามารถพิมพ์ข้อความได้สูงสุด 10,000 ตัวอักษร
                            </div>
                        </div>

                        <!-- ปุ่มดำเนินการ -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('messages.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>ส่งข้อความ
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- คำแนะนำการใช้งาน -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-lightbulb me-2"></i>คำแนะนำการใช้งาน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold"><i class="bi bi-palette me-1"></i>ระดับความสำคัญ:</h6>
                            <ul class="list-unstyled">
                                <li><span class="badge bg-secondary">ต่ำ</span> - ข้อความทั่วไป</li>
                                <li><span class="badge bg-primary">ปกติ</span> - ข้อความธุรกิจปกติ</li>
                                <li><span class="badge bg-warning">สูง</span> - ข้อความสำคัญ</li>
                                <li><span class="badge bg-danger">ด่วน</span> - ต้องการความสนใจทันที</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold"><i class="bi bi-shield-check me-1"></i>ข้อควรระวัง:</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success me-1"></i>ใช้ภาษาที่สุภาพ</li>
                                <li><i class="bi bi-check-circle text-success me-1"></i>ตรวจสอบผู้รับให้ถูกต้อง</li>
                                <li><i class="bi bi-check-circle text-success me-1"></i>หัวข้อควรสื่อความหมาย</li>
                                <li><i class="bi bi-check-circle text-success me-1"></i>อย่าส่งข้อมูลสำคัญ</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ตัวนับตัวอักษรในข้อความ
document.getElementById('body').addEventListener('input', function() {
    const maxLength = 10000;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // สร้าง counter ถ้ายังไม่มี
    let counter = document.getElementById('char-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'char-counter';
        counter.className = 'form-text text-end';
        this.parentNode.appendChild(counter);
    }
    
    counter.innerHTML = `<i class="bi bi-fonts me-1"></i>${currentLength}/${maxLength} ตัวอักษร`;
    
    if (remaining < 100) {
        counter.className = 'form-text text-end text-warning';
    } else if (remaining < 0) {
        counter.className = 'form-text text-end text-danger';
    } else {
        counter.className = 'form-text text-end text-muted';
    }
});

// แสดงไอคอนตามระดับความสำคัญ
document.getElementById('priority').addEventListener('change', function() {
    const priorityIcons = {
        'low': 'text-muted',
        'normal': 'text-primary',
        'high': 'text-warning',
        'urgent': 'text-danger'
    };
    
    // อัปเดตสีของ select
    this.className = 'form-select ' + (priorityIcons[this.value] || '');
});

// เพิ่ม Select2 สำหรับ dropdown ผู้รับ (ถ้ามี)
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('#recipient_id').select2({
            placeholder: 'ค้นหาและเลือกผู้รับข้อความ',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
@endpush
@endsection
