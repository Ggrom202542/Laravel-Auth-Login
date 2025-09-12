@extends('layouts.dashboard')

@section('title', 'รายงานความปลอดภัย')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-shield-check me-2"></i>
                        รายงานความปลอดภัย
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">รายงาน</a></li>
                            <li class="breadcrumb-item active">รายงานความปลอดภัย</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.export', ['type' => 'security', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-success">
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
                    <form method="GET" action="{{ route('admin.reports.security') }}" class="row align-items-end">
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
                            <a href="{{ route('admin.reports.security') }}" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-clockwise me-1"></i>รีเซ็ต
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                เข้าสู่ระบบสำเร็จ
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($securityStats['successful_logins']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle" style="font-size: 2rem; color: #1cc88a;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-left-danger">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                เข้าสู่ระบบล้มเหลว
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($securityStats['failed_logins']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-x-circle" style="font-size: 2rem; color: #e74a3b;"></i>
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
                                {{ number_format($securityStats['suspicious_activities']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem; color: #f6c23e;"></i>
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
                                2FA เปิดใช้งาน
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($securityStats['2fa_enabled']) }}
                            </div>
                            <div class="text-xs text-muted">
                                ({{ number_format($securityStats['2fa_percentage'], 1) }}%)
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-shield-lock" style="font-size: 2rem; color: #36b9cc;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Charts -->
    <div class="row mb-4">
        <!-- Login Attempts Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        การเข้าสู่ระบบรายวัน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="loginAttemptsChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Score -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-shield-check me-2"></i>
                        คะแนนความปลอดภัย
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="securityScoreChart" width="400" height="400"></canvas>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-{{ $securityScore >= 80 ? 'success' : ($securityScore >= 60 ? 'warning' : 'danger') }}">
                            {{ number_format($securityScore, 1) }}%
                        </h3>
                        <p class="text-muted mb-0">
                            @if($securityScore >= 80)
                                <span class="text-success">ดีเยี่ยม</span>
                            @elseif($securityScore >= 60)
                                <span class="text-warning">พอใช้</span>
                            @else
                                <span class="text-danger">ต้องปรับปรุง</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row mb-4">
        <!-- Failed Login Attempts -->
        <div class="col-xl-6 col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="bi bi-x-circle me-2"></i>
                        การเข้าสู่ระบบล้มเหลว ({{ $failedLogins->count() }} รายการ)
                    </h6>
                </div>
                <div class="card-body">
                    @if($failedLogins->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>อีเมล</th>
                                        <th>IP Address</th>
                                        <th>เวลา</th>
                                        <th>จำนวนครั้ง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($failedLogins as $fail)
                                    <tr>
                                        <td>
                                            <small>{{ $fail->user_email ?: 'Unknown' }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $fail->ip_address ?: 'Unknown' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($fail->latest_attempt)->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">{{ $fail->attempts }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted">ไม่พบการเข้าสู่ระบบล้มเหลว</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Suspicious Activities -->
        <div class="col-xl-6 col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        กิจกรรมน่าสงสัย ({{ $suspiciousActivities->count() }} รายการ)
                    </h6>
                </div>
                <div class="card-body">
                    @if($suspiciousActivities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ประเภท</th>
                                        <th>ผู้ใช้</th>
                                        <th>IP Address</th>
                                        <th>เวลา</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suspiciousActivities as $activity)
                                    <tr>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                {{ ucfirst($activity->activity_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $activity->user->name ?? 'Unknown' }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $activity->ip_address ?: 'Unknown' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $activity->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-shield-check text-success mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted">ไม่พบกิจกรรมน่าสงสัย</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- IP Risk Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-globe me-2"></i>
                        การวิเคราะห์ความเสี่ยงของ IP Address
                    </h6>
                </div>
                <div class="card-body">
                    @if($riskIPs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>IP Address</th>
                                        <th>การเข้าสู่ระบบล้มเหลว</th>
                                        <th>กิจกรรมน่าสงสัย</th>
                                        <th>ระดับความเสี่ยง</th>
                                        <th>การกระทำล่าสุด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riskIPs as $ip)
                                    <tr>
                                        <td>
                                            <strong>{{ $ip->ip_address ?: 'Unknown' }}</strong>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge bg-danger">{{ $ip->failed_attempts }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge bg-warning text-dark">{{ $ip->suspicious_count }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            @php
                                                $riskLevel = 'Low';
                                                $riskClass = 'success';
                                                $totalRisk = $ip->failed_attempts + $ip->suspicious_count;
                                                if ($totalRisk >= 10) {
                                                    $riskLevel = 'High';
                                                    $riskClass = 'danger';
                                                } elseif ($totalRisk >= 5) {
                                                    $riskLevel = 'Medium';
                                                    $riskClass = 'warning';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $riskClass }}">{{ $riskLevel }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <small>{{ \Carbon\Carbon::parse($ip->last_activity)->format('d/m/Y H:i') }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-shield-check text-success mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted">ไม่พบ IP Address ที่มีความเสี่ยง</p>
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
    // Login Attempts Chart
    const loginCtx = document.getElementById('loginAttemptsChart');
    if (loginCtx) {
        const loginAttemptsChart = new Chart(loginCtx, {
            type: 'line',
            data: {
                labels: @json($dailyLogins->pluck('date')),
                datasets: [{
                    label: 'เข้าสู่ระบบสำเร็จ',
                    data: @json($dailyLogins->pluck('successful')),
                    borderColor: 'rgba(28, 200, 138, 1)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                }, {
                    label: 'เข้าสู่ระบบล้มเหลว',
                    data: @json($dailyLogins->pluck('failed')),
                    borderColor: 'rgba(231, 74, 59, 1)',
                    backgroundColor: 'rgba(231, 74, 59, 0.1)',
                    borderWidth: 2,
                    fill: false,
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
                        position: 'top'
                    }
                }
            }
        });
    }

    // Security Score Chart
    const scoreCtx = document.getElementById('securityScoreChart');
    if (scoreCtx) {
        const securityScore = {{ $securityScore }};
        const scoreColor = securityScore >= 80 ? '#1cc88a' : (securityScore >= 60 ? '#f6c23e' : '#e74a3b');
        
        const securityScoreChart = new Chart(scoreCtx, {
            type: 'doughnut',
            data: {
                labels: ['คะแนนความปลอดภัย', 'คงเหลือ'],
                datasets: [{
                    data: [securityScore, 100 - securityScore],
                    backgroundColor: [
                        scoreColor,
                        'rgba(221, 223, 235, 0.5)'
                    ],
                    borderColor: [
                        scoreColor,
                        'rgba(221, 223, 235, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
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

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}
</style>
@endpush
