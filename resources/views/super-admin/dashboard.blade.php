@extends('layouts.dashboard')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-shield-check me-2"></i>
                    Super Admin Dashboard
                </h1>
                <div class="text-end">
                    <span class="badge bg-danger fs-6 me-2">SUPER ADMIN</span>
                    <br>
                    <small class="text-muted">
                        ล็อกอินล่าสุด: {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d/m/Y H:i') : 'ยังไม่เคยล็อกอิน' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">
                                <i class="bi bi-shield-check me-2"></i>
                                ยินดีต้อนรับ, {{ auth()->user()->name }}!
                            </h4>
                            <p class="card-text mb-0">
                                คุณเข้าใช้งานในฐานะ <strong>ผู้ดูแลระบบสูงสุด (Super Administrator)</strong> 
                                วันนี้เป็นวันที่ {{ now()->format('d/m/Y') }}
                            </p>
                            <small class="opacity-75">
                                มีสิทธิ์ครบถ้วนในการควบคุมระบบทั้งหมด
                            </small>
                        </div>
                        <div class="col-md-4 text-end d-none d-md-block">
                            <i class="bi bi-shield-check opacity-75" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                ผู้ใช้ทั้งหมด
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_users'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Admin
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['admin_count'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-badge" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                ออนไลน์
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['online_users'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                กิจกรรมวันนี้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['today_activities'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-activity" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-danger">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                บัญชีล็อค
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['locked_accounts'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-lock" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-secondary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                บทบาททั้งหมด
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_roles'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-lines-fill" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Performance & Analytics -->
    <div class="row mb-4">
        <!-- System Performance Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        กิจกรรมระบบและประสิทธิภาพ (7 วันที่ผ่านมา)
                    </h6>
                    <div class="dropdown no-arrow">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-list-ul"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">ดูรายงานเต็ม</a></li>
                            <li><a class="dropdown-item" href="#">ส่งออก PDF</a></li>
                            <li><a class="dropdown-item" href="#">ตั้งค่าการเตือน</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="systemPerformanceChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health Status -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-heart-pulse me-2"></i>
                        สถานะระบบ
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($systemHealth))
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="font-weight-bold">สถานะทั่วไป:</span>
                            <span class="badge bg-success fs-6">ปกติ</span>
                        </div>

                        <div class="progress-group mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="small">CPU Usage</span>
                                <span class="small font-weight-bold">{{ $systemHealth['cpu'] ?? '0' }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $systemHealth['cpu'] ?? 0 }}%"></div>
                            </div>
                        </div>

                        <div class="progress-group mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="small">Memory Usage</span>
                                <span class="small font-weight-bold">{{ $systemHealth['memory'] ?? '0' }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: {{ $systemHealth['memory'] ?? 0 }}%"></div>
                            </div>
                        </div>

                        <div class="progress-group mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="small">Disk Usage</span>
                                <span class="small font-weight-bold">{{ $systemHealth['disk'] ?? '0' }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ $systemHealth['disk'] ?? 0 }}%"></div>
                            </div>
                        </div>

                        <hr>

                        <div class="text-center">
                            <div class="row text-center">
                                <div class="col">
                                    <div class="small text-muted">Uptime</div>
                                    <div class="font-weight-bold">{{ $systemHealth['uptime'] ?? '0 วัน' }}</div>
                                </div>
                                <div class="col">
                                    <div class="small text-muted">Load Avg</div>
                                    <div class="font-weight-bold">{{ $systemHealth['load'] ?? '0.00' }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-server mb-3" style="font-size: 3rem; color: #dddfeb;"></i>
                            <p class="text-muted">ข้อมูลระบบไม่พร้อมใช้งาน</p>
                            <button class="btn btn-outline-primary btn-sm">รีเฟรชข้อมูล</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Management Sections -->
    <div class="row mb-4">
        <!-- User Management Overview -->
        <div class="col-xl-6 col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-people-fill me-2"></i>
                        การจัดการผู้ใช้งานขั้นสูง
                    </h6>
                    <a href="{{ route('super-admin.users.index') }}" class="btn btn-danger btn-sm">
                        <i class="bi bi-plus me-1"></i>
                        จัดการทั้งหมด
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($adminUsers) && $adminUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Admin</th>
                                        <th>บทบาท</th>
                                        <th>สถานะ</th>
                                        <th>ล็อกอินล่าสุด</th>
                                        <th>การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adminUsers as $admin)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <img src="{{ $admin->profile_photo ? asset('storage/'.$admin->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode($admin->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                                         alt="{{ $admin->name }}" 
                                                         class="rounded-circle" 
                                                         style="width: 24px; height: 24px;">
                                                </div>
                                                <div>
                                                    <strong>{{ $admin->name }}</strong><br>
                                                    <small class="text-muted">{{ $admin->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $admin->role === 'super_admin' ? 'danger' : 'success' }}">
                                                {{ $admin->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($admin->status === 'active')
                                                <span class="badge bg-success">ใช้งานได้</span>
                                            @else
                                                <span class="badge bg-danger">ปิดใช้งาน</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $admin->last_login_at ? $admin->last_login_at->format('d/m/Y H:i') : 'ยังไม่เคยล็อกอิน' }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-primary btn-sm" title="แก้ไข">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @if($admin->id !== auth()->id())
                                                <button class="btn btn-outline-warning btn-sm" title="ล็อค">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-person-badge mb-3" style="font-size: 3rem; color: #dddfeb;"></i>
                            <p class="text-muted">ยังไม่มีผู้ดูแลระบบอื่น</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- System Logs & Security -->
        <div class="col-xl-6 col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-shield-exclamation me-2"></i>
                        Security & System Logs
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($securityLogs) && $securityLogs->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($securityLogs as $log)
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            @if(str_contains($log->action, 'failed'))
                                                <i class="bi bi-exclamation-triangle text-danger"></i>
                                            @elseif(str_contains($log->action, 'login'))
                                                <i class="bi bi-box-arrow-in-right text-success"></i>
                                            @elseif(str_contains($log->action, 'logout'))
                                                <i class="bi bi-box-arrow-right text-warning"></i>
                                            @else
                                                <i class="bi bi-info-circle text-info"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1 small">{{ $log->action ?? 'Unknown Action' }}</h6>
                                            <p class="mb-1 text-muted small">
                                                {{ $log->description ?? 'ไม่มีรายละเอียด' }}
                                            </p>
                                            <small class="text-muted">
                                                IP: {{ $log->ip_address ?? 'N/A' }} | 
                                                User Agent: {{ Str::limit($log->user_agent ?? 'N/A', 30) }}
                                            </small>
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $log->created_at ? $log->created_at->diffForHumans() : 'N/A' }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-danger btn-sm">ดู Security Logs ทั้งหมด</a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-shield-check mb-3" style="font-size: 3rem; color: #dddfeb;"></i>
                            <p class="text-muted">ยังไม่มี Security Logs</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Super Admin Tools -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-header bg-danger py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-tools me-2"></i>
                        Super Admin Tools - ใช้ด้วยความระมัดระวัง
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- User Management -->
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('super-admin.users.index') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-people me-2"></i>
                                    จัดการผู้ใช้
                                </a>
                            </div>
                        </div>

                        <!-- Role & Permission Management -->
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-success">
                                    <i class="bi bi-person-badge me-2"></i>
                                    บทบาท & สิทธิ์
                                </a>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-info">
                                    <i class="bi bi-gear me-2"></i>
                                    ตั้งค่าระบบ
                                </a>
                            </div>
                        </div>

                        <!-- Database Management -->
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-warning">
                                    <i class="bi bi-database me-2"></i>
                                    ฐานข้อมูล
                                </a>
                            </div>
                        </div>

                        <!-- Backup & Restore -->
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-secondary">
                                    <i class="bi bi-archive me-2"></i>
                                    สำรองข้อมูล
                                </a>
                            </div>
                        </div>

                        <!-- Emergency Actions -->
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-danger" onclick="return confirm('คุณแน่ใจหรือไม่?')">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Emergency
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr class="mt-4">
                    
                    <!-- Advanced System Tools -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-dark">
                                    <i class="bi bi-terminal me-2"></i>
                                    System Console
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-dark">
                                    <i class="bi bi-bug me-2"></i>
                                    Debug Tools
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-dark">
                                    <i class="bi bi-graph-up me-2"></i>
                                    Analytics
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-dark">
                                    <i class="bi bi-file-earmark-arrow-down me-2"></i>
                                    Export Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // System Performance Chart
    const sysCtx = document.getElementById('systemPerformanceChart');
    if (sysCtx) {
        const systemChart = new Chart(sysCtx, {
            type: 'line',
            data: {
                labels: @json($performanceChartData['labels'] ?? ['วันนี้']),
                datasets: [
                    {
                        label: 'การเข้าใช้งาน',
                        data: @json($performanceChartData['logins'] ?? [0]),
                        borderColor: 'rgba(78, 115, 223, 1)',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'กิจกรรมระบบ',
                        data: @json($performanceChartData['activities'] ?? [0]),
                        borderColor: 'rgba(28, 200, 138, 1)',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'ข้อผิดพลาด',
                        data: @json($performanceChartData['errors'] ?? [0]),
                        borderColor: 'rgba(231, 74, 59, 1)',
                        backgroundColor: 'rgba(231, 74, 59, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.chart-area {
    position: relative;
    height: 300px;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-secondary {
    border-left: 0.25rem solid #858796 !important;
}

.avatar img {
    object-fit: cover;
}

.progress-group .progress {
    border-radius: 10px;
}

.list-group-item:last-child {
    border-bottom: none !important;
}

.btn-outline-dark {
    border-color: #5a5c69;
    color: #5a5c69;
}

.btn-outline-dark:hover {
    background-color: #5a5c69;
    border-color: #5a5c69;
    color: white;
}
</style>
@endpush
