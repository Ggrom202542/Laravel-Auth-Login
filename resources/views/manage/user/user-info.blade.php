@php
    $userType = auth()->user()->user_type;
    $layout = $userType === 'super_admin' ? 'layouts.super-admin' : 'layouts.admin';
@endphp
@extends($layout)

@section('title', 'ข้อมูลผู้ใช้งาน')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile/information.css') }}">
@endpush
@section('content')
    <section class="mt-5">
        <div class="container">
            <div class="profile-header">
                <img src="{{ $user->avatar ? asset('images/profile/' . $user->id . '/' . $user->avatar) : asset('images/profile/profile.png') }}"
                    alt="profile">
                <div class="mt-4">
                    <p>อีเมล : {{ $user->email }}</p>
                    <p>เบอร์โทร : {{ $user->phone }}</p>
                </div>
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
                @if (Auth::user()->user_type === 'super_admin')
                    <form action="{{ route('super_admin.updateUserInfo', $user->id) }}" method="post"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="prefix">คำนำหน้า</label>
                            <input type="text" name="prefix" id="prefix" class="form-control" value="{{ $user->prefix }}">
                            @error('prefix')
                                <div class="text-danger m-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="name">ชื่อ</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
                            @error('name')
                                <div class="text-danger m-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">อีเมล</label>
                            <input type="text" name="email" id="email" class="form-control" value="{{ $user->email }}">
                            @error('email')
                                <div class="text-danger m-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="phone">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ $user->phone }}">
                            @error('phone')
                                <div class="text-danger m-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="user_type">ประเภทผู้ใช้งาน</label>
                            <select name="user_type" id="user_type" class="form-control">
                                <option value="user" {{ $user->user_type === 'user' ? 'selected' : '' }}>ผู้ใช้งานทั่วไป</option>
                                <option value="admin" {{ $user->user_type === 'admin' ? 'selected' : '' }}>ผู้ดูแลระบบ</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-confirmed"><i class="bi bi-floppy"></i>บันทึก</button>
                    </form>
                @elseif(Auth::user()->user_type === 'admin')
                    <form action="{{ route('admin.updateUserInfo', $user->id) }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="prefix">คำนำหน้า</label>
                            <input type="text" name="prefix" id="prefix" class="form-control" value="{{ $user->prefix }}">
                            @error('prefix')
                                <div class="text-danger m-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="name">ชื่อ</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
                            @error('name')
                                <div class="text-danger m-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">อีเมล</label>
                            <input type="text" name="email" id="email" class="form-control" value="{{ $user->email }}">
                            @error('email')
                                <div class="text-danger m-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="phone">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ $user->phone }}">
                            @error('phone')
                                <div class="text-danger m-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn-confirmed"><i class="bi bi-floppy"></i>บันทึก</button>
                    </form>
                @endif
                <div style="text-align: center; margin-top: 20px;">
                    <a href="javascript:void(0);" class="text-danger"
                        onclick="confirmDelete('{{ route('admin.deleteUser', $user->id) }}')">ลบบัญชีผู้ใช้งาน</a>
                </div>
            </div>
        </div>
    </section>
    <script src="{{ asset('js/button/button.js') }}"></script>
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