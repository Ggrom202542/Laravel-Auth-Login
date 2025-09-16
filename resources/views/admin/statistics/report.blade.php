@extends('layouts.dashboard')

@section('title', 'รายงานสถิติโดยละเอียด')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-graph-up me-2"></i>รายงานสถิติโดยละเอียด
                    </h1>
                    <p class="text-muted">รายงานการวิเคราะห์ข้อมูลเชิงลึกสำหรับ {{ $days }} วันที่ผ่านมา</p>
                </div>
                <div>
                    <a href="{{ route('admin.statistics.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>กลับไปหน้าสถิติ
                    </a>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>พิมพ์รายงาน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Executive Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-primary">
                <div class="card-header bg-primary">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-data me-2"></i>สรุปผู้บริหาร
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title">คะแนนประสิทธิภาพรวม</h6>
                                    <h2 class="text-primary">{{ $reportData['executive_summary']['key_metrics']['efficiency_score'] ?? 0 }}%</h2>
                                    <small class="text-muted">จากคะแนนเต็ม 100</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title">คะแนนคุณภาพ</h6>
                                    <h2 class="text-success">{{ $reportData['executive_summary']['key_metrics']['quality_score'] ?? 0 }}%</h2>
                                    <small class="text-muted">ระดับคุณภาพการทำงาน</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title">ระดับปัญหาคอขวด</h6>
                                    @php
                                        $severity = $reportData['executive_summary']['key_metrics']['bottleneck_severity'] ?? 'Minimal';
                                        $severityClass = match($severity) {
                                            'High' => 'text-danger',
                                            'Medium' => 'text-warning',
                                            'Low' => 'text-info',
                                            default => 'text-success'
                                        };
                                    @endphp
                                    <h2 class="{{ $severityClass }}">{{ $severity }}</h2>
                                    <small class="text-muted">ความรุนแรงของปัญหา</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Efficiency Report -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>รายงานประสิทธิภาพการอนุมัติ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ยื่นขอทั้งหมด:</strong></td>
                                    <td>{{ number_format($reportData['approval_efficiency']['total_submitted'] ?? 0) }} รายการ</td>
                                </tr>
                                <tr>
                                    <td><strong>ประมวลผลแล้ว:</strong></td>
                                    <td>{{ number_format($reportData['approval_efficiency']['total_processed'] ?? 0) }} รายการ</td>
                                </tr>
                                <tr>
                                    <td><strong>อัตราการประมวลผล:</strong></td>
                                    <td>{{ $reportData['approval_efficiency']['processing_rate'] ?? 0 }}%</td>
                                </tr>
                                <tr>
                                    <td><strong>เวลาประมวลผลเฉลี่ย:</strong></td>
                                    <td>{{ $reportData['approval_efficiency']['avg_processing_time_hours'] ?? 0 }} ชั่วโมง</td>
                                </tr>
                                <tr>
                                    <td><strong>ประเภทความเร็ว:</strong></td>
                                    <td>
                                        @php
                                            $category = $reportData['approval_efficiency']['processing_speed_category'] ?? 'Unknown';
                                            $categoryClass = match($category) {
                                                'Excellent' => 'badge bg-success',
                                                'Good' => 'badge bg-primary',
                                                'Average' => 'badge bg-warning',
                                                default => 'badge bg-danger'
                                            };
                                        @endphp
                                        <span class="{{ $categoryClass }}">{{ $category }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ปริมาณงานต่อวัน:</strong></td>
                                    <td>{{ $reportData['approval_efficiency']['daily_throughput'] ?? 0 }} รายการ</td>
                                </tr>
                                <tr>
                                    <td><strong>ความสามารถต่อสัปดาห์:</strong></td>
                                    <td>{{ $reportData['approval_efficiency']['weekly_capacity'] ?? 0 }} รายการ</td>
                                </tr>
                                <tr>
                                    <td><strong>งานค้างปัจจุบัน:</strong></td>
                                    <td>{{ number_format($reportData['approval_efficiency']['current_backlog'] ?? 0) }} รายการ</td>
                                </tr>
                                <tr>
                                    <td><strong>อัตราเติบโตงานค้าง:</strong></td>
                                    <td>{{ $reportData['approval_efficiency']['backlog_growth_rate'] ?? 0 }}%</td>
                                </tr>
                                <tr>
                                    <td><strong>คะแนนประสิทธิภาพ:</strong></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: {{ $reportData['approval_efficiency']['efficiency_score'] ?? 0 }}%"
                                                 aria-valuenow="{{ $reportData['approval_efficiency']['efficiency_score'] ?? 0 }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ $reportData['approval_efficiency']['efficiency_score'] ?? 0 }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Performance Detailed -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>การประเมินผลการทำงานของผู้ใช้งานโดยละเอียด
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($reportData['user_performance_detailed']) && count($reportData['user_performance_detailed']) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ชื่อผู้ใช้</th>
                                        <th>บทบาท</th>
                                        <th>รายการที่ตรวจสอบ</th>
                                        <th>อัตราการอนุมัติ</th>
                                        <th>เวลาประมวลผลเฉลี่ย</th>
                                        <th>คะแนนคุณภาพ</th>
                                        <th>ระดับประสิทธิภาพ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reportData['user_performance_detailed'] as $user)
                                        <tr>
                                            <td>{{ $user['name'] }}</td>
                                            <td>
                                                <span class="badge {{ $user['role'] === 'super_admin' ? 'bg-danger' : 'bg-primary' }}">
                                                    {{ $user['role'] === 'super_admin' ? 'Super Admin' : 'Admin' }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($user['total_reviewed']) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <small class="me-2">{{ $user['approval_rate'] }}%</small>
                                                    <div class="progress" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar bg-success" 
                                                             style="width: {{ $user['approval_rate'] }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user['avg_processing_hours'] }} ชม.</td>
                                            <td>
                                                @php
                                                    $qualityClass = $user['quality_score'] >= 80 ? 'text-success' : 
                                                                   ($user['quality_score'] >= 60 ? 'text-warning' : 'text-danger');
                                                @endphp
                                                <span class="{{ $qualityClass }}">{{ $user['quality_score'] }}%</span>
                                            </td>
                                            <td>
                                                @php
                                                    $efficiencyClass = match($user['efficiency_rating']) {
                                                        'Excellent' => 'badge bg-success',
                                                        'Good' => 'badge bg-primary',
                                                        'Average' => 'badge bg-warning',
                                                        default => 'badge bg-secondary'
                                                    };
                                                @endphp
                                                <span class="{{ $efficiencyClass }}">{{ $user['efficiency_rating'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">ไม่มีข้อมูลการประเมินผลผู้ใช้งานในช่วงเวลานี้</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bottleneck Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>การวิเคราะห์ปัญหาคอขวด
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($reportData['bottleneck_analysis']))
                        <div class="row">
                            <!-- User Capacity Issues -->
                            <div class="col-md-6 mb-3">
                                <h6><i class="bi bi-person-clock me-2"></i>ปัญหาความสามารถของผู้ใช้</h6>
                                @if($reportData['bottleneck_analysis']['user_capacity_issues']['capacity_imbalance'])
                                    <div class="alert alert-warning">
                                        <strong>พบความไม่สมดุลในการกระจายงาน:</strong>
                                        <ul class="mb-0 mt-2">
                                            @if(count($reportData['bottleneck_analysis']['user_capacity_issues']['overloaded_users']) > 0)
                                                <li>ผู้ใช้ที่งานล้น: {{ count($reportData['bottleneck_analysis']['user_capacity_issues']['overloaded_users']) }} คน</li>
                                            @endif
                                            @if(count($reportData['bottleneck_analysis']['user_capacity_issues']['underutilized_users']) > 0)
                                                <li>ผู้ใช้ที่ใช้งานน้อย: {{ count($reportData['bottleneck_analysis']['user_capacity_issues']['underutilized_users']) }} คน</li>
                                            @endif
                                        </ul>
                                    </div>
                                @else
                                    <div class="alert alert-success">
                                        <i class="bi bi-check-circle me-2"></i>การกระจายงานมีความสมดุลดี
                                    </div>
                                @endif
                            </div>

                            <!-- Peak Hours Analysis -->
                            <div class="col-md-6 mb-3">
                                <h6><i class="bi bi-clock me-2"></i>การวิเคราะห์ช่วงเวลาแออัด</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <small class="text-muted">ช่วงเวลายื่นขอมากที่สุด:</small>
                                        <div><strong>{{ $reportData['bottleneck_analysis']['peak_hours_analysis']['peak_submission_hour'] ?? 'N/A' }}</strong></div>
                                        
                                        <small class="text-muted mt-2 d-block">ช่วงเวลาตรวจสอบมากที่สุด:</small>
                                        <div><strong>{{ $reportData['bottleneck_analysis']['peak_hours_analysis']['peak_review_hour'] ?? 'N/A' }}</strong></div>
                                        
                                        @if(isset($reportData['bottleneck_analysis']['peak_hours_analysis']['mismatch_detected']) && $reportData['bottleneck_analysis']['peak_hours_analysis']['mismatch_detected'])
                                            <div class="mt-2">
                                                <span class="badge bg-warning">พบความไม่ตรงกันของเวลา</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recommendations -->
                        @if(isset($reportData['bottleneck_analysis']['recommendations']) && count($reportData['bottleneck_analysis']['recommendations']) > 0)
                            <div class="mt-3">
                                <h6><i class="bi bi-lightbulb me-2"></i>ข้อเสนอแนะ</h6>
                                <ul class="list-group">
                                    @foreach($reportData['bottleneck_analysis']['recommendations'] as $recommendation)
                                        <li class="list-group-item">
                                            <i class="bi bi-arrow-right me-2 text-primary"></i>{{ $recommendation }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">ไม่มีข้อมูลการวิเคราะห์ปัญหาคอขวด</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-award me-2"></i>ตัวชี้วัดคุณภาพ
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($reportData['quality_metrics']))
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="card bg-primary text-white h-100">
                                    <div class="card-body">
                                        <h6>คะแนนคุณภาพรวม</h6>
                                        <h2>{{ round($reportData['quality_metrics']['quality_score'], 1) }}%</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="card bg-info text-white h-100">
                                    <div class="card-body">
                                        <h6>อัตราการแทนที่</h6>
                                        <h2>{{ round($reportData['quality_metrics']['override_rate'], 1) }}%</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body">
                                        <h6>ความสม่ำเสมอ</h6>
                                        <h2>{{ round($reportData['quality_metrics']['consistency_score'], 1) }}%</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="card bg-warning text-white h-100">
                                    <div class="card-body">
                                        <h6>ความตรงต่อเวลา</h6>
                                        <h2>{{ round($reportData['quality_metrics']['timeliness_score'], 1) }}%</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">ไม่มีข้อมูลตัวชี้วัดคุณภาพ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>ข้อเสนอแนะและแนวทางการปรับปรุง
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($reportData['recommendations']) && count($reportData['recommendations']) > 0)
                        <div class="row">
                            @foreach($reportData['recommendations'] as $recommendation)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-left-{{ $recommendation['type'] === 'critical' ? 'danger' : ($recommendation['type'] === 'warning' ? 'warning' : ($recommendation['type'] === 'success' ? 'success' : 'info')) }}">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                @php
                                                    $icon = match($recommendation['type']) {
                                                        'critical' => 'bi bi-exclamation-triangle text-danger',
                                                        'warning' => 'bi bi-exclamation-circle text-warning',
                                                        'success' => 'bi bi-check-circle text-success',
                                                        default => 'bi bi-info-circle text-info'
                                                    };
                                                @endphp
                                                <i class="{{ $icon }} me-2"></i>
                                                <h6 class="mb-0">{{ $recommendation['title'] }}</h6>
                                            </div>
                                            <p class="text-muted mb-2">{{ $recommendation['description'] }}</p>
                                            @if(isset($recommendation['suggestions']) && count($recommendation['suggestions']) > 0)
                                                <div class="small">
                                                    <strong>ข้อเสนอแนะ:</strong>
                                                    <ul class="mb-0 mt-1">
                                                        @foreach($recommendation['suggestions'] as $suggestion)
                                                            <li>{{ $suggestion }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-hand-thumbs-up fa-3x text-success mb-3"></i>
                            <h5 class="text-success">ระบบทำงานได้ดีเยี่ยม!</h5>
                            <p class="text-muted">ไม่มีข้อเสนอแนะเพิ่มเติมในขณะนี้</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Report Footer -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <small class="text-muted">
                        รายงานนี้สร้างขึ้นเมื่อ {{ now()->format('d/m/Y H:i:s') }} | 
                        ข้อมูลครอบคลุมระยะเวลา {{ $days }} วันที่ผ่านมา |
                        รายงานโดย {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, .navbar, .sidebar {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    .container-fluid {
        padding: 0 !important;
    }
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection