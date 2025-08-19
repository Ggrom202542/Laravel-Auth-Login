@php
    $userType = auth()->user()->user_type;
    $layout = $userType === 'super_admin' ? 'layouts.super-admin' : 'layouts.admin';
@endphp
@extends($layout)

@section('title', 'ข้อมูลผู้ดูแลระบบ')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manage/manage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile/information.css') }}">
@endpush
@section('content')
    <section class="mt-5">
        <div class="container">
            <div class="profile-header">
                <img src="{{ $admin->avatar ? asset('images/profile/' . $admin->id . '/' . $admin->avatar) : asset('images/profile/profile.png') }}"
                    alt="profile">
                <div class="mt-4">
                    <p>อีเมล : {{ $admin->email }}</p>
                    <p>เบอร์โทร : {{ $admin->phone }}</p>
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
                @if($userType === 'super_admin')
                    <form action="{{ route('super_admin.updateAdminInfo', $admin->id) }}" method="post">
                        @csrf
                        <div>
                            <label for="prefix">คำนำหน้า</label>
                            <select id="prefix" name="prefix" class="form-control">
                                <option value="นาย" {{ $admin->prefix == 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ $admin->prefix == 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ $admin->prefix == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                            </select>
                        </div>
                        <div>
                            <label for="name">ชื่อผู้ใช้งาน</label>
                            <input type="text" id="name" name="name" value="{{ $admin->name }}" class="form-control">
                        </div>
                        <div>
                            <label for="phone">เบอร์โทรศัพท์</label>
                            <input type="text" id="phone" name="phone" value="{{ $admin->phone }}" class="form-control">
                        </div>
                        <div>
                            <label for="email">อีเมล</label>
                            <input type="text" id="email" name="email" value="{{ $admin->email }}" class="form-control">
                        </div>
                        <div>
                            <label for="username">บัญชีผู้ใช้งาน</label>
                            <input type="text" id="username" name="username" value="{{ $admin->username }}"
                                class="form-control">
                        </div>
                        <div>
                            <label for="usertype">ประเภทผู้ใช้งาน</label>
                            <select id="usertype" name="usertype" class="form-control">
                                <option value="user" {{ $admin->user_type == 'user' ? 'selected' : '' }}>ผู้ใช้งานทั่วไป</option>
                                <option value="admin" {{ $admin->user_type == 'admin' ? 'selected' : '' }}>ผู้ดูแลระบบ</option>
                                <option value="super_admin" {{ $admin->user_type == 'super_admin' ? 'selected' : '' }}>
                                    ผู้ดูแลระบบใหญ่</option>
                            </select>
                        </div>
                        <div style="text-align: right;">
                            <button type="submit" class="btn-confirmed"><i class="bi bi-save"></i> อัพเดทข้อมูล</button>
                        </div>
                        <div style="text-align: center; margin-top: 10px;">
                            <a href="javascript:void(0);" class="text-danger" onclick="confirmDelete('{{ route('super_admin.deleteAdmin', $admin->id) }}')">ลบข้อมูลผู้ใช้งาน</a>
                        </div>
                    </form>
                @else
                    <form action="{{ route('admin.updateUserInfo', $admin->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <label for="prefix">คำนำหน้า</label>
                            <select id="prefix" name="prefix" class="form-control" disabled>
                                <option value="นาย" {{ $admin->prefix == 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ $admin->prefix == 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ $admin->prefix == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                            </select>
                        </div>
                        <div>
                            <label for="name">ชื่อผู้ใช้งาน</label>
                            <input type="text" id="name" name="name" value="{{ $admin->name }}" class="form-control" readonly>
                        </div>
                        <div>
                            <label for="phone">เบอร์โทรศัพท์</label>
                            <input type="text" id="phone" name="phone" value="{{ $admin->phone }}" class="form-control"
                                readonly>
                        </div>
                        <div>
                            <label for="email">อีเมล</label>
                            <input type="text" id="email" name="email" value="{{ $admin->email }}" class="form-control"
                                readonly>
                        </div>
                        <div>
                            <label for="username">บัญชีผู้ใช้งาน</label>
                            <input type="text" id="username" name="username" value="{{ $admin->username }}" class="form-control"
                                readonly>
                        </div>
                    </form>
                @endif
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