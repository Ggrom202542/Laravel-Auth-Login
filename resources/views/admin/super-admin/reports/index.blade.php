@extends('layouts.dashboard')

@section('title', 'รายงานระบบ')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">
                <i class="bi bi-graph-up text-primary me-2"></i>
                รายงานระบบ
            </h1>
            <p class="text-muted mb-0">ภาพรวมและสถิติการใช้งานระบบ</p>
        </div>
        <div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="refreshDashboard()">
                    <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                </button>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('super-admin.reports.export', ['type' => 'users', 'format' => 'pdf']) }}">
                            <i class="bi bi-file-pdf me-2"></i>PDF Report
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('super-admin.reports.export', ['type' => 'users', 'format' => 'excel']) }}">
                            <i class="bi bi-file-excel me-2"></i>Excel Report
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-primary-subtle">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="mb-0 text-primary fw-bold">{{ number_format($systemStats['total_users']) }}</h3>
                            <p class="text-muted mb-0">ผู้ใช้ทั้งหมด</p>
                        </div>
                        <i class="bi bi-people text-primary fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-success-subtle">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="mb-0 text-success fw-bold">{{ number_format($systemStats['active_sessions']) }}</h3>
                            <p class="text-muted mb-0">Sessions ที่ใช้งาน</p>
                        </div>
                        <i class="bi bi-wifi text-success fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-warning-subtle">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="mb-0 text-warning fw-bold">{{ number_format($systemStats['security_incidents_today']) }}</h3>
                            <p class="text-muted mb-0">Security Incidents วันนี้</p>
                        </div>
                        <i class="bi bi-shield-exclamation text-warning fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-info-subtle">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="mb-0 text-info fw-bold">{{ $systemStats['uptime_percentage'] }}%</h3>
                            <p class="text-muted mb-0">System Uptime</p>
                        </div>
                        <i class="bi bi-server text-info fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <ul class="nav nav-pills" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" href="{{ route('super-admin.reports.index') }}">
                        <i class="bi bi-speedometer2 me-2"></i>ภาพรวม
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="{{ route('super-admin.reports.users') }}">
                        <i class="bi bi-people me-2"></i>ผู้ใช้งาน
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="{{ route('super-admin.reports.sessions') }}">
                        <i class="bi bi-laptop me-2"></i>Sessions
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="{{ route('super-admin.reports.security') }}">
                        <i class="bi bi-shield-lock me-2"></i>ความปลอดภัย
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="{{ route('super-admin.reports.performance') }}">
                        <i class="bi bi-speedometer me-2"></i>Performance
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Monthly Usage Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        การใช้งานรายเดือน
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyUsageChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-speedometer text-success me-2"></i>
                        Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Avg Response Time</span>
                            <span class="fw-bold">{{ $performanceStats['avg_response_time'] }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 75%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Peak Users Today</span>
                            <span class="fw-bold">{{ $performanceStats['peak_users'] }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: 60%"></div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Error Rate</span>
                            <span class="fw-bold text-success">{{ $performanceStats['error_rate'] }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 5%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Overview -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-shield-check text-warning me-2"></i>
                        ความปลอดภัยวันนี้
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-danger mb-1">{{ $securityStats['failed_logins_today'] }}</h4>
                                <small class="text-muted">Failed Logins</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-warning mb-1">{{ $securityStats['blocked_ips'] }}</h4>
                                <small class="text-muted">Blocked IPs</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="text-info mb-1">{{ $securityStats['security_alerts'] }}</h4>
                            <small class="text-muted">Security Alerts</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-activity text-primary me-2"></i>
                        System Health
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted">System Status</span>
                        <span class="badge bg-success px-3 py-2">{{ $systemStats['system_health'] }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted">Database</span>
                        <span class="badge bg-success px-3 py-2">Healthy</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted">Storage</span>
                        <span class="badge bg-success px-3 py-2">75% Available</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1) !important;
}
.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}
.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}
.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1) !important;
}

.nav-pills .nav-link {
    border-radius: 8px;
    margin-right: 0.5rem;
    transition: all 0.3s ease;
}

.nav-pills .nav-link:hover {
    background-color: rgba(13, 110, 253, 0.1);
}

.nav-pills .nav-link.active {
    background-color: #0d6efd;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Usage Chart
const monthlyUsageCtx = document.getElementById('monthlyUsageChart').getContext('2d');
const monthlyUsageChart = new Chart(monthlyUsageCtx, {
    type: 'line',
    data: {
        labels: [
            @foreach($monthlyUsage as $data)
                '{{ $data->month }}',
            @endforeach
        ],
        datasets: [{
            label: 'Sessions',
            data: [
                @foreach($monthlyUsage as $data)
                    {{ $data->sessions }},
                @endforeach
            ],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

function refreshDashboard() {
    Swal.fire({
        title: 'รีเฟรชข้อมูล',
        text: 'กำลังโหลดข้อมูลล่าสุด...',
        icon: 'info',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
        }
    }).then(() => {
        location.reload();
    });
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection