@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login_register/login_register.css') }}">
<link rel="stylesheet" href="{{ asset('css/password-strength-meter.css') }}">
@endpush
@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-logo">
            <img src="{{ asset('images/logo/prapreut-logo.png') }}" alt="Prapreut" width="170px">
        </div>
        <h2 class="login-title">ลงทะเบียน {{ config('app.name', 'Laravel') }}</h2>
        <p class="login-desc">กรุณากรอกข้อมูลเพื่อสร้างบัญชีผู้ใช้งาน</p>
        <div class="login-form" style="text-align: start; width: 60%;"><hr>
            <form action="{{ route('register') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="prefix" class="form-label">คำนำหน้า <span style="color: red;">*</span></label>
                    <select id="prefix" class="form-select" name="prefix" required>
                        <option value="นาย">นาย</option>
                        <option value="นาง">นาง</option>
                        <option value="นางสาว">นางสาว</option>
                    </select>
                    @error('prefix')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="first_name" class="form-label">ชื่อ <span style="color: red;">*</span></label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                           id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                    @error('first_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">นามสกุล <span style="color: red;">*</span></label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                           id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                    @error('last_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">เบอร์โทรศัพท์ <span style="color: red;">*</span></label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" value="{{ old('phone') }}" required>
                    @error('phone')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">อีเมล (ไม่บังคับ)</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="username">บัญชีผู้ใช้ (Username) <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" required>
                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password">รหัสผ่าน <span style="color: red;">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="text-muted">รหัสผ่านจะถูกตรวจสอบความแข็งแกร่งอัตโนมัติ</small>
                    <!-- Password Strength Meter จะถูกแทรกที่นี่โดย JavaScript -->
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password-confirm">ยืนยันรหัสผ่าน <span style="color: red;">*</span></label>
                    <input type="password" class="form-control" id="password-confirm" name="password_confirmation" required>
                    @error('password_confirmation')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right"></i>ลงทะเบียน</button>
            </form>
            <div class="mt-4" style="text-align: center">
                <a href="{{ route('login') }}">มีบัญชีผู้ใช้แล้ว? ลงชื่อเข้าใช้งานที่นี่</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/password-strength-meter.js') }}"></script>
@endpush
