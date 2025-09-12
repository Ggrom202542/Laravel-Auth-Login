@extends('layouts.dashboard')

@section('title', 'รายงานผู้ใช้งาน')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-people me-2"></i>
                        รายงานผู้ใช้งาน
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">รายงาน</a></li>
                            <li class="breadcrumb-item active">รายงานผู้ใช้งาน</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.export', ['type' => 'users', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-success">
                        <i class="bi bi-download me-1"></i>ส่งออก CSV
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>กลับ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.users') }}" class="row align-items-end">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">วันที่เริ่มต้น</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">วันที่สิ้นสุด</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>กรองข้อมูล
                            </button>
                            <a href="{{ route('admin.reports.users') }}" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-clockwise me-1"></i>รีเซ็ต
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                ผู้ใช้ทั้งหมด
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($userStats['total']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                ใช้งานได้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($userStats['active']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                รอการอนุมัติ
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($userStats['pending']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                เปิด 2FA
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($userStats['with_2fa']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-shield-check" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-danger">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                ไม่ใช้งาน
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($userStats['inactive']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-x" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-secondary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                ใหม่ช่วงนี้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($userStats['new_this_period']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-plus" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Daily Registrations Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        การลงทะเบียนรายวัน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="dailyRegistrationsChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>
                        การกระจายตามสถานะ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusChart" width="400" height="400"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($statusDistribution as $status => $count)
                        <span class="mr-2 d-block mb-1">
                            <i class="bi bi-circle-fill text-{{ $status === 'active' ? 'success' : ($status === 'pending' ? 'warning' : 'danger') }}"></i>
                            {{ ucfirst($status) }} ({{ number_format($count) }})
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-star me-2"></i>
                        ผู้ใช้งานที่ใช้งานบ่อยที่สุด (Top 10)
                    </h6>
                </div>
                <div class="card-body">
                    @if($activeUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>ชื่อ</th>
                                        <th>อีเมล</th>
                                        <th>สถานะ</th>
                                        <th>จำนวนกิจกรรม</th>
                                        <th>สมัครเมื่อ</th>
                                        <th>เข้าใช้ล่าสุด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeUsers as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
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
                                            @if($user->status === 'active')
                                                <span class="badge bg-success">ใช้งานได้</span>
                                            @elseif($user->status === 'pending')
                                                <span class="badge bg-warning">รอการอนุมัติ</span>
                                            @else
                                                <span class="badge bg-danger">ปิดใช้งาน</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ number_format($user->activity_logs_count) }}</span>
                                        </td>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if($user->last_login_at)
                                                {{ $user->last_login_at->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-muted">ยังไม่เคยเข้าใช้</span>
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
                            <p class="text-muted">ไม่พบข้อมูลผู้ใช้งาน</p>
                        </div>
                    @endif
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
    // Daily Registrations Chart
    const regCtx = document.getElementById('dailyRegistrationsChart');
    if (regCtx) {
        const dailyRegistrationsChart = new Chart(regCtx, {
            type: 'line',
            data: {
                labels: @json($dailyRegistrations->pluck('date')),
                datasets: [{
                    label: 'การลงทะเบียน',
                    data: @json($dailyRegistrations->pluck('count')),
                    borderColor: 'rgba(78, 115, 223, 1)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
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

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: @json($statusDistribution->keys()),
                datasets: [{
                    data: @json($statusDistribution->values()),
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(231, 74, 59, 0.8)'
                    ],
                    borderColor: [
                        'rgba(28, 200, 138, 1)',
                        'rgba(246, 194, 62, 1)',
                        'rgba(231, 74, 59, 1)'
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
    border-left: 0.25rem solid #6c757d !important;
}

.avatar img {
    object-fit: cover;
}
</style>
@endpush
