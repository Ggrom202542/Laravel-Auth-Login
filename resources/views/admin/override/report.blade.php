@extends('layouts.dashboard')

@section('title', 'รายงานการแทนที่คำสั่งรายละเอียด')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-clipboard-data me-2 text-info"></i>รายงานการแทนที่คำสั่งรายละเอียด
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.override.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>กลับ
            </a>
            <a href="{{ route('admin.override.export') }}" class="btn btn-success btn-sm">
                <i class="bi bi-download me-1"></i>ส่งออกรายงาน
            </a>
        </div>
    </div>

    <!-- Time Period Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.override.report') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="days" class="form-label">ช่วงเวลาการวิเคราะห์</label>
                    <select class="form-select" id="days" name="days" onchange="this.form.submit()">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 วันที่ผ่านมา</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 วันที่ผ่านมา</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 วันที่ผ่านมา</option>
                        <option value="365" {{ $days == 365 ? 'selected' : '' }}>1 ปีที่ผ่านมา</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        รายงานนี้แสดงข้อมูลการวิเคราะห์การแทนที่คำสั่งอนุมัติในระบบ
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                การแทนที่ทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($overrideAnalysis['total_overrides'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-repeat fa-2x text-gray-300"></i>
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
                                อัตราการแทนที่</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($ratioAnalysis['override_rate'] ?? 0, 1) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-percent fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                ผู้ใช้ที่ทำการแทนที่</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ count($userPatterns ?? []) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fa-2x text-gray-300"></i>
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
                                การอนุมัติปกติ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($ratioAnalysis['total_approvals'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Override Trends Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>แนวโน้มการแทนที่คำสั่ง
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="overrideTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reason Analysis -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>เหตุผลการแทนที่
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="reasonAnalysisChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Patterns Analysis -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-person-lines-fill me-2"></i>รูปแบบการแทนที่ของผู้ใช้
            </h6>
        </div>
        <div class="card-body">
            @if(!empty($userPatterns) && count($userPatterns) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>ผู้ใช้</th>
                                <th>บทบาท</th>
                                <th>จำนวนการแทนที่</th>
                                <th>เหตุผลหลัก</th>
                                <th>อัตราการแทนที่</th>
                                <th>การแทนที่ล่าสุด</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userPatterns as $pattern)
                                <tr>
                                    <td>
                                        <strong>{{ $pattern['name'] ?? 'ไม่ทราบ' }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($pattern['role'] ?? 'unknown') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ number_format($pattern['override_count'] ?? 0) }}
                                        </span>
                                    </td>
                                    <td>{{ $pattern['common_reason'] ?? 'ไม่ระบุ' }}</td>
                                    <td>
                                        @php
                                            $rate = $pattern['override_rate'] ?? 0;
                                            $badgeClass = $rate > 20 ? 'bg-danger' : ($rate > 10 ? 'bg-warning' : 'bg-success');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ number_format($rate, 1) }}%
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $pattern['last_override'] ?? 'ไม่มี' }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-shield-check fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">ไม่พบข้อมูลการแทนที่</h5>
                    <p class="text-muted">ไม่มีการแทนที่คำสั่งในช่วงเวลาที่เลือก</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Monthly Comparison -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-calendar3 me-2"></i>เปรียบเทียบรายเดือน
            </h6>
        </div>
        <div class="card-body">
            @if(!empty($monthlyComparison) && count($monthlyComparison) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>เดือน/ปี</th>
                                <th>การอนุมัติทั้งหมด</th>
                                <th>การแทนที่</th>
                                <th>อัตราการแทนที่</th>
                                <th>แนวโน้ม</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyComparison as $month)
                                <tr>
                                    <td>
                                        <strong>{{ $month['month'] ?? 'ไม่ทราบ' }}</strong>
                                    </td>
                                    <td>{{ number_format($month['total_approvals'] ?? 0) }}</td>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ number_format($month['overrides'] ?? 0) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $rate = $month['override_rate'] ?? 0;
                                            $badgeClass = $rate > 15 ? 'bg-danger' : ($rate > 8 ? 'bg-warning' : 'bg-success');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ number_format($rate, 1) }}%
                                        </span>
                                    </td>
                                    <td>
                                        @if(($month['trend'] ?? 0) > 0)
                                            <i class="bi bi-arrow-up text-danger"></i>
                                            <small class="text-danger">เพิ่มขึ้น</small>
                                        @elseif(($month['trend'] ?? 0) < 0)
                                            <i class="bi bi-arrow-down text-success"></i>
                                            <small class="text-success">ลดลง</small>
                                        @else
                                            <i class="bi bi-arrow-right text-muted"></i>
                                            <small class="text-muted">คงที่</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">ไม่มีข้อมูลเปรียบเทียบ</h5>
                    <p class="text-muted">ไม่สามารถแสดงข้อมูลเปรียบเทียบรายเดือนได้</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recommendations -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="bi bi-lightbulb me-2"></i>คำแนะนำและข้อเสนอแนะ
            </h6>
        </div>
        <div class="card-body">
            @php
                $overrideRate = $ratioAnalysis['override_rate'] ?? 0;
                $totalOverrides = $overrideAnalysis['total_overrides'] ?? 0;
            @endphp
            
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">การวิเคราะห์ปัจจุบัน:</h6>
                    <ul class="list-unstyled">
                        @if($overrideRate > 15)
                            <li class="text-danger mb-2">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                อัตราการแทนที่สูง ({{ number_format($overrideRate, 1) }}%) - ควรตรวจสอบกระบวนการอนุมัติ
                            </li>
                        @elseif($overrideRate > 8)
                            <li class="text-warning mb-2">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                อัตราการแทนที่ปานกลาง ({{ number_format($overrideRate, 1) }}%) - ควรติดตามอย่างใกล้ชิด
                            </li>
                        @else
                            <li class="text-success mb-2">
                                <i class="bi bi-check-circle me-2"></i>
                                อัตราการแทนที่อยู่ในเกณฑ์ปกติ ({{ number_format($overrideRate, 1) }}%)
                            </li>
                        @endif

                        @if($totalOverrides > 50)
                            <li class="text-warning mb-2">
                                <i class="bi bi-info-circle me-2"></i>
                                จำนวนการแทนที่ค่อนข้างสูง - ควรวิเคราะห์สาเหตุ
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">ข้อเสนอแนะ:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-arrow-right me-2"></i>
                            ทบทวนเกณฑ์การอนุมัติเป็นประจำ
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right me-2"></i>
                            จัดอบรมผู้ตรวจสอบเพื่อลดการแทนที่
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right me-2"></i>
                            ติดตามรูปแบบการแทนที่อย่างสม่ำเสมอ
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right me-2"></i>
                            สร้างคู่มือการตัดสินใจที่ชัดเจน
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.chart-area {
    position: relative;
    height: 20rem;
    width: 100%;
}
.chart-pie {
    position: relative;
    height: 15rem;
    width: 100%;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Override Trends Chart
    @if(!empty($overrideTrends))
        const trendsCtx = document.getElementById('overrideTrendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($overrideTrends['labels'] ?? [])) !!},
                datasets: [{
                    label: 'การแทนที่คำสั่ง',
                    data: {!! json_encode(array_values($overrideTrends['values'] ?? [])) !!},
                    borderColor: '#f6c23e',
                    backgroundColor: 'rgba(246, 194, 62, 0.1)',
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
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    @endif

    // Reason Analysis Chart
    @if(!empty($reasonAnalysis))
        const reasonCtx = document.getElementById('reasonAnalysisChart').getContext('2d');
        new Chart(reasonCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($reasonAnalysis ?? [])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($reasonAnalysis ?? [])) !!},
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a', 
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    @endif
});
</script>
@endpush