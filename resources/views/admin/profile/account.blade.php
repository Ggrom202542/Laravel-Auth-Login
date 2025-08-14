@extends('layouts.admin')

@section('title', 'บัญชีผู้ใช้')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile/account.css') }}">
@endpush

@section('content')
    <section class="mt-5">
        <div class="container">
            <div class="account-header">
                <div>
                    <img src="{{ asset('images/profile/' . Auth::user()->id . '/' . Auth::user()->avatar) }}" alt="profile">
                </div>
                <div class="mt-4">
                    <p>อีเมล : {{ Auth::user()->email }}</p>
                    <p>เบอร์โทรศัพท์ : {{ Auth::user()->phone }}</p>
                </div>
            </div>
            <div class="account-body">
                <div class="mt-4 body-title">
                    <h2>ข้อมูลบัญชีผู้ใช้</h2>
                    <p class="text-muted">โปรดกรอกข้อมูลส่วนตัวของคุณให้ครบถ้วนและถูกต้อง เพื่อความถูกต้องในการใช้งานระบบ
                        หากข้อมูลมีการเปลี่ยนแปลง กรุณาแก้ไขให้เป็นปัจจุบัน</p><hr>
                </div>
                <form action="{{ route('admin.updateAccountSettings') }}" method="post">
                    @csrf

                    <div class="form-group">
                        <label for="email">อีเมลสำรอง (สำหรับกู้คืนรหัสผ่าน)</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ Auth::user()->email }}">
                        @error('email')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="username">บัญชีผู้ใช้</label>
                        <input type="text" name="username" id="username" class="form-control" value="{{ Auth::user()->username }}">
                        @error('username')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password">รหัสผ่านใหม่</label>
                        <input type="password" name="password" id="password" class="form-control">
                        @error('password')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        @error('password_confirmation')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <p style="color: var(--color-warning);">* หากเปลี่ยนรหัสผ่านใหม่ ระบบจะทำการออกจากระบบอัตโนมัติ เพื่อให้เข้าสู่ระบบใหม่อีกครั้ง</p>
                    <button type="submit" class="btn-confirmed"><i class="bi bi-floppy"></i>บันทึก</button>
                </form>
            </div>
        </div>
    </section>
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