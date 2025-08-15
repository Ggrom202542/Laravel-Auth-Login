@extends('layouts.user')

@section('title', 'ข้อมูลส่วนตัว')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile/information.css') }}">
@endpush

@section('content')
    <section class="mt-5">
        <div class="container">
            <div class="profile-header">
                <img src="{{ auth()->user()->avatar ? asset('images/profile/' . auth()->user()->id . '/' . auth()->user()->avatar) : asset('images/profile/profile.png') }}"
                    alt="profile">
                <div class="mt-4">
                    <p>อีเมล : {{ auth()->user()->email }}</p>
                    <p>เบอร์โทร : {{ auth()->user()->phone }}</p>
                </div>
            </div>
            <div class="profile-info">
                <div class="mt-4 info-title">
                    <h2>ข้อมูลส่วนตัว</h2>
                    <p class="text-muted">โปรดกรอกข้อมูลส่วนตัวของคุณให้ครบถ้วนและถูกต้อง เพื่อความถูกต้องในการใช้งานระบบ
                        หากข้อมูลมีการเปลี่ยนแปลง กรุณาแก้ไขให้เป็นปัจจุบัน</p>
                    <hr>
                </div>
                <form action="{{ route('user.updateInformation') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="prefix">คำนำหน้า</label>
                        <input type="text" name="prefix" id="prefix" class="form-control"
                            value="{{ auth()->user()->prefix }}">
                        @error('prefix')
                            <div class="text-danger m-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">ชื่อ</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ auth()->user()->name }}">
                        @error('name')
                            <div class="text-danger m-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="email">อีเมล</label>
                        <input type="text" name="email" id="email" class="form-control" value="{{ auth()->user()->email }}">
                        @error('email')
                            <div class="text-danger m-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="phone">เบอร์โทรศัพท์</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ auth()->user()->phone }}">
                        @error('phone')
                            <div class="text-danger m-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="image">รูปภาพ</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                        @error('image')
                            <div class="text-danger m-1">{{ $message }}</div>
                        @enderror
                    </div>
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