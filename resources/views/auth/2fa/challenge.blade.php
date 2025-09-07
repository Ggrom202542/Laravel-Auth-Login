@extends('layouts.app')

@section('title', 'ยืนยันตัวตน Two-Factor Authentication')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="bi bi-shield-lock"></i>
                        ต้องยืนยันตัวตนด้วย Two-Factor Authentication
                    </h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-phone" style="font-size: 3rem; color: #ffc107;"></i>
                        <h5 class="mt-3">กรอกรหัสยืนยัน</h5>
                        <p class="text-muted">
                            กรุณากรอกรหัส 6 หลักจากแอป Authenticator เพื่อเสร็จสิ้นการเข้าสู่ระบบ
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('2fa.verify') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="code" class="form-label">รหัสยืนยัน</label>
                            <input type="text" 
                                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                                   id="code" 
                                   name="code" 
                                   maxlength="6" 
                                   placeholder="123456"
                                   required 
                                   autocomplete="off"
                                   autofocus
                                   style="letter-spacing: 0.5em; font-size: 1.5rem;">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i>
                                ตรวจสอบรหัส
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <hr>
                        <p class="text-muted small">สูญหายเครื่องมือ Authenticator?</p>
                        <a href="{{ route('2fa.recovery') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-key"></i>
                            ใช้รหัสกู้คืน
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link text-muted btn-sm">
                                <i class="bi bi-box-arrow-right"></i>
                                ยกเลิกและออกจากระบบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    
    // จัดรูปแบบการกรอกรหัสยืนยัน
    codeInput.addEventListener('input', function(e) {
        // ลบตัวอักษรที่ไม่ใช่ตัวเลข
        this.value = this.value.replace(/\D/g, '');
        
        // จำกัดไว้ที่ 6 หลัก
        if (this.value.length > 6) {
            this.value = this.value.substring(0, 6);
        }
    });

    // ส่งฟอร์มอัตโนมัติเมื่อกรอกครบ 6 หลัก
    codeInput.addEventListener('input', function(e) {
        if (this.value.length === 6) {
            // หน่วงเวลาเล็กน้อยเพื่อให้ผู้ใช้เห็นรหัสที่สมบูรณ์
            setTimeout(() => {
                this.form.submit();
            }, 500);
        }
    });

    // โฟกัสที่ช่องกรอก
    codeInput.focus();
});
</script>
@endpush