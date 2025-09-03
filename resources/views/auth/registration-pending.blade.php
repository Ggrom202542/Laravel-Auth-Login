@extends('layouts.app')

@section('title', 'รอการอนุมัติ')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5 text-center">
                    <div class="display-1 text-warning mb-4">📧</div>
                    <h1 class="text-warning mb-3">ส่งคำขอเรียบร้อยแล้ว!</h1>
                    <p class="lead text-muted mb-4">
                        ขอขอบคุณที่สมัครสมาชิกกับเรา เราได้รับข้อมูลของคุณเรียบร้อยแล้ว
                    </p>

                    <div class="bg-warning bg-opacity-10 p-4 rounded mb-4">
                        <h5 class="text-warning mb-3">
                            <i class="fas fa-info-circle"></i> ขั้นตอนต่อไป
                        </h5>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                เราได้ส่งอีเมลยืนยันไปยังที่อยู่อีเมลของคุณแล้ว
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock text-warning"></i>
                                ทีมงานจะทำการตรวจสอบข้อมูลและอนุมัติภายใน 1-3 วันทำการ
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope text-info"></i>
                                เราจะแจ้งผลการพิจารณาผ่านอีเมลให้คุณทราบ
                            </li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-lightbulb"></i> เคล็ดลับ
                        </h6>
                        <p class="mb-0">
                            คุณสามารถตรวจสอบอีเมลในกล่อง Inbox หรือ Spam 
                            เพื่อดูลิงก์ตรวจสอบสถานะการสมัครแบบเรียลไทม์
                        </p>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary me-md-2">
                            <i class="fas fa-sign-in-alt"></i> ไปหน้าเข้าสู่ระบบ
                        </a>
                        <a href="{{ route('welcome') }}" class="btn btn-secondary">
                            <i class="fas fa-home"></i> กลับหน้าหลัก
                        </a>
                    </div>

                    <div class="mt-4 pt-4 border-top">
                        <p class="text-muted small">
                            <i class="fas fa-question-circle"></i>
                            หากคุณไม่ได้รับอีเมลภายใน 24 ชั่วโมง กรุณาติดต่อทีมสนับสนุน
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
