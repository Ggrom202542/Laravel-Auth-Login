@extends('layouts.dashboard')

@section('title', 'สถิติการอนุมัติ')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-graph-up me-2"></i>สถิติการอนุมัติ
        </h1>
        <div class="d-flex gap-2">
            @if($isSuperAdmin)
                <a href="{{ route('admin.statistics.report') }}" class="btn btn-info btn-sm">
                    <i class="bi bi-file-earmark-text me-1"></i>รายงานรายละเอียด
                </a>
            @endif
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm active" data-period="30">30 วัน</button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-period="60">60 วัน</button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-period="90">90 วัน</button>
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
                                การอนุมัติทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($overviewStats['total_approvals']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clipboard-check fa-2x text-gray-300"></i>
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
                                อัตราการอนุมัติ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overviewStats['approval_rate'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                เวลาประมวลผลเฉลี่ย</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overviewStats['avg_processing_time_hours'] }} ชม.</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ $overviewStats['override_rate'] > 5 ? 'warning' : 'secondary' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ $overviewStats['override_rate'] > 5 ? 'warning' : 'secondary' }} text-uppercase mb-1">
                                อัตราการแทนที่</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overviewStats['override_rate'] }}%</div>
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
        <!-- Approval Trends Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>แนวโน้มการอนุมัติ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="approvalTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Distribution Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>การกระจายเวลาประมวลผล
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="timeDistributionChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> 0-1 ชม.
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> 1-4 ชม.
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> 4-12 ชม.
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> 12+ ชม.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-speedometer2 me-2"></i>ตัวชี้วัดประสิทธิภาพ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-primary">{{ $performanceMetrics['daily_throughput'] }}</div>
                                <div class="text-xs text-gray-600">การอนุมัติต่อวัน</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-info">{{ $performanceMetrics['quality_score'] }}%</div>
                                <div class="text-xs text-gray-600">คะแนนคุณภาพ</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-warning">{{ $performanceMetrics['escalation_rate'] }}%</div>
                                <div class="text-xs text-gray-600">อัตราการขึ้นบันได</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-success">{{ $performanceMetrics['avg_processing_time_hours'] }} ชม.</div>
                                <div class="text-xs text-gray-600">เวลาประมวลผลเฉลี่ย</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Performance Rankings -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-trophy me-2"></i>อันดับประสิทธิภาพผู้ใช้
                    </h6>
                </div>
                <div class="card-body">
                    @if(count($userPerformance) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ผู้ใช้</th>
                                        <th>รีวิว</th>
                                        <th>อัตราอนุมัติ</th>
                                        <th>เวลาเฉลี่ย</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($userPerformance, 0, 10) as $index => $user)
                                        <tr>
                                            <td>
                                                @if($index < 3)
                                                    <i class="bi bi-trophy text-warning me-1"></i>
                                                @endif
                                                <small>
                                                    {{ $user['first_name'] }} {{ $user['last_name'] }}
                                                    <br><span class="text-muted">{{ ucfirst($user['role']) }}</span>
                                                </small>
                                            </td>
                                            <td><small>{{ $user['total_reviewed'] }}</small></td>
                                            <td><small>{{ $user['approval_rate'] }}%</small></td>
                                            <td><small>{{ round($user['avg_processing_hours'], 1) }} ชม.</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-person-x fa-2x text-muted mb-2"></i>
                            <p class="text-muted">ไม่มีข้อมูลประสิทธิภาพผู้ใช้</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Flow Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-diagram-3 me-2"></i>การวิเคราะห์กระบวนการอนุมัติ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-info">การกระจายสถานะ</h6>
                            @if(isset($flowAnalysis['status_distribution']))
                                @foreach($flowAnalysis['status_distribution'] as $status => $count)
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-xs">
                                                @if($status === 'approved') อนุมัติ
                                                @elseif($status === 'rejected') ปฏิเสธ
                                                @elseif($status === 'pending') รอดำเนินการ
                                                @else {{ ucfirst($status) }}
                                                @endif
                                            </span>
                                            <span class="text-xs">{{ $count }}</span>
                                        </div>
                                        @php
                                            $total = array_sum($flowAnalysis['status_distribution']);
                                            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                        @endphp
                                        <div class="progress progress-sm">
                                            <div class="progress-bar 
                                                @if($status === 'approved') bg-success 
                                                @elseif($status === 'rejected') bg-danger 
                                                @else bg-warning @endif" 
                                                role="progressbar" style="width: {{ $percentage }}%">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="col-md-4">
                            <h6 class="text-info">เวลาประมวลผลตามสถานะ</h6>
                            @if(isset($flowAnalysis['avg_time_by_status']))
                                @foreach($flowAnalysis['avg_time_by_status'] as $status => $hours)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-xs">
                                            @if($status === 'approved') อนุมัติ
                                            @elseif($status === 'rejected') ปฏิเสธ
                                            @elseif($status === 'pending') รอดำเนินการ
                                            @else {{ ucfirst($status) }}
                                            @endif
                                        </span>
                                        <span class="text-xs font-weight-bold">{{ round($hours, 1) }} ชม.</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="col-md-4">
                            <h6 class="text-info">สถิติการกระจายเวลา</h6>
                            @if(isset($timeAnalysis['statistics']))
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-xs">เวลาเฉลี่ย</span>
                                    <span class="text-xs font-weight-bold">{{ $timeAnalysis['statistics']['avg_hours'] }} ชม.</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-xs">เวลามัธยฐาน</span>
                                    <span class="text-xs font-weight-bold">{{ $timeAnalysis['statistics']['median_hours'] }} ชม.</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-xs">เวลาสูงสุด</span>
                                    <span class="text-xs font-weight-bold">{{ $timeAnalysis['statistics']['max_hours'] }} ชม.</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-xs">จำนวนที่ประมวลผล</span>
                                    <span class="text-xs font-weight-bold">{{ number_format($timeAnalysis['statistics']['total_processed']) }}</span>
                                </div>
                            @endif
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
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-secondary { border-left: 0.25rem solid #858796 !important; }
.chart-area { position: relative; height: 10rem; width: 100%; }
.chart-pie { position: relative; height: 15rem; width: 100%; }
.progress-sm { height: 0.5rem; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Approval Trends Chart
    const trendsCtx = document.getElementById('approvalTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: @json($approvalTrends['labels']),
            datasets: @json($approvalTrends['datasets'])
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
                        text: 'จำนวน'
                    }
                }
            }
        }
    });

    // Time Distribution Chart
    @if(isset($timeAnalysis['time_buckets']))
    const timeCtx = document.getElementById('timeDistributionChart').getContext('2d');
    new Chart(timeCtx, {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($timeAnalysis['time_buckets'])),
            datasets: [{
                data: @json(array_values($timeAnalysis['time_buckets'])),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#c82333', '#717384', '#484a56'],
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

    // Period filter buttons
    $('.btn-group button').on('click', function() {
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
        
        const days = $(this).data('period');
        const url = new URL(window.location.href);
        url.searchParams.set('days', days);
        window.location.href = url.toString();
    });

    // Set active period button based on current URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentDays = urlParams.get('days') || '{{ $days }}';
    $('.btn-group button').removeClass('active');
    $('.btn-group button[data-period="' + currentDays + '"]').addClass('active');
});
</script>
@endpush