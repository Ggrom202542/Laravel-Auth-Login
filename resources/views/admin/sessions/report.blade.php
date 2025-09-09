@extends('layouts.dashboard')

@section('title', 'รายงาน Sessions - Admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>รายงาน Sessions
                    </h4>
                    <div class="btn-group">
                        <a href="{{ route('admin.sessions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>กลับ
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-download me-1"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.sessions.export', ['format' => 'csv', 'days' => $days]) }}">
                                    <i class="bi bi-filetype-csv me-1"></i>CSV
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- ตัวกรอง -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <select name="days" class="form-select me-2">
                                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 วันล่าสุด</option>
                                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 วันล่าสุด</option>
                                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 วันล่าสุด</option>
                                </select>
                                <select name="type" class="form-select me-2">
                                    <option value="overview" {{ $type === 'overview' ? 'selected' : '' }}>ภาพรวม</option>
                                    <option value="detailed" {{ $type === 'detailed' ? 'selected' : '' }}>รายละเอียด</option>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- สถิติรวม -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center py-3">
                                    <h5>{{ number_format($statistics['total_sessions']) }}</h5>
                                    <small>Total Sessions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center py-3">
                                    <h5>{{ number_format($statistics['active_sessions']) }}</h5>
                                    <small>Active Sessions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center py-3">
                                    <h5>{{ number_format($statistics['unique_users']) }}</h5>
                                    <small>Unique Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center py-3">
                                    <h5>{{ number_format($statistics['online_users']) }}</h5>
                                    <small>Online Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center py-3">
                                    <h5>{{ number_format($statistics['avg_session_duration']) }}m</h5>
                                    <small>Avg Duration</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center py-3">
                                    <h5>{{ number_format($statistics['peak_concurrent_users']) }}</h5>
                                    <small>Peak Concurrent</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts และรายงาน -->
                    <div class="row">
                        <!-- Daily Sessions Chart -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Sessions ประจำวัน</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="dailySessionsChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Device Breakdown -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">แยกตามอุปกรณ์</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="deviceChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Top Users -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">ผู้ใช้ที่มี Sessions มากที่สุด</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ผู้ใช้</th>
                                                    <th>จำนวน Sessions</th>
                                                    <th>อัตราส่วน</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report['top_users'] as $user)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $user->user->username ?? 'N/A' }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $user->user->email ?? 'N/A' }}</small>
                                                        </div>
                                                    </td>
                                                    <td>{{ $user->session_count }}</td>
                                                    <td>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar" style="width: {{ ($user->session_count / $report['top_users']->max('session_count')) * 100 }}%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Breakdown -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">แยกตามประเทศ</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ประเทศ</th>
                                                    <th>จำนวน Sessions</th>
                                                    <th>อัตราส่วน</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report['location_breakdown'] as $location)
                                                <tr>
                                                    <td>{{ $location->location_country }}</td>
                                                    <td>{{ $location->count }}</td>
                                                    <td>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar bg-info" style="width: {{ ($location->count / $report['location_breakdown']->max('count')) * 100 }}%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
// Daily Sessions Chart
const dailySessionsCtx = document.getElementById('dailySessionsChart').getContext('2d');
const dailySessionsChart = new Chart(dailySessionsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($report['daily_sessions']->pluck('date')) !!},
        datasets: [{
            label: 'Sessions',
            data: {!! json_encode($report['daily_sessions']->pluck('count')) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Device Chart
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
const deviceChart = new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($report['device_breakdown']->pluck('device_type')) !!},
        datasets: [{
            data: {!! json_encode($report['device_breakdown']->pluck('count')) !!},
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.progress {
    background-color: #e9ecef;
}

.table-sm th,
.table-sm td {
    padding: 0.5rem;
    font-size: 0.875rem;
}

.card-body.py-3 {
    padding: 0.75rem 1rem;
}

canvas {
    max-height: 300px;
}
</style>
@endpush
