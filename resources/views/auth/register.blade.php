@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login_register/login_register.css') }}">
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
                    <label for="name" class="form-label">ชื่อ - นามสกุล <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">เบอร์โทรศัพท์ <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                    @error('phone')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">อีเมล (ถ้ามี) (ถ้าไม่มี - )</label>
                    <input type="text" class="form-control" id="email" name="email" required>
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
                    <p style="color: var(--color-8); margin-top: 8px;">หมายเหตุ : รหัสผ่านต้องมี 8 หลักขึ้นไป</p>
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
