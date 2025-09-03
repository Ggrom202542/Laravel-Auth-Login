@extends('layouts.app')

@section('title', 'รอการอนุมัติ')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-hourglass-split me-2"></i>
                        รอการอนุมัติการสมัครสมาชิก
                    </h4>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-clock-history text-warning" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="text-dark mb-3">คำขอสมัครสมาชิกของคุณได้รับแล้ว!</h5>
                    
                    <div class="alert alert-info">
                        <p class="mb-2">
                            <strong>สถานะ:</strong> รอการตรวจสอบจากผู้ดูแลระบบ
                        </p>
                        <p class="mb-0">
                            <small>กรุณารอการอนุมัติจากผู้ดูแลระบบ คุณจะได้รับอีเมลแจ้งเตือนเมื่อมีการอนุมัติ</small>
                        </p>
                    </div>

                    <div class="row text-start mt-4">
                        <div class="col-md-6">
                            <h6><i class="bi bi-info-circle text-primary me-2"></i>ขั้นตอนต่อไป:</h6>
                            <ul class="list-unstyled ms-3">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    ผู้ดูแลระบบจะตรวจสอบข้อมูลของคุณ
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-envelope text-primary me-2"></i>
                                    คุณจะได้รับอีเมลแจ้งผลการอนุมัติ
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-box-arrow-in-right text-success me-2"></i>
                                    หากได้รับการอนุมัติ คุณสามารถเข้าสู่ระบบได้ทันที
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-clock text-warning me-2"></i>ระยะเวลา:</h6>
                            <ul class="list-unstyled ms-3">
                                <li class="mb-2">
                                    <strong>ระยะเวลาการอนุมัติ:</strong><br>
                                    <small>ปกติ 1-3 วันทำการ</small>
                                </li>
                                <li class="mb-2">
                                    <strong>หมดอายุคำขอ:</strong><br>
                                    <small>7 วันนับจากวันที่สมัคร</small>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('login') }}" class="btn btn-primary me-2">
                            <i class="bi bi-arrow-left me-1"></i>
                            กลับหน้าเข้าสู่ระบบ
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-person-plus me-1"></i>
                            สมัครสมาชิกใหม่
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h6><i class="bi bi-question-circle text-info me-2"></i>ต้องการความช่วยเหลือ?</h6>
                    <p class="mb-2">
                        หากมีข้อสงสัยหรือไม่ได้รับการตอบกลับภายในระยะเวลาที่กำหนด กรุณาติดต่อ:
                    </p>
                    <ul class="list-unstyled ms-3">
                        <li>
                            <i class="bi bi-envelope text-primary me-2"></i>
                            <a href="mailto:admin@example.com">admin@example.com</a>
                        </li>
                        <li>
                            <i class="bi bi-telephone text-primary me-2"></i>
                            <a href="tel:0000000000">000-000-0000</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.btn {
    border-radius: 8px;
    padding: 0.5rem 1.5rem;
}

.list-unstyled li {
    padding: 0.25rem 0;
}
</style>
@endpush
