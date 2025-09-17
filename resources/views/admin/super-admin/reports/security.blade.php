@extends('layouts.dashboard')

@section('title', 'รายงานความปลอดภัย')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">
                <i class="bi bi-shield-lock text-primary me-2"></i>
                รายงานความปลอดภัย
            </h1>
            <p class="text-muted mb-0">การวิเคราะห์และสถิติความปลอดภัยของระบบ</p>
        </div>
        <div>
            <div class="btn-group" role="group">
                <a href="{{ route('super-admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                </button>
                <button type="button" class="btn btn-danger" onclick="generateSecurityReport()">
                    <i class="bi bi-shield-exclamation me-1"></i> สร้างรายงานฉุกเฉิน
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
                            <option value="1" {{ $period == '1' ? 'selected' : '' }}>วันนี้</option>
                            <option value="7" {{ $period == '7' ? 'selected' : '' }}>7 วันที่ผ่านมา</option>
                            <option value="30" {{ $period == '30' ? 'selected' : '' }}>30 วันที่ผ่านมา</option>
                            <option value="90" {{ $period == '90' ? 'selected' : '' }}>90 วันที่ผ่านมา</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-danger-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                    <h4 class="text-danger fw-bold mb-1">{{ number_format($securityStats['failed_attempts']) }}</h4>
                    <small class="text-muted">การล็อกอินล้มเหลว</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-warning fs-1 mb-2"></i>
                    <h4 class="text-warning fw-bold mb-1">{{ number_format($securityStats['suspicious_attempts']) }}</h4>
                    <small class="text-muted">กิจกรรมน่าสงสัย</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-dark-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-ban text-dark fs-1 mb-2"></i>
                    <h4 class="text-dark fw-bold mb-1">{{ number_format($securityStats['blocked_ips']) }}</h4>
                    <small class="text-muted">IP ที่ถูกบล็อก</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check text-primary fs-1 mb-2"></i>
                    <h4 class="text-primary fw-bold mb-1">{{ number_format($securityStats['security_incidents']) }}</h4>
                    <small class="text-muted">Security Incidents</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-info-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-activity text-info fs-1 mb-2"></i>
                    <h4 class="text-info fw-bold mb-1">{{ number_format($securityStats['total_login_attempts']) }}</h4>
                    <small class="text-muted">การล็อกอินทั้งหมด</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Daily Login Attempts Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        การพยายามเข้าสู่ระบบรายวัน
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="loginAttemptsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Security Log Types -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart text-warning me-2"></i>
                        ประเภท Security Logs
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="securityLogChart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspicious IPs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-shield-exclamation text-danger me-2"></i>
                        IP ที่น่าสงสัย ({{ $period }} วันที่ผ่านมา)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">#</th>
                                    <th class="border-0">IP Address</th>
                                    <th class="border-0">การพยายามทั้งหมด</th>
                                    <th class="border-0">การล็อกอินล้มเหลว</th>
                                    <th class="border-0">อัตราล้มเหลว</th>
                                    <th class="border-0">ระดับความเสี่ยง</th>
                                    <th class="border-0">การกระทำ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suspiciousIPs as $index => $ip)
                                @php
                                    $failureRate = $ip->attempts > 0 ? ($ip->failed_attempts / $ip->attempts) * 100 : 0;
                                    $riskLevel = $failureRate >= 80 ? 'danger' : ($failureRate >= 50 ? 'warning' : 'info');
                                    $riskText = $failureRate >= 80 ? 'สูงมาก' : ($failureRate >= 50 ? 'ปานกลาง' : 'ต่ำ');
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-globe text-danger"></i>
                                            </div>
                                            <span class="fw-semibold font-monospace">{{ $ip->ip_address }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-primary fw-bold">{{ number_format($ip->attempts) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-bold">{{ number_format($ip->failed_attempts) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="text-{{ $riskLevel }} fw-bold me-2">{{ number_format($failureRate, 1) }}%</span>
                                            <div class="progress" style="width: 60px; height: 8px;">
                                                <div class="progress-bar bg-{{ $riskLevel }}" style="width: {{ $failureRate }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $riskLevel }}">{{ $riskText }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-info" onclick="viewIPDetails('{{ $ip->ip_address }}')" title="ดูรายละเอียด">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="blockIP('{{ $ip->ip_address }}')" title="บล็อก IP">
                                                <i class="bi bi-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-shield-check fs-1 text-success"></i>
                                        <p class="mt-2">ไม่พบ IP ที่น่าสงสัยในช่วงเวลาที่เลือก</p>
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
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
.bg-dark-subtle { background-color: rgba(33, 37, 41, 0.1) !important; }

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
// Login Attempts Chart
const loginAttemptsCtx = document.getElementById('loginAttemptsChart').getContext('2d');
const loginAttemptsChart = new Chart(loginAttemptsCtx, {
    type: 'line',
    data: {
        labels: [
            @foreach($dailyAttempts as $data)
                '{{ \Carbon\Carbon::parse($data->date)->format('M j') }}',
            @endforeach
        ],
        datasets: [{
            label: 'ทั้งหมด',
            data: [
                @foreach($dailyAttempts as $data)
                    {{ $data->total }},
                @endforeach
            ],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4
        }, {
            label: 'ล้มเหลว',
            data: [
                @foreach($dailyAttempts as $data)
                    {{ $data->failed }},
                @endforeach
            ],
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            tension: 0.4
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

// Security Log Types Chart
const securityLogCtx = document.getElementById('securityLogChart').getContext('2d');
const securityLogChart = new Chart(securityLogCtx, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($securityLogTypes as $log)
                '{{ ucfirst($log->type) }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($securityLogTypes as $log)
                    {{ $log->count }},
                @endforeach
            ],
            backgroundColor: [
                '#dc3545',
                '#ffc107',
                '#0d6efd',
                '#198754',
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

function generateSecurityReport() {
    Swal.fire({
        title: 'สร้างรายงานฉุกเฉิน',
        text: 'ต้องการสร้างรายงานความปลอดภัยฉุกเฉินหรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'สร้างรายงาน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'กำลังสร้างรายงาน!',
                'รายงานความปลอดภัยฉุกเฉินจะถูกส่งไปยังผู้ดูแลระบบ',
                'success'
            );
        }
    });
}

function viewIPDetails(ipAddress) {
    Swal.fire({
        title: 'รายละเอียด IP Address',
        html: `
            <div class="text-start">
                <p><strong>IP Address:</strong> ${ipAddress}</p>
                <p><strong>Location:</strong> ไม่ทราบ (ต้องการ API เพิ่มเติม)</p>
                <p><strong>ISP:</strong> ไม่ทราบ (ต้องการ API เพิ่มเติม)</p>
                <p><strong>ประเภท:</strong> ไม่ทราบ</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'ปิด'
    });
}

function blockIP(ipAddress) {
    Swal.fire({
        title: 'บล็อก IP Address',
        text: `ต้องการบล็อก IP ${ipAddress} หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'บล็อก',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            // ส่งคำขอไปยัง API
            fetch(`{{ route('super-admin.security.block-ip') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    ip_address: ipAddress,
                    reason: 'Suspicious activity detected',
                    type: 'blacklist'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'บล็อกเรียบร้อย!',
                        `IP ${ipAddress} ถูกบล็อกแล้ว`,
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        data.message || 'ไม่สามารถบล็อก IP ได้',
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'เกิดข้อผิดพลาด!',
                    'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                    'error'
                );
            });
        }
    });
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection