@extends('layouts.admin')

@section('title', 'ข้อมูลผู้ดูแลระบบ')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manage/manage.css') }}">
@endpush
@section('content')
    <section class="mt-5">
        <article class="box-register">
            <div style="float: left;position: fixed;">
                <button type="button" class="btn-insert" onclick="location.href='{{ route('admin.adminManagement') }}'"><i class="bi bi-arrow-left"></i>ย้อนกลับ</button>
            </div>
            <div class="box-header">
                <img src="{{ asset('images/profile/profile.png') }}" alt="profile default">
                <h4>ข้อมูลผู้ดูแลระบบ</h4>
                <p style="color: var(--color-8)">โปรดตรวจสอบข้อมูลให้ถูกต้อง หากพบข้อผิดพลาดกรุณาแจ้งผู้ดูแลระบบใหญ่ Super Admin หรือแจ้งผู้เกี่ยวข้อง</p>
            </div>
            <div class="box-form">
                <form>
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
                        <input type="text" id="name" name="name" value="{{ $admin->name }}" class="form-control" readonly>
                    </div>
                    <div>
                        <label for="phone">เบอร์โทรศัพท์</label>
                        <input type="text" id="phone" name="phone" value="{{ $admin->phone }}" class="form-control" readonly>
                    </div>
                    <div>
                        <label for="email">อีเมล</label>
                        <input type="email" id="email" name="email" value="{{ $admin->email }}" class="form-control" readonly>
                    </div>
                    <div>
                        <label for="username">บัญชีผู้ใช้งาน</label>
                        <input type="text" id="username" name="username" value="{{ $admin->username }}" class="form-control" readonly>
                    </div>
                </form>
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