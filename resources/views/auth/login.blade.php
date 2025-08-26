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
        <h2 class="login-title">เข้าสู่ระบบ {{ config('app.name', 'Laravel') }}</h2>
        <p class="login-desc">กรุณาเข้าสู่ระบบเพื่อเข้าใช้งาน</p>
        <div class="login-form" style="text-align: start; width: 60%;"><hr>
            <form action="{{ route('login') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label">บัญชีผู้ใช้งาน</label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required>
                    @error('username')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">รหัสผ่าน</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right"></i>เข้าสู่ระบบ</button>
            </form>
            <div class="mt-4" style="text-align: center;">
                <a href="{{ route('register') }}">ยังไม่มีบัญชีผู้ใช้งาน? ลงทะเบียนที่นี่</a>
            </div><br>
        </div>
    </div>
</div>
@if (session('success'))
    <script>
        Swal.fire({
            title: "สำเร็จ!",
            icon: "success",
            text: "{{ session('success') }}",
            draggable: true
        });
    </script>
@endif
@endsection
