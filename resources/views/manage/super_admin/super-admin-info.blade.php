@extends('layouts.super-admin')

@section('title', 'ข้อมูลผู้ดูแลระบบใหญ่')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manage/manage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile/information.css') }}">
@endpush
@section('content')
    <section class="mt-5">
        <div class="container">
            <div class="profile-header">
                <img src="{{ $superAdmin->avatar ? asset('images/profile/' . $superAdmin->id . '/' . $superAdmin->avatar) : asset('images/profile/profile.png') }}"
                    alt="profile">
                <div class="mt-4">
                    <p>อีเมล : {{ $superAdmin->email }}</p>
                    <p>เบอร์โทร : {{ $superAdmin->phone }}</p>
                </div><hr>
                <div>
                    <button type="button" class="btn-insert" onclick="window.history.back()"><i class="bi bi-arrow-left"></i>ย้อนกลับ</button>
                    <button type="button" class="btn-insert"><i class="bi bi-bell"></i>แจ้งปัญหา</button>
                </div>
            </div>
            <div class="profile-info">
                <div class="mt-4 info-title">
                    <h2>ตรวจสอบข้อมูลส่วนตัว</h2>
                    <p class="text-muted">การอัพเดท แก้ไขข้อมูลส่วนตัวของบุคคลอื่น ๆ
                        ต้องได้รับอนุญาตจากเจ้าของข้อมูลก่อนทุกครั้ง เพื่อความถูกต้องในการใช้งานระบบ</p>
                    <hr>
                </div>
                <form action="{{ route('super_admin.updateSuperAdminInfo', $superAdmin->id) }}" method="post">
                    @csrf
                    
                    <div>
                        <label for="prefix">คำนำหน้า</label>
                        <input type="text" id="prefix" name="prefix" class="form-control" value="{{ $superAdmin->prefix }}" required>
                        @error('prefix')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="name">ชื่อ - นามสกุล</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ $superAdmin->name }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="email">อีเมล</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ $superAdmin->email }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="phone">เบอร์โทร</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ $superAdmin->phone }}" required>
                        @error('phone')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="user_type">ประเภทผู้ใช้งาน</label>
                        <select name="user_type" id="user_type" class="form-control" required>
                            <option value="super_admin" {{ $superAdmin->user_type == 'super_admin' ? 'selected' : '' }}>ผู้ดูแลระบบใหญ่</option>
                            <option value="admin" {{ $superAdmin->user_type == 'admin' ? 'selected' : '' }}>ผู้ดูแลระบบ</option>
                            <option value="user" {{ $superAdmin->user_type == 'user' ? 'selected' : '' }}>ผู้ใช้งานทั่วไป</option>
                        </select>
                        @error('user_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="username">บัญชีผู้ใช้งาน</label>
                        <input type="text" id="username" name="username" class="form-control" value="{{ $superAdmin->username }}" required>
                        @error('username')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <button type="submit" class="btn-confirmed"><i class="bi bi-floppy"></i>บันทึกข้อมูล</button>
                    </div>
                    <div style="text-align: center; margin-top: 10px;">
                        <a href="javascript:void(0);" class="text-danger">ลบบัญชีนี้ออก</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection