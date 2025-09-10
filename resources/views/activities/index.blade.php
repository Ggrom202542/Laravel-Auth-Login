@extends('layouts.dashboard')

@section('title', 'ประวัติกิจกรรม')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-clock-history me-2"></i>ประวัติกิจกรรม
    </h1>
    <div class="d-flex gap-2">
        @if($canViewAll)
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#chartModal">
                <i class="bi bi-graph-up me-1"></i>ดูกราฟ
            </button>
        @endif
        <button type="button" class="btn btn-success btn-sm" onclick="exportActivities()">
            <i class="bi bi-download me-1"></i>ส่งออกข้อมูล
        </button>
    </div>
</div>

<!-- สถิติภาพรวม -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            กิจกรรมทั้งหมด
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_activities']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-list-check fa-2x text-gray-300"></i>
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
                            กิจกรรมวันนี้
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['activities_today']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-day fa-2x text-gray-300"></i>
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
                            กิจกรรมน่าสงสัย
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['suspicious_activities']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
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
                            IP Address ที่แตกต่าง
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['unique_ips']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-globe fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ตัวกรอง -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-funnel me-2"></i>ตัวกรองข้อมูล
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('activities.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="activity_type" class="form-label">ประเภทกิจกรรม</label>
                    <select name="activity_type" id="activity_type" class="form-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type }}" {{ request('activity_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="date_from" class="form-label">วันที่เริ่มต้น</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="date_to" class="form-label">วันที่สิ้นสุด</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                </div>
                
                @if($canViewAll)
                <div class="col-md-3 mb-3">
                    <label for="user_id" class="form-label">ผู้ใช้</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="col-md-2 mb-3">
                    <label for="ip_address" class="form-label">IP Address</label>
                    <input type="text" name="ip_address" id="ip_address" class="form-control" 
                           placeholder="ค้นหา IP" value="{{ request('ip_address') }}">
                </div>
                
                <div class="col-12">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="suspicious" value="1" 
                               id="suspicious" {{ request('suspicious') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="suspicious">
                            แสดงเฉพาะกิจกรรมที่น่าสงสัย
                        </label>
                    </div>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                    <a href="{{ route('activities.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>รีเซ็ต
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- รายการกิจกรรม -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-list-ul me-2"></i>รายการประวัติกิจกรรม
        </h6>
    </div>
    <div class="card-body">
        @if($activities->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="activitiesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="12%">วันที่/เวลา</th>
                            @if($canViewAll)
                            <th width="15%">ผู้ใช้</th>
                            @endif
                            <th width="15%">ประเภท</th>
                            <th width="25%">คำอธิบาย</th>
                            <th width="12%">IP Address</th>
                            <th width="8%">สถานะ</th>
                            <th width="8%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                        <tr class="{{ $activity->is_suspicious ? 'table-warning' : '' }}">
                            <td>{{ $activity->id }}</td>
                            <td>
                                <small class="text-muted">{{ $activity->created_at->format('d/m/Y') }}</small><br>
                                <strong>{{ $activity->created_at->format('H:i:s') }}</strong>
                            </td>
                            @if($canViewAll)
                            <td>
                                @if($activity->user)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $activity->user->profile_image ? asset('storage/avatars/'.$activity->user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($activity->user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                             alt="{{ $activity->user->name }}" 
                                             class="rounded-circle me-2" style="width: 30px; height: 30px;">
                                        <div>
                                            <div class="fw-bold">{{ $activity->user->name }}</div>
                                            <small class="text-muted">{{ ucfirst($activity->user->role) }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            @endif
                            <td>
                                <span class="d-flex align-items-center">
                                    <i class="{{ $activity->activity_icon }} me-2"></i>
                                    {{ $activity->friendly_description }}
                                </span>
                            </td>
                            <td>
                                <span title="{{ $activity->description }}">
                                    {{ Str::limit($activity->description, 80) }}
                                </span>
                                @if($activity->browser_info && $activity->browser_info != 'ไม่ทราบ')
                                    <br><small class="text-muted">{{ $activity->browser_info }}</small>
                                @endif
                            </td>
                            <td>
                                <code>{{ $activity->ip_address }}</code>
                                @if($activity->location)
                                    <br><small class="text-muted">{{ $activity->location }}</small>
                                @endif
                            </td>
                            <td>
                                @if($activity->is_suspicious)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-triangle me-1"></i>น่าสงสัย
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>ปกติ
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('activities.show', $activity->id) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($canViewAll)
                                        @if($activity->is_suspicious)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    onclick="unmarkSuspicious({{ $activity->id }})"
                                                    title="ยกเลิกการทำเครื่องหมาย">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning" 
                                                    onclick="markSuspicious({{ $activity->id }})"
                                                    title="ทำเครื่องหมายว่าน่าสงสัย">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $activities->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h5 class="text-muted mt-3">ไม่พบประวัติกิจกรรม</h5>
                <p class="text-muted">ลองปรับเปลี่ยนเงื่อนไขการค้นหา</p>
            </div>
        @endif
    </div>
</div>

@if($canViewAll)
<!-- Modal สำหรับแสดงกราฟ -->
<div class="modal fade" id="chartModal" tabindex="-1" aria-labelledby="chartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chartModalLabel">
                    <i class="bi bi-graph-up me-2"></i>สถิติกิจกรรม
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="chartDays" class="form-label">ช่วงเวลา</label>
                        <select id="chartDays" class="form-select" onchange="loadChartData()">
                            <option value="7">7 วันที่ผ่านมา</option>
                            <option value="30">30 วันที่ผ่านมา</option>
                            <option value="90">90 วันที่ผ่านมา</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <h6>กิจกรรมรายวัน</h6>
                        <canvas id="dailyChart" width="400" height="200"></canvas>
                    </div>
                    <div class="col-md-4">
                        <h6>ประเภทกิจกรรม</h6>
                        <canvas id="typeChart" width="200" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let dailyChart, typeChart;

function exportActivities() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.location.href = "{{ route('activities.export') }}?" + params.toString();
}

@if($canViewAll)
function markSuspicious(activityId) {
    const reason = prompt('กรุณาระบุเหตุผลที่ทำเครื่องหมายว่าน่าสงสัย:');
    if (reason === null) return;
    
    fetch(`/activities/${activityId}/mark-suspicious`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถดำเนินการได้'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}

function unmarkSuspicious(activityId) {
    if (!confirm('คุณต้องการยกเลิกการทำเครื่องหมายว่าน่าสงสัยใช่หรือไม่?')) return;
    
    fetch(`/activities/${activityId}/unmark-suspicious`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถดำเนินการได้'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}

function loadChartData() {
    const days = document.getElementById('chartDays').value;
    
    fetch(`/activities/chart-data?days=${days}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateDailyChart(data.daily_data);
            updateTypeChart(data.type_data);
        }
    })
    .catch(error => {
        console.error('Error loading chart data:', error);
    });
}

function updateDailyChart(data) {
    const ctx = document.getElementById('dailyChart').getContext('2d');
    
    if (dailyChart) {
        dailyChart.destroy();
    }
    
    dailyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.formatted_date),
            datasets: [{
                label: 'จำนวนกิจกรรม',
                data: data.map(item => item.count),
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
}

function updateTypeChart(data) {
    const ctx = document.getElementById('typeChart').getContext('2d');
    
    if (typeChart) {
        typeChart.destroy();
    }
    
    typeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.activity_type),
            datasets: [{
                data: data.map(item => item.count),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#C9CBCF',
                    '#4BC0C0',
                    '#FF6384'
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
}

// โหลดข้อมูลกราฟเมื่อเปิด modal
document.getElementById('chartModal').addEventListener('shown.bs.modal', function () {
    loadChartData();
});
@endif

// Auto refresh ทุก 5 นาที
setInterval(() => {
    if (!document.hidden) {
        location.reload();
    }
}, 300000);
</script>
@endpush
