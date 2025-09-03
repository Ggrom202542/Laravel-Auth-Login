@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-person-badge me-2"></i>
                    Admin Dashboard
                </h1>
                <div class="text-end">
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
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">
                                ยินดีต้อนรับ, {{ auth()->user()->name }}!
                            </h4>
                            <p class="card-text mb-0">
                                คุณเข้าใช้งานในฐานะ <strong>ผู้ดูแลระบบ (Admin)</strong> 
                                วันนี้เป็นวันที่ {{ now()->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end d-none d-md-block">
                            <i class="bi bi-person-badge opacity-75" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 1 -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                ผู้ใช้งานทั้งหมด
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                ผู้ใช้งานใหม่วันนี้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['new_users_today'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-plus" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                ผู้ใช้งานออนไลน์
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

        <div class="col-xl-3 col-md-6 mb-4">
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
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- User Registration Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        กราฟการสมัครสมาชิกรายวัน (30 วันที่ผ่านมา)
                    </h6>
                    <div class="dropdown no-arrow">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-list-ul"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">ดูรายละเอียด</a></li>
                            <li><a class="dropdown-item" href="#">ส่งออกข้อมูล</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="registrationChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>
                        การกระจายบทบาท
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="roleChart" width="400" height="400"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @if(isset($roleStats))
                            @foreach($roleStats as $role => $count)
                            <span class="mr-2">
                                <i class="bi bi-circle-fill text-{{ $loop->index == 0 ? 'primary' : ($loop->index == 1 ? 'success' : 'info') }}"></i> 
                                {{ ucfirst($role) }} ({{ $count }})
                            </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Management & Activities Row -->
    <div class="row">
        <!-- Recent Users -->
        <div class="col-xl-6 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-people me-2"></i>
                        ผู้ใช้งานล่าสุด
                    </h6>
                    <a href="#" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus me-1"></i>
                        จัดการผู้ใช้
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentUsers) && $recentUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ชื่อ</th>
                                        <th>อีเมล</th>
                                        <th>บทบาท</th>
                                        <th>สมัครเมื่อ</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentUsers as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <img src="{{ $user->profile_photo ? asset('storage/'.$user->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                                         alt="{{ $user->name }}" 
                                                         class="rounded-circle" 
                                                         style="width: 32px; height: 32px;">
                                                </div>
                                                <strong>{{ $user->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $user->role === 'admin' ? 'success' : ($user->role === 'super_admin' ? 'danger' : 'primary') }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if($user->status === 'active')
                                                <span class="badge bg-success">ใช้งานได้</span>
                                            @else
                                                <span class="badge bg-danger">ปิดใช้งาน</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-people mb-3" style="font-size: 3rem; color: #dddfeb;"></i>
                            <p class="text-muted">ยังไม่มีผู้ใช้งานใหม่</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- System Activities -->
        <div class="col-xl-6 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-list-check me-2"></i>
                        กิจกรรมระบบล่าสุด
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($systemActivities) && $systemActivities->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($systemActivities as $activity)
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            @if($activity->action === 'login')
                                                <i class="bi bi-box-arrow-in-right text-success"></i>
                                            @elseif($activity->action === 'logout')
                                                <i class="bi bi-box-arrow-right text-warning"></i>
                                            @elseif($activity->action === 'register')
                                                <i class="bi bi-person-plus text-primary"></i>
                                            @else
                                                <i class="bi bi-circle-fill text-info"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $activity->user ? $activity->user->name : 'ระบบ' }}</h6>
                                            <p class="mb-1 text-muted small">{{ $activity->description ?? $activity->action }}</p>
                                            <small class="text-muted">
                                                IP: {{ $activity->ip_address ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-primary btn-sm">ดูกิจกรรมทั้งหมด</a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox mb-3" style="font-size: 3rem; color: #dddfeb;"></i>
                            <p class="text-muted">ยังไม่มีกิจกรรมระบบ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-tools me-2"></i>
                        การจัดการด่วน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-primary">
                                    <i class="bi bi-people me-2"></i>
                                    จัดการผู้ใช้
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-success">
                                    <i class="bi bi-person-badge me-2"></i>
                                    จัดการบทบาท
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-info">
                                    <i class="bi bi-key me-2"></i>
                                    จัดการสิทธิ์
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-warning">
                                    <i class="bi bi-clock-history me-2"></i>
                                    ตรวจสอบกิจกรรม
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-danger">
                                    <i class="bi bi-person-lock me-2"></i>
                                    ผู้ใช้ถูกล็อค
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-secondary">
                                    <i class="bi bi-gear me-2"></i>
                                    ตั้งค่าระบบ
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
    // Registration Chart
    const regCtx = document.getElementById('registrationChart');
    if (regCtx) {
        const registrationChart = new Chart(regCtx, {
            type: 'line',
            data: {
                labels: @json($registrationChartData['labels'] ?? ['วันนี้']),
                datasets: [{
                    label: 'การสมัครใหม่',
                    data: @json($registrationChartData['data'] ?? [0]),
                    borderColor: 'rgba(28, 200, 138, 1)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
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
                        display: false
                    }
                }
            }
        });
    }

    // Role Distribution Chart
    const roleCtx = document.getElementById('roleChart');
    if (roleCtx) {
        const roleChart = new Chart(roleCtx, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($roleStats ?? [])),
                datasets: [{
                    data: @json(array_values($roleStats ?? [])),
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(28, 200, 138, 0.8)', 
                        'rgba(246, 194, 62, 0.8)'
                    ],
                    borderColor: [
                        'rgba(78, 115, 223, 1)',
                        'rgba(28, 200, 138, 1)',
                        'rgba(246, 194, 62, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
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
.chart-area, .chart-pie {
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

.avatar img {
    object-fit: cover;
}

.list-group-item:last-child {
    border-bottom: none !important;
}
</style>
@endpush
