@extends('layouts.dashboard')

@section('title', 'สถิติการตรวจสอบการอนุมัติ')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-graph-up me-2"></i>สถิติการตรวจสอบการอนุมัติ
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>กลับไปรายการ
            </a>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm {{ ($days ?? 30) == 7 ? 'active' : '' }}" onclick="changePeriod(7)">7 วัน</button>
                <button type="button" class="btn btn-outline-primary btn-sm {{ ($days ?? 30) == 30 ? 'active' : '' }}" onclick="changePeriod(30)">30 วัน</button>
                <button type="button" class="btn btn-outline-primary btn-sm {{ ($days ?? 30) == 90 ? 'active' : '' }}" onclick="changePeriod(90)">90 วัน</button>
            </div>
        </div>
    </div>

    <!-- Overview Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                รวมบันทึกทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_logs'] ?? 0) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clipboard-data fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                การอนุมัติ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_approvals'] ?? 0) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                การปฏิเสธ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_rejections'] ?? 0) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-x-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                การยกเลิก</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_overrides'] ?? 0) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-repeat fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>แนวโน้มกิจกรรม ({{ $days ?? 30 }} วันที่ผ่านมา)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Distribution Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>การกระจายของการดำเนินการ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="actionChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @if(isset($actionDistribution) && count($actionDistribution) > 0)
                            @foreach($actionDistribution as $action => $count)
                                <span class="mr-2">
                                    <i class="fas fa-circle text-primary"></i> {{ ucfirst(str_replace('_', ' ', $action)) }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Activity and Override Analysis -->
    <div class="row mb-4">
        <!-- User Activity Rankings -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-trophy me-2"></i>อันดับกิจกรรมของผู้ใช้
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($userActivity) && count($userActivity) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>อันดับ</th>
                                        <th>ผู้ใช้</th>
                                        <th>กิจกรรม</th>
                                        <th>การยกเลิก</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userActivity as $index => $user)
                                        <tr>
                                            <td>
                                                @if($index < 3)
                                                    <i class="bi bi-trophy text-warning me-1"></i>
                                                @endif
                                                {{ $index + 1 }}
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $user['name'] }}</strong>
                                                    <br><small class="text-muted">{{ ucfirst($user['role']) }}</small>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-info">{{ $user['total_actions'] }}</span></td>
                                            <td>
                                                @if($user['overrides'] > 0)
                                                    <span class="badge bg-warning">{{ $user['overrides'] }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-person-x fa-2x text-muted mb-2"></i>
                            <p class="text-muted">ไม่มีข้อมูลกิจกรรมของผู้ใช้</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Override Analysis -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-arrow-repeat me-2"></i>การวิเคราะห์การยกเลิก
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($overrideAnalysis))
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 font-weight-bold text-warning">{{ $overrideAnalysis['override_rate'] ?? 0 }}%</div>
                                    <div class="text-xs text-gray-600">อัตราการยกเลิก</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 font-weight-bold text-info">{{ $overrideAnalysis['most_overridden_action'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-600">การดำเนินการที่ถูกยกเลิกมากที่สุด</div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 font-weight-bold text-primary">{{ $overrideAnalysis['top_overrider'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-600">ผู้ยกเลิกมากที่สุด</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 font-weight-bold text-success">{{ $overrideAnalysis['total_overrides'] ?? 0 }}</div>
                                    <div class="text-xs text-gray-600">จำนวนการยกเลิกทั้งหมด</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-info-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted">ไม่มีข้อมูลการวิเคราะห์การยกเลิก</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-bar-chart me-2"></i>สถิติรายละเอียด
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 font-weight-bold text-primary">{{ number_format($stats['unique_users'] ?? 0) }}</div>
                                <div class="text-xs text-gray-600">ผู้ใช้ที่มีกิจกรรม</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 font-weight-bold text-info">{{ number_format($stats['avg_daily_actions'] ?? 0, 1) }}</div>
                                <div class="text-xs text-gray-600">กิจกรรมเฉลี่ยต่อวัน</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 font-weight-bold text-success">{{ $stats['approval_rate'] ?? 0 }}%</div>
                                <div class="text-xs text-gray-600">อัตราการอนุมัติ</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 font-weight-bold text-warning">{{ $stats['peak_activity_hour'] ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-600">ชั่วโมงที่มีกิจกรรมสูงสุด</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.chart-area { position: relative; height: 20rem; width: 100%; }
.chart-pie { position: relative; height: 15rem; width: 100%; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Trend Chart
    @if(isset($trendData) && count($trendData) > 0)
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: @json(array_keys($trendData)),
            datasets: [{
                label: 'กิจกรรมรายวัน',
                data: @json(array_values($trendData)),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'วันที่'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'จำนวนกิจกรรม'
                    },
                    beginAtZero: true
                }
            }
        }
    });
    @endif

    // Action Distribution Chart
    @if(isset($actionDistribution) && count($actionDistribution) > 0)
    const actionCtx = document.getElementById('actionChart').getContext('2d');
    
    @php
        $actionLabels = [];
        foreach(array_keys($actionDistribution) as $action) {
            $actionLabels[] = ucfirst(str_replace('_', ' ', $action));
        }
    @endphp
    
    new Chart(actionCtx, {
        type: 'doughnut',
        data: {
            labels: @json($actionLabels),
            datasets: [{
                data: @json(array_values($actionDistribution)),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#c82333', '#717384'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            cutout: '80%',
        }
    });
    @endif
});

function changePeriod(days) {
    const url = new URL(window.location.href);
    url.searchParams.set('days', days);
    window.location.href = url.toString();
}
</script>
@endpush