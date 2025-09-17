@extends('layouts.dashboard')

@section('title', 'รายงานประสิทธิภาพ')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">
                <i class="bi bi-speedometer text-primary me-2"></i>
                รายงานประสิทธิภาพ
            </h1>
            <p class="text-muted mb-0">การวิเคราะห์ประสิทธิภาพและการใช้งานระบบ</p>
        </div>
        <div>
            <div class="btn-group" role="group">
                <a href="{{ route('super-admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                </button>
                <button type="button" class="btn btn-success" onclick="exportPerformanceReport()">
                    <i class="bi bi-download me-1"></i> ส่งออก Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-success-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-lightning text-success fs-1 mb-2"></i>
                    <h4 class="text-success fw-bold mb-1">{{ number_format($performanceStats['avg_response_time']) }} ms</h4>
                    <small class="text-muted">เวลาตอบสนองเฉลี่ย</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-primary-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-activity text-primary fs-1 mb-2"></i>
                    <h4 class="text-primary fw-bold mb-1">{{ number_format($performanceStats['total_requests']) }}</h4>
                    <small class="text-muted">คำขอทั้งหมด</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-warning-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-cpu text-warning fs-1 mb-2"></i>
                    <h4 class="text-warning fw-bold mb-1">{{ number_format($performanceStats['cpu_usage'], 1) }}%</h4>
                    <small class="text-muted">การใช้งาน CPU</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm bg-info-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-hdd text-info fs-1 mb-2"></i>
                    <h4 class="text-info fw-bold mb-1">{{ number_format($performanceStats['memory_usage'], 1) }}%</h4>
                    <small class="text-muted">การใช้งาน Memory</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Server Status Cards -->
    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-server text-primary me-2"></i>
                        สถานะเซิร์ฟเวอร์
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Database</span>
                        <span class="badge bg-success">Online</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Redis Cache</span>
                        <span class="badge bg-success">Connected</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">File Storage</span>
                        <span class="badge bg-success">Available</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Mail Service</span>
                        <span class="badge bg-warning">Limited</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up text-success me-2"></i>
                        เวลาตอบสนองรายชั่วโมง
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="responseTimeChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Performance Charts -->
    <div class="row mb-4">
        <!-- Request Distribution Chart -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart text-info me-2"></i>
                        การกระจายตัวของคำขอ
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="requestDistributionChart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Error Rate Chart -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        อัตราข้อผิดพลาด
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="errorRateChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Performance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-database text-primary me-2"></i>
                        ประสิทธิภาพฐานข้อมูล
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Query Type</th>
                                    <th class="border-0">จำนวนครั้ง</th>
                                    <th class="border-0">เวลาเฉลี่ย (ms)</th>
                                    <th class="border-0">เวลาสูงสุด (ms)</th>
                                    <th class="border-0">ประสิทธิภาพ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-search text-success"></i>
                                            </div>
                                            <span class="fw-semibold">SELECT</span>
                                        </div>
                                    </td>
                                    <td><span class="text-primary fw-bold">{{ number_format(15643) }}</span></td>
                                    <td><span class="text-success">{{ number_format(12.5, 1) }}</span></td>
                                    <td><span class="text-warning">{{ number_format(250.8, 1) }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-2">ดี</span>
                                            <div class="progress" style="width: 60px; height: 8px;">
                                                <div class="progress-bar bg-success" style="width: 85%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-plus-circle text-primary"></i>
                                            </div>
                                            <span class="fw-semibold">INSERT</span>
                                        </div>
                                    </td>
                                    <td><span class="text-primary fw-bold">{{ number_format(3421) }}</span></td>
                                    <td><span class="text-success">{{ number_format(8.2, 1) }}</span></td>
                                    <td><span class="text-warning">{{ number_format(156.3, 1) }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-2">ดีมาก</span>
                                            <div class="progress" style="width: 60px; height: 8px;">
                                                <div class="progress-bar bg-success" style="width: 92%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-pencil text-warning"></i>
                                            </div>
                                            <span class="fw-semibold">UPDATE</span>
                                        </div>
                                    </td>
                                    <td><span class="text-primary fw-bold">{{ number_format(1867) }}</span></td>
                                    <td><span class="text-warning">{{ number_format(45.6, 1) }}</span></td>
                                    <td><span class="text-danger">{{ number_format(1250.4, 1) }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-warning me-2">ปานกลาง</span>
                                            <div class="progress" style="width: 60px; height: 8px;">
                                                <div class="progress-bar bg-warning" style="width: 60%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-trash text-danger"></i>
                                            </div>
                                            <span class="fw-semibold">DELETE</span>
                                        </div>
                                    </td>
                                    <td><span class="text-primary fw-bold">{{ number_format(234) }}</span></td>
                                    <td><span class="text-success">{{ number_format(15.3, 1) }}</span></td>
                                    <td><span class="text-warning">{{ number_format(89.7, 1) }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-2">ดี</span>
                                            <div class="progress" style="width: 60px; height: 8px;">
                                                <div class="progress-bar bg-success" style="width: 78%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
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
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }

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
// Response Time Chart
const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
const responseTimeChart = new Chart(responseTimeCtx, {
    type: 'line',
    data: {
        labels: [
            @for($i = 23; $i >= 0; $i--)
                '{{ sprintf("%02d:00", (now()->hour - $i + 24) % 24) }}',
            @endfor
        ],
        datasets: [{
            label: 'เวลาตอบสนอง (ms)',
            data: [
                @for($i = 23; $i >= 0; $i--)
                    {{ rand(50, 200) }},
                @endfor
            ],
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
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
                },
                ticks: {
                    callback: function(value) {
                        return value + ' ms';
                    }
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

// Request Distribution Chart
const requestDistCtx = document.getElementById('requestDistributionChart').getContext('2d');
const requestDistChart = new Chart(requestDistCtx, {
    type: 'doughnut',
    data: {
        labels: ['Dashboard', 'API', 'Authentication', 'Reports', 'User Management'],
        datasets: [{
            data: [35, 25, 20, 12, 8],
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

// Error Rate Chart
const errorRateCtx = document.getElementById('errorRateChart').getContext('2d');
const errorRateChart = new Chart(errorRateCtx, {
    type: 'bar',
    data: {
        labels: [
            @for($i = 6; $i >= 0; $i--)
                '{{ now()->subDays($i)->format('M j') }}',
            @endfor
        ],
        datasets: [{
            label: '4xx Errors',
            data: [
                @for($i = 6; $i >= 0; $i--)
                    {{ rand(5, 25) }},
                @endfor
            ],
            backgroundColor: '#ffc107'
        }, {
            label: '5xx Errors',
            data: [
                @for($i = 6; $i >= 0; $i--)
                    {{ rand(0, 5) }},
                @endfor
            ],
            backgroundColor: '#dc3545'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
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

function refreshData() {
    Swal.fire({
        title: 'รีเฟรชข้อมูล',
        text: 'กำลังโหลดข้อมูลประสิทธิภาพล่าสุด...',
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

function exportPerformanceReport() {
    Swal.fire({
        title: 'ส่งออกรายงาน',
        text: 'กำลังสร้างไฟล์ Excel...',
        icon: 'info',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
        }
    }).then(() => {
        // จำลองการดาวน์โหลดไฟล์
        const link = document.createElement('a');
        link.href = '{{ route("super-admin.reports.export", ["type" => "performance"]) }}';
        link.download = 'performance-report-' + new Date().toISOString().split('T')[0] + '.xlsx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        Swal.fire(
            'สำเร็จ!',
            'รายงานประสิทธิภาพถูกส่งออกเรียบร้อยแล้ว',
            'success'
        );
    });
}

// Real-time updates (optional)
setInterval(() => {
    // อัปเดตข้อมูลแบบ real-time ถ้าต้องการ
    // fetch('/api/performance-stats')
    //     .then(response => response.json())
    //     .then(data => {
    //         // อัปเดต charts และ metrics
    //     });
}, 30000); // อัปเดตทุก 30 วินาที
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection