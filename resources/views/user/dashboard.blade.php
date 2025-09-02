@extends('layouts.dashboard')

@section('title', 'User Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user-circle me-2"></i>
                    User Dashboard
                </h1>
                <div class="text-end">
                    <small class="text-muted">
                        ล็อกอินล่าสุด: {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d/m/Y H:i') : 'ยังไม่เคยล็อกอิน' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">
                                ยินดีต้อนรับ, {{ auth()->user()->name }}!
                            </h4>
                            <p class="card-text mb-0">
                                คุณเข้าใช้งานในฐานะ <strong>ผู้ใช้งาน</strong> 
                                วันนี้เป็นวันที่ {{ now()->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end d-none d-md-block">
                            <i class="fas fa-user-check fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                การเข้าสู่ระบบทั้งหมด
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_logins'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                กิจกรรมวันนี้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['today_activities'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                สถานะบัญชี
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if(auth()->user()->status === 'active')
                                    <span class="badge bg-success">ใช้งานได้</span>
                                @else
                                    <span class="badge bg-danger">ปิดใช้งาน</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                บทบาท
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="badge bg-primary">{{ ucfirst(auth()->user()->role) }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Activity Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area me-2"></i>
                        กราฟกิจกรรมรายวัน (7 วันที่ผ่านมา)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="activityChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        กิจกรรมล่าสุด
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($recentActivities) && $recentActivities->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentActivities as $activity)
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $activity->action }}</h6>
                                        <p class="mb-1 text-muted small">{{ $activity->description ?? 'ไม่มีรายละเอียด' }}</p>
                                    </div>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-primary btn-sm">ดูกิจกรรมทั้งหมด</a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">ยังไม่มีกิจกรรมที่บันทึกไว้</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Profile & Settings Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs me-2"></i>
                        การดำเนินการด่วน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-primary">
                                    <i class="fas fa-user-edit me-2"></i>
                                    แก้ไขโปรไฟล์
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-success">
                                    <i class="fas fa-key me-2"></i>
                                    เปลี่ยนรหัสผ่าน
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-info">
                                    <i class="fas fa-history me-2"></i>
                                    ประวัติกิจกรรม
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="#" class="btn btn-outline-warning">
                                    <i class="fas fa-cog me-2"></i>
                                    ตั้งค่า
                                </a>
                            </div>
                        </div>
                    </div>
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
    // Activity Chart
    const ctx = document.getElementById('activityChart');
    if (ctx) {
        const activityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels'] ?? ['วันนี้']),
                datasets: [{
                    label: 'กิจกรรม',
                    data: @json($chartData['data'] ?? [0]),
                    borderColor: 'rgba(78, 115, 223, 1)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
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
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
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
.chart-area {
    position: relative;
    height: 300px;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.list-group-item:last-child {
    border-bottom: none !important;
}
</style>
@endpush
