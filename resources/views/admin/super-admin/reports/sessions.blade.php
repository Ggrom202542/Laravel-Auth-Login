@extends('layouts.dashboard')

@section('title', 'รายงาน Sessions')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">
                <i class="bi bi-laptop text-primary me-2"></i>
                รายงาน Sessions
            </h1>
            <p class="text-muted mb-0">สถิติและข้อมูลการใช้งาน Sessions ในระบบ</p>
        </div>
        <div>
            <div class="btn-group" role="group">
                <a href="{{ route('super-admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                </button>
            </div>
        </div>
    </div>

    <!-- Period Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" class="d-flex align-items-center gap-3">
                        <label class="form-label mb-0 fw-semibold">ช่วงเวลา:</label>
                        <select name="period" class="form-select" style="width: 200px;" onchange="this.form.submit()">
                            <option value="7" {{ $period == '7' ? 'selected' : '' }}>7 วันที่ผ่านมา</option>
                            <option value="30" {{ $period == '30' ? 'selected' : '' }}>30 วันที่ผ่านมา</option>
                            <option value="90" {{ $period == '90' ? 'selected' : '' }}>90 วันที่ผ่านมา</option>
                            <option value="365" {{ $period == '365' ? 'selected' : '' }}>1 ปีที่ผ่านมา</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-laptop text-primary fs-1 mb-2"></i>
                    <h4 class="text-primary fw-bold mb-1">{{ number_format($sessionStats['total_sessions']) }}</h4>
                    <small class="text-muted">Sessions ทั้งหมด</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-success-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-wifi text-success fs-1 mb-2"></i>
                    <h4 class="text-success fw-bold mb-1">{{ number_format($sessionStats['active_sessions']) }}</h4>
                    <small class="text-muted">Sessions ที่ใช้งาน</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-info-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-plus-circle text-info fs-1 mb-2"></i>
                    <h4 class="text-info fw-bold mb-1">{{ number_format($sessionStats['new_sessions']) }}</h4>
                    <small class="text-muted">Sessions ใหม่</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-people text-warning fs-1 mb-2"></i>
                    <h4 class="text-warning fw-bold mb-1">{{ number_format($sessionStats['unique_users']) }}</h4>
                    <small class="text-muted">ผู้ใช้ที่แตกต่าง</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-secondary-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-clock text-secondary fs-1 mb-2"></i>
                    <h4 class="text-secondary fw-bold mb-1">{{ $sessionStats['average_duration'] }}</h4>
                    <small class="text-muted">ระยะเวลาเฉลี่ย</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Daily Sessions Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        Sessions รายวัน
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="dailySessionsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Device Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart text-success me-2"></i>
                        การแจกแจงตามอุปกรณ์
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="deviceChart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Distribution Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-display text-info me-2"></i>
                        การแจกแจงตามแพลตฟอร์ม ({{ $period }} วันที่ผ่านมา)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">#</th>
                                    <th class="border-0">แพลตฟอร์ม</th>
                                    <th class="border-0">จำนวน Sessions</th>
                                    <th class="border-0">เปอร์เซ็นต์</th>
                                    <th class="border-0">กราฟ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalSessions = $platformDistribution->sum('count');
                                @endphp
                                @forelse($platformDistribution as $index => $platform)
                                @php
                                    $percentage = $totalSessions > 0 ? ($platform->count / $totalSessions) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi 
                                                    @if(stripos($platform->platform, 'windows') !== false) bi-windows
                                                    @elseif(stripos($platform->platform, 'mac') !== false) bi-apple
                                                    @elseif(stripos($platform->platform, 'linux') !== false) bi-ubuntu
                                                    @elseif(stripos($platform->platform, 'android') !== false) bi-android
                                                    @elseif(stripos($platform->platform, 'ios') !== false) bi-phone
                                                    @else bi-display
                                                    @endif text-primary"></i>
                                            </div>
                                            <span class="fw-semibold">{{ $platform->platform ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ number_format($platform->count) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($percentage, 1) }}%</span>
                                    </td>
                                    <td style="width: 200px;">
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mt-2">ไม่มีข้อมูลในช่วงเวลาที่เลือก</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1) !important; }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1) !important; }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1) !important; }

.avatar-sm {
    width: 40px;
    height: 40px;
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
// Daily Sessions Chart
const dailySessionsCtx = document.getElementById('dailySessionsChart').getContext('2d');
const dailySessionsChart = new Chart(dailySessionsCtx, {
    type: 'bar',
    data: {
        labels: [
            @foreach($dailySessions as $data)
                '{{ \Carbon\Carbon::parse($data->date)->format('M j') }}',
            @endforeach
        ],
        datasets: [{
            label: 'Sessions รายวัน',
            data: [
                @foreach($dailySessions as $data)
                    {{ $data->count }},
                @endforeach
            ],
            backgroundColor: 'rgba(13, 110, 253, 0.8)',
            borderColor: '#0d6efd',
            borderWidth: 1
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

// Device Distribution Chart
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
const deviceChart = new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($deviceDistribution as $device)
                '{{ ucfirst($device->device_type ?? "Unknown") }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($deviceDistribution as $device)
                    {{ $device->count }},
                @endforeach
            ],
            backgroundColor: [
                '#0d6efd',
                '#198754', 
                '#ffc107',
                '#dc3545',
                '#6f42c1'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function refreshData() {
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