@extends('layouts.dashboard')

@section('title', 'รายงานระบบ - ผู้ดูแลระบบสูงสุด')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>รายงานระบบเซสชัน
                    </h4>
                    <div>
                        <a href="{{ route('super-admin.sessions.system-report', ['format' => 'excel']) }}" 
                           class="btn btn-success me-2">
                            <i class="bi bi-file-earmark-excel me-1"></i>ส่งออก Excel
                        </a>
                        <a href="{{ route('super-admin.sessions.system-report', ['format' => 'pdf']) }}" 
                           class="btn btn-danger me-2">
                            <i class="bi bi-file-earmark-pdf me-1"></i>ส่งออก PDF
                        </a>
                        <a href="{{ route('super-admin.sessions.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>กลับไปยังแดชบอร์ด
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('super-admin.sessions.system-report') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">วันที่เริ่มต้น</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from', now()->subDays(30)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">วันที่สิ้นสุด</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to', now()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="user_role" class="form-label">บทบาทผู้ใช้</label>
                                <select class="form-select" id="user_role" name="user_role">
                                    <option value="">ทุกบทบาท</option>
                                    <option value="user" {{ request('user_role') == 'user' ? 'selected' : '' }}>ผู้ใช้</option>
                                    <option value="admin" {{ request('user_role') == 'admin' ? 'selected' : '' }}>ผู้ดูแลระบบ</option>
                                    <option value="super_admin" {{ request('user_role') == 'super_admin' ? 'selected' : '' }}>ผู้ดูแลระบบสูงสุด</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel me-1"></i>ใช้ตัวกรอง
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Summary Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>{{ number_format($report['overview']['total_sessions']) }}</h4>
                                    <small>เซสชันทั้งหมด</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>{{ number_format($report['overview']['active_sessions']) }}</h4>
                                    <small>เซสชันที่กำลังใช้งาน</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>{{ number_format($report['user_activity']['unique_users']) }}</h4>
                                    <small>ผู้ใช้ที่ไม่ซ้ำกัน</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>{{ number_format($report['overview']['suspicious_sessions']) }}</h4>
                                    <small>เซสชันที่น่าสงสัย</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">เซสชันรายวัน</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="sessionsChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">เซสชันตามบทบาท</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="rolesChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Device & Browser Analysis -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">อุปกรณ์ยอดนิยม</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ประเภทอุปกรณ์</th>
                                                    <th class="text-end">จำนวน</th>
                                                    <th class="text-end">เปอร์เซ็นต์</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report['device_analysis']['device_types'] as $type => $count)
                                                @php
                                                    $total = array_sum($report['device_analysis']['device_types']);
                                                    $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $type }}</td>
                                                    <td class="text-end">{{ number_format($count) }}</td>
                                                    <td class="text-end">{{ number_format($percentage, 1) }}%</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">เบราว์เซอร์ยอดนิยม</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>เบราว์เซอร์</th>
                                                    <th class="text-end">จำนวน</th>
                                                    <th class="text-end">เปอร์เซ็นต์</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report['device_analysis']['browsers'] as $browser => $count)
                                                @php
                                                    $total = array_sum($report['device_analysis']['browsers']);
                                                    $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $browser }}</td>
                                                    <td class="text-end">{{ number_format($count) }}</td>
                                                    <td class="text-end">{{ number_format($percentage, 1) }}%</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Sessions Activity -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">กิจกรรมเซสชันล่าสุด</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ผู้ใช้</th>
                                                    <th>อีเมล</th>
                                                    <th>บทบาท</th>
                                                    <th>อุปกรณ์</th>
                                                    <th>ตำแหน่งที่ตั้ง</th>
                                                    <th>เริ่มต้น</th>
                                                    <th>สถานะ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentSessions as $session)
                                                <tr>
                                                    <td>{{ $session->user->name }}</td>
                                                    <td>{{ $session->user->email }}</td>
                                                    <td>
                                                        <span class="badge 
                                                            @if($session->user->role === 'super_admin') bg-danger
                                                            @elseif($session->user->role === 'admin') bg-warning
                                                            @else bg-primary
                                                            @endif">
                                                            @if($session->user->role === 'super_admin')
                                                                ผู้ดูแลระบบสูงสุด
                                                            @elseif($session->user->role === 'admin')
                                                                ผู้ดูแลระบบ
                                                            @else
                                                                ผู้ใช้
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <i class="bi 
                                                            @if($session->device_type === 'mobile') bi-phone
                                                            @elseif($session->device_type === 'tablet') bi-tablet
                                                            @else bi-laptop
                                                            @endif me-1">
                                                        </i>
                                                        {{ $session->device_name ?: ($session->device_type === 'mobile' ? 'มือถือ' : ($session->device_type === 'tablet' ? 'แท็บเล็ต' : 'คอมพิวเตอร์')) }}
                                                    </td>
                                                    <td>
                                                        @if($session->location_country && $session->location_city)
                                                            <i class="bi bi-geo-alt me-1"></i>
                                                            {{ $session->location_city }}, {{ $session->location_country }}
                                                        @else
                                                            <span class="text-muted">ไม่ทราบ</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $session->created_at->format('d M Y H:i') }}</td>
                                                    <td>
                                                        @if($session->is_active)
                                                            <span class="badge bg-success">ใช้งานอยู่</span>
                                                        @else
                                                            <span class="badge bg-secondary">สิ้นสุดแล้ว</span>
                                                        @endif
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
document.addEventListener('DOMContentLoaded', function() {
    // Sessions by Day Chart
    const sessionsCtx = document.getElementById('sessionsChart').getContext('2d');
    new Chart(sessionsCtx, {
        type: 'line',
        data: {
            labels: @json($report['chart_data']['sessions']['labels']),
            datasets: [{
                label: 'เซสชัน',
                data: @json($report['chart_data']['sessions']['data']),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Sessions by Role Chart
    const rolesCtx = document.getElementById('rolesChart').getContext('2d');
    new Chart(rolesCtx, {
        type: 'doughnut',
        data: {
            labels: @json($report['chart_data']['roles']['labels']),
            datasets: [{
                data: @json($report['chart_data']['roles']['data']),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush
