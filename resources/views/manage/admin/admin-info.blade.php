@php
    $userType = auth()->user()->user_type;
    $layout = $userType === 'super_admin' ? 'layouts.super-admin' : 'layouts.admin';
@endphp
@extends($layout)

@section('title', 'ข้อมูลผู้ดูแลระบบ')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manage/manage.css') }}">
@endpush
@section('content')
    <section class="mt-5">
        <article class="box-register">
            <div style="float: left;position: fixed;">
                <button type="button" class="btn-insert" onclick="window.history.back()"><i
                        class="bi bi-arrow-left"></i>ย้อนกลับ</button>
            </div>
            <div class="box-header">
                <img src="{{ asset('images/profile/profile.png') }}" alt="profile default">
                <h4>ข้อมูลผู้ดูแลระบบ</h4>
                <p style="color: var(--color-8)">โปรดตรวจสอบข้อมูลให้ถูกต้อง หากพบข้อผิดพลาดกรุณาแจ้งผู้ดูแลระบบใหญ่ Super
                    Admin หรือแจ้งผู้เกี่ยวข้อง</p>
            </div>
            <div class="box-form">
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
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="submit" class="btn-confirmed"><i class="bi bi-save"></i> อัพเดทข้อมูล</button>
                        </div>
                        <div style="text-align: center; margin-top: 20px;">
                            <a href="javascript:void(0);" class="text-danger">ลบข้อมูลผู้ใช้งาน</a>
                        </div>
                    </form>
                @else
                    <form>
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
        </article>
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