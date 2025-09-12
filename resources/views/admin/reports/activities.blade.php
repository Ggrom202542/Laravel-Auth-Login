@extends('layouts.dashboard')

@section('title', 'รายงานกิจกรรม')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-activity me-2"></i>
                        รายงานกิจกรรม
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">รายงาน</a></li>
                            <li class="breadcrumb-item active">รายงานกิจกรรม</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.export', ['type' => 'activities', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-success">
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
                    <form method="GET" action="{{ route('admin.reports.activities') }}" class="row align-items-end">
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
                            <a href="{{ route('admin.reports.activities') }}" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-clockwise me-1"></i>รีเซ็ต
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Statistics -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                กิจกรรมทั้งหมด
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($activityStats['total']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-activity" style="font-size: 2rem; color: #dddfeb;"></i>
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
                                เข้าสู่ระบบ
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($activityStats['logins']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-arrow-in-right" style="font-size: 2rem; color: #dddfeb;"></i>
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
                                สร้างข้อมูล
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($activityStats['creates']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-plus-circle" style="font-size: 2rem; color: #dddfeb;"></i>
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
                                แก้ไขข้อมูล
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($activityStats['updates']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-pencil" style="font-size: 2rem; color: #dddfeb;"></i>
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
                                ลบข้อมูล
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($activityStats['deletes']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-trash" style="font-size: 2rem; color: #dddfeb;"></i>
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
                                น่าสงสัย
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($activityStats['suspicious']) }}
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
        <!-- Daily Activities Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        กิจกรรมรายวัน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="dailyActivitiesChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Types Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>
                        ประเภทกิจกรรม
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="activityTypesChart" width="400" height="400"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($activityTypes->take(5) as $activity)
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

    <!-- Hourly Activity & Top IPs -->
    <div class="row mb-4">
        <!-- Hourly Activity -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock me-2"></i>
                        กิจกรรมตามช่วงเวลา (24 ชั่วโมง)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="hourlyActivityChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top IP Addresses -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-globe me-2"></i>
                        Top IP Addresses
                    </h6>
                </div>
                <div class="card-body">
                    @if($topIPs->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topIPs as $ip)
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $ip->ip_address ?: 'Unknown' }}</h6>
                                        <small class="text-muted">{{ number_format($ip->count) }} กิจกรรม</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary">{{ $ip->count }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-globe mb-3" style="font-size: 3rem; color: #dddfeb;"></i>
                            <p class="text-muted">ไม่พบข้อมูล IP Address</p>
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
    // Daily Activities Chart
    const dailyCtx = document.getElementById('dailyActivitiesChart');
    if (dailyCtx) {
        const dailyActivitiesChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: @json($dailyActivities->pluck('date')),
                datasets: [{
                    label: 'กิจกรรมรายวัน',
                    data: @json($dailyActivities->pluck('count')),
                    borderColor: 'rgba(54, 185, 204, 1)',
                    backgroundColor: 'rgba(54, 185, 204, 0.1)',
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
                        beginAtZero: true
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

    // Activity Types Chart
    const typesCtx = document.getElementById('activityTypesChart');
    if (typesCtx) {
        const activityTypesChart = new Chart(typesCtx, {
            type: 'doughnut',
            data: {
                labels: @json($activityTypes->take(5)->pluck('activity_type')),
                datasets: [{
                    data: @json($activityTypes->take(5)->pluck('count')),
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

    // Hourly Activity Chart
    const hourlyCtx = document.getElementById('hourlyActivityChart');
    if (hourlyCtx) {
        // Prepare hourly data (0-23 hours)
        const hourlyData = Array(24).fill(0);
        @json($hourlyActivity).forEach(item => {
            hourlyData[item.hour] = item.count;
        });

        const hourlyActivityChart = new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: 'กิจกรรมต่อชั่วโมง',
                    data: hourlyData,
                    backgroundColor: 'rgba(246, 194, 62, 0.8)',
                    borderColor: 'rgba(246, 194, 62, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
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

.list-group-item:last-child {
    border-bottom: none !important;
}
</style>
@endpush
