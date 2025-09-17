@extends('layouts.dashboard')

@section('title', 'รายงานผู้ใช้งาน')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark">
                <i class="bi bi-people text-primary me-2"></i>
                รายงานผู้ใช้งาน
            </h1>
            <p class="text-muted mb-0">สถิติและข้อมูลผู้ใช้งานในระบบ</p>
        </div>
        <div>
            <div class="btn-group" role="group">
                <a href="{{ route('super-admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </a>
                <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
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
                            <option value="7" {{ $period == '7' ? 'selected' : '' }}>7 วันที่ผ่านมา</option>
                            <option value="30" {{ $period == '30' ? 'selected' : '' }}>30 วันที่ผ่านมา</option>
                            <option value="90" {{ $period == '90' ? 'selected' : '' }}>90 วันที่ผ่านมา</option>
                            <option value="365" {{ $period == '365' ? 'selected' : '' }}>1 ปีที่ผ่านมา</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-people text-primary fs-1 mb-2"></i>
                    <h4 class="text-primary fw-bold mb-1">{{ number_format($userStats['total_users']) }}</h4>
                    <small class="text-muted">ผู้ใช้ทั้งหมด</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-success-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-person-check text-success fs-1 mb-2"></i>
                    <h4 class="text-success fw-bold mb-1">{{ number_format($userStats['active_users']) }}</h4>
                    <small class="text-muted">ผู้ใช้ที่ใช้งาน</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-info-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-person-plus text-info fs-1 mb-2"></i>
                    <h4 class="text-info fw-bold mb-1">{{ number_format($userStats['new_users']) }}</h4>
                    <small class="text-muted">ผู้ใช้ใหม่</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-shield-check text-warning fs-1 mb-2"></i>
                    <h4 class="text-warning fw-bold mb-1">{{ number_format($userStats['admin_users']) }}</h4>
                    <small class="text-muted">Admin/Super Admin</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm bg-danger-subtle">
                <div class="card-body text-center">
                    <i class="bi bi-person-lock text-danger fs-1 mb-2"></i>
                    <h4 class="text-danger fw-bold mb-1">{{ number_format($userStats['locked_users']) }}</h4>
                    <small class="text-muted">ผู้ใช้ถูกล็อก</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Registration Trend -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        แนวโน้มการลงทะเบียน
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="registrationChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Role Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart text-success me-2"></i>
                        การแจกแจงตามบทบาท
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="roleChart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-trophy text-warning me-2"></i>
                        ผู้ใช้ที่ใช้งานมากที่สุด ({{ $period }} วันที่ผ่านมา)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">#</th>
                                    <th class="border-0">ผู้ใช้</th>
                                    <th class="border-0">บทบาท</th>
                                    <th class="border-0">จำนวน Sessions</th>
                                    <th class="border-0">อันดับ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topUsers as $index => $userSession)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $userSession->user->username ?? 'N/A' }}</h6>
                                                <small class="text-muted">{{ $userSession->user->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($userSession->user && $userSession->user->role == 'super_admin')
                                            <span class="badge bg-danger">Super Admin</span>
                                        @elseif($userSession->user && $userSession->user->role == 'admin')
                                            <span class="badge bg-warning">Admin</span>
                                        @else
                                            <span class="badge bg-primary">User</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ number_format($userSession->session_count) }}</span>
                                    </td>
                                    <td>
                                        @if($index == 0)
                                            <i class="bi bi-trophy-fill text-warning fs-4"></i>
                                        @elseif($index == 1)
                                            <i class="bi bi-award-fill text-secondary fs-4"></i>
                                        @elseif($index == 2)
                                            <i class="bi bi-award text-warning fs-4"></i>
                                        @else
                                            <span class="text-muted">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mt-2">ไม่มีข้อมูลในช่วงเวลาที่เลือก</p>
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
// Registration Trend Chart
const registrationCtx = document.getElementById('registrationChart').getContext('2d');
const registrationChart = new Chart(registrationCtx, {
    type: 'line',
    data: {
        labels: [
            @foreach($registrationData as $data)
                '{{ \Carbon\Carbon::parse($data->date)->format('M j') }}',
            @endforeach
        ],
        datasets: [{
            label: 'การลงทะเบียนรายวัน',
            data: [
                @foreach($registrationData as $data)
                    {{ $data->count }},
                @endforeach
            ],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
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

// Role Distribution Chart
const roleCtx = document.getElementById('roleChart').getContext('2d');
const roleChart = new Chart(roleCtx, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($roleDistribution as $role)
                '{{ ucfirst($role->role) }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($roleDistribution as $role)
                    {{ $role->count }},
                @endforeach
            ],
            backgroundColor: [
                '#0d6efd',
                '#198754', 
                '#ffc107',
                '#dc3545',
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
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection