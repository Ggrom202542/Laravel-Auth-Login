@extends('layouts.dashboard')

@section('title', 'รายงานระบบ')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-graph-up me-2"></i>
                    รายงานระบบ
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.users') }}" class="btn btn-outline-primary">
                        <i class="bi bi-people me-1"></i>รายงานผู้ใช้
                    </a>
                    <a href="{{ route('admin.reports.activities') }}" class="btn btn-outline-info">
                        <i class="bi bi-activity me-1"></i>รายงานกิจกรรม
                    </a>
                    <a href="{{ route('admin.reports.security') }}" class="btn btn-outline-warning">
                        <i class="bi bi-shield-check me-1"></i>รายงานความปลอดภัย
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
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
                                {{ number_format($overview['total_users']) }}
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
                                สมาชิกใหม่เดือนนี้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($overview['new_users_this_month']) }}
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
                                ผู้ใช้งานวันนี้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($overview['active_users_today']) }}
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
                                กิจกรรมน่าสงสัย
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($overview['suspicious_activities']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem; color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Registration Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        การลงทะเบียนสมาชิกใหม่ (30 วันที่ผ่านมา)
                    </h6>
                    <div class="dropdown no-arrow">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'users', 'date_from' => now()->subDays(30)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}">
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i>ส่งออก CSV
                            </a></li>
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

        <!-- Activity Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>
                        กิจกรรมยอดนิยม
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="activityChart" width="400" height="400"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($topActivities as $activity)
                        <span class="mr-2 d-block mb-1">
                            <i class="bi bi-circle-fill text-{{ $loop->index == 0 ? 'primary' : ($loop->index == 1 ? 'success' : ($loop->index == 2 ? 'info' : ($loop->index == 3 ? 'warning' : 'secondary'))) }}"></i>
                            {{ ucfirst($activity->activity_type) }} ({{ number_format($activity->count) }})
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Reports -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-speedometer2 me-2"></i>
                        รายงานด่วน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-people text-primary" style="font-size: 2.5rem;"></i>
                                    <h5 class="mt-3">รายงานผู้ใช้</h5>
                                    <p class="text-muted">สถิติและข้อมูลผู้ใช้งาน</p>
                                    <a href="{{ route('admin.reports.users') }}" class="btn btn-primary">
                                        <i class="bi bi-arrow-right me-1"></i>ดูรายงาน
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-activity text-info" style="font-size: 2.5rem;"></i>
                                    <h5 class="mt-3">รายงานกิจกรรม</h5>
                                    <p class="text-muted">การใช้งานและพฤติกรรม</p>
                                    <a href="{{ route('admin.reports.activities') }}" class="btn btn-info">
                                        <i class="bi bi-arrow-right me-1"></i>ดูรายงาน
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="card border-warning h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-shield-check text-warning" style="font-size: 2.5rem;"></i>
                                    <h5 class="mt-3">รายงานความปลอดภัย</h5>
                                    <p class="text-muted">การรักษาความปลอดภัย</p>
                                    <a href="{{ route('admin.reports.security') }}" class="btn btn-warning">
                                        <i class="bi bi-arrow-right me-1"></i>ดูรายงาน
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-download text-success" style="font-size: 2.5rem;"></i>
                                    <h5 class="mt-3">ส่งออกข้อมูล</h5>
                                    <p class="text-muted">ดาวน์โหลดรายงาน CSV</p>
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-download me-1"></i>ส่งออก
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'users']) }}">รายงานผู้ใช้</a></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'activities']) }}">รายงานกิจกรรม</a></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'security']) }}">รายงานความปลอดภัย</a></li>
                                        </ul>
                                    </div>
                                </div>
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
                labels: @json(collect($registrationChart)->pluck('date')),
                datasets: [{
                    label: 'การลงทะเบียนใหม่',
                    data: @json(collect($registrationChart)->pluck('count')),
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

    // Activity Chart
    const actCtx = document.getElementById('activityChart');
    if (actCtx) {
        const activityChart = new Chart(actCtx, {
            type: 'doughnut',
            data: {
                labels: @json($topActivities->pluck('activity_type')),
                datasets: [{
                    data: @json($topActivities->pluck('count')),
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderColor: [
                        'rgba(78, 115, 223, 1)',
                        'rgba(28, 200, 138, 1)',
                        'rgba(54, 185, 204, 1)',
                        'rgba(246, 194, 62, 1)',
                        'rgba(108, 117, 125, 1)'
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

.dropdown-menu {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.card.border-primary:hover,
.card.border-info:hover,
.card.border-warning:hover,
.card.border-success:hover {
    box-shadow: 0 0.5rem 2rem 0 rgba(0, 0, 0, 0.1);
}
</style>
@endpush
