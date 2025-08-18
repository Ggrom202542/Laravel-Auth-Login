@extends('layouts.admin')

@section('title', 'ลงทะเบียนผู้ใช้งาน')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manage/manage.css') }}">
@endpush
@section('content')
    <section class="mt-5">
        <article class="box-register">
            <div style="float: left;position: fixed;">
                <button type="button" class="btn-insert" onclick="location.href='{{ route('admin.userManagement') }}'"><i class="bi bi-arrow-left"></i>ย้อนกลับ</button>
            </div>
            <div class="box-header">
                <img src="{{ asset('images/profile/profile.png') }}" alt="profile default">
                <h4>ผู้ลงทะเบียนเข้าใช้งาน</h4>
                <p style="color: var(--color-8)">โปรดตรวจสอบข้อมูลให้ถูกต้อง อาจมีผู้ใช้งานคนอื่นที่ใช้ข้อมูลนี้ เพื่อใช้งานระบบของคุณ อาจทำให้เกิดอันตรายต่อระบบได้</p>
            </div>
            <div class="box-form">
                <form action="{{ route('admin.registerUserInsert', $registration->id) }}" method="post">
                    @csrf
                    <div>
                        <label for="prefix">คำนำหน้า</label>
                        <select id="prefix" name="prefix" class="form-control">
                            <option value="นาย" {{ $registration->prefix == 'นาย' ? 'selected' : '' }}>นาย</option>
                            <option value="นาง" {{ $registration->prefix == 'นาง' ? 'selected' : '' }}>นาง</option>
                            <option value="นางสาว" {{ $registration->prefix == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                        </select>
                    </div>
                    <div>
                        <label for="name">ชื่อผู้ใช้งาน</label>
                        <input type="text" id="name" name="name" value="{{ $registration->name }}" class="form-control" readonly>
                    </div>
                    <div>
                        <label for="phone">เบอร์โทรศัพท์</label>
                        <input type="text" id="phone" name="phone" value="{{ $registration->phone }}" class="form-control" readonly>
                    </div>
                    <div>
                        <label for="email">อีเมล</label>
                        <input type="email" id="email" name="email" value="{{ $registration->email }}" class="form-control" readonly>
                    </div>
                    <div>
                        <label for="username">บัญชีผู้ใช้งาน</label>
                        <input type="text" id="username" name="username" value="{{ $registration->username }}" class="form-control" readonly>
                    </div>
                    <div>
                        <button type="submit" class="btn-confirmed"><i class="bi bi-check-lg"></i>อนุมัติ</button>
                    </div>
                </form>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="javascript:void(0);"  class="text-danger" onclick="confirmDelete('{{ route('admin.deleteRegisteredUser', $registration->id) }}')">ลบข้อมูลผู้ลงทะเบียน</a>
                </div>
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