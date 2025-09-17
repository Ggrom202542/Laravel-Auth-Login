@extends('layouts.dashboard')

@section('title', 'การตั้งค่าระบบ')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">การตั้งค่าระบบ</h1>
            <p class="text-muted">จัดการการตั้งค่าระบบทั้งหมด</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Settings Navigation -->
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear text-primary me-2"></i>หมวดหมู่การตั้งค่า
                    </h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('super-admin.settings.general') }}" class="list-group-item list-group-item-action {{ request()->routeIs('super-admin.settings.general') ? 'active' : '' }}">
                        <i class="bi bi-sliders me-2"></i>การตั้งค่าทั่วไป
                    </a>
                    <a href="{{ route('super-admin.settings.security') }}" class="list-group-item list-group-item-action {{ request()->routeIs('super-admin.settings.security') ? 'active' : '' }}">
                        <i class="bi bi-shield-check me-2"></i>ความปลอดภัย
                    </a>
                    <a href="{{ route('super-admin.settings.email') }}" class="list-group-item list-group-item-action {{ request()->routeIs('super-admin.settings.email') ? 'active' : '' }}">
                        <i class="bi bi-envelope me-2"></i>การตั้งค่าอีเมล
                    </a>
                    <a href="{{ route('super-admin.settings.notifications') }}" class="list-group-item list-group-item-action {{ request()->routeIs('super-admin.settings.notifications') ? 'active' : '' }}">
                        <i class="bi bi-bell me-2"></i>การแจ้งเตือน
                    </a>
                    <a href="{{ route('super-admin.settings.backup') }}" class="list-group-item list-group-item-action {{ request()->routeIs('super-admin.settings.backup') ? 'active' : '' }}">
                        <i class="bi bi-archive me-2"></i>การสำรองข้อมูล
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- System Overview -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-server text-primary" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2">ข้อมูลระบบ</h5>
                            <p class="card-text text-muted">PHP {{ $systemInfo['php_version'] }}</p>
                            <p class="card-text text-muted">Laravel {{ $systemInfo['laravel_version'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-hdd text-success" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2">พื้นที่จัดเก็บ</h5>
                            <p class="card-text text-muted">ว่าง: {{ $systemInfo['disk_free_space'] }}</p>
                            <p class="card-text text-muted">ทั้งหมด: {{ $systemInfo['disk_total_space'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning text-warning me-2"></i>การดำเนินการด่วน
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <form action="{{ route('super-admin.settings.clear-cache') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100" onclick="return confirm('คุณต้องการล้างแคชระบบหรือไม่?')">
                                    <i class="bi bi-arrow-clockwise me-2"></i>ล้างแคช
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4 mb-3">
                            <form action="{{ route('super-admin.settings.optimize') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-success w-100" onclick="return confirm('คุณต้องการปรับปรุงประสิทธิภาพระบบหรือไม่?')">
                                    <i class="bi bi-speedometer2 me-2"></i>ปรับปรุงระบบ
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4 mb-3">
                            <form action="{{ route('super-admin.settings.create-backup') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-info w-100" onclick="return confirm('คุณต้องการสร้างการสำรองข้อมูลหรือไม่?')">
                                    <i class="bi bi-download me-2"></i>สำรองข้อมูล
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle text-info me-2"></i>ข้อมูลระบบโดยละเอียด
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>เวอร์ชัน PHP:</strong></td>
                                    <td>{{ $systemInfo['php_version'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>เวอร์ชัน Laravel:</strong></td>
                                    <td>{{ $systemInfo['laravel_version'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>ฐานข้อมูล:</strong></td>
                                    <td>{{ ucfirst($systemInfo['database_type']) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>เซิร์ฟเวอร์:</strong></td>
                                    <td>{{ $systemInfo['server_software'] }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Memory Limit:</strong></td>
                                    <td>{{ $systemInfo['memory_limit'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Max Execution Time:</strong></td>
                                    <td>{{ $systemInfo['max_execution_time'] }} วินาที</td>
                                </tr>
                                <tr>
                                    <td><strong>Upload Max Size:</strong></td>
                                    <td>{{ $systemInfo['upload_max_filesize'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>สถานะ:</strong></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>ปกติ
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endpush