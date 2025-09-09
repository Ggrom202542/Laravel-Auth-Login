@extends('layouts.dashboard')

@section('title', 'Session Management Dashboard - Super Admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1">Session Management Dashboard</h3>
                    <p class="text-muted mb-0">ภาพรวมการจัดการ Sessions ทั้งระบบ</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('super-admin.sessions.index') }}" class="btn btn-primary">
                        <i class="bi bi-list-ul me-1"></i>All Sessions
                    </a>
                    <a href="{{ route('super-admin.sessions.realtime') }}" class="btn btn-success">
                        <i class="bi bi-broadcast me-1"></i>Real-time
                    </a>
                    <a href="{{ route('super-admin.sessions.settings') }}" class="btn btn-secondary">
                        <i class="bi bi-gear me-1"></i>Settings
                    </a>
                </div>
            </div>

            <!-- System Overview Stats -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-server fs-1 mb-2"></i>
                            <h4>{{ number_format($stats['total_sessions']) }}</h4>
                            <small>Total Sessions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-wifi fs-1 mb-2"></i>
                            <h4>{{ number_format($stats['active_sessions']) }}</h4>
                            <small>Active Sessions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-people fs-1 mb-2"></i>
                            <h4>{{ number_format($stats['unique_users']) }}</h4>
                            <small>Unique Users</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-person-check fs-1 mb-2"></i>
                            <h4>{{ number_format($stats['online_users']) }}</h4>
                            <small>Online Now</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-clock fs-1 mb-2"></i>
                            <h4>{{ number_format($stats['avg_session_duration']) }}m</h4>
                            <small>Avg Duration</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                            <h4>{{ number_format($stats['suspicious_sessions']) }}</h4>
                            <small>Suspicious</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <!-- Daily Sessions Trend -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Daily Sessions Trend (30 วัน)</h6>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateChart('7')">7d</button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="updateChart('30')">30d</button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateChart('90')">90d</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="dailySessionsChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Role Distribution -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Users by Role</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="roleChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device & Location Stats -->
            <div class="row mb-4">
                <!-- Device Statistics -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Device Statistics</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="deviceChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Geographic Distribution -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Top Locations</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Country</th>
                                            <th>Sessions</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalLocationSessions = collect($chartData['location_stats'])->sum() @endphp
                                        @foreach($chartData['location_stats'] as $country => $count)
                                        <tr>
                                            <td>
                                                <i class="bi bi-geo-alt me-1"></i>{{ $country }}
                                            </td>
                                            <td>{{ number_format($count) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2">{{ round(($count / $totalLocationSessions) * 100, 1) }}%</span>
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-info" style="width: {{ ($count / max($chartData['location_stats'])) * 100 }}%"></div>
                                                    </div>
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

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-warning w-100 mb-2" onclick="bulkAction('cleanup')">
                                        <i class="bi bi-trash me-2"></i>
                                        Cleanup Expired Sessions
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-info w-100 mb-2" onclick="generateReport()">
                                        <i class="bi bi-bar-chart me-2"></i>
                                        Generate System Report
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-success w-100 mb-2" onclick="exportData()">
                                        <i class="bi bi-download me-2"></i>
                                        Export Session Data
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#bulkActionsModal">
                                        <i class="bi bi-people-fill me-2"></i>
                                        Bulk User Actions
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk User Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkActionsForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="action" class="form-label">Action</label>
                                <select class="form-select" id="action" name="action" required>
                                    <option value="">เลือกการดำเนินการ</option>
                                    <option value="terminate">Terminate All Sessions</option>
                                    <option value="trust">Trust All Devices</option>
                                    <option value="untrust">Untrust All Devices</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_ids" class="form-label">Select Users</label>
                                <select class="form-select" id="user_ids" name="user_ids[]" multiple size="5">
                                    <!-- Users will be loaded via AJAX -->
                                </select>
                                <small class="text-muted">Hold Ctrl to select multiple users</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" 
                                  placeholder="ระบุเหตุผลในการดำเนินการ..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitBulkActions()">
                    Execute Action
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let dailySessionsChart, roleChart, deviceChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadUsersForBulkActions();
});

function initializeCharts() {
    // Daily Sessions Chart
    const dailyCtx = document.getElementById('dailySessionsChart').getContext('2d');
    dailySessionsChart = new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($chartData['daily_sessions'], 'date')) !!},
            datasets: [{
                label: 'Total Sessions',
                data: {!! json_encode(array_column($chartData['daily_sessions'], 'sessions')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.3,
                fill: true
            }, {
                label: 'Unique Users',
                data: {!! json_encode(array_column($chartData['daily_sessions'], 'unique_users')) !!},
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.3,
                fill: true
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

    // Role Distribution Chart
    const roleCtx = document.getElementById('roleChart').getContext('2d');
    roleChart = new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($chartData['role_distribution'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($chartData['role_distribution'])) !!},
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
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

    // Device Chart
    const deviceCtx = document.getElementById('deviceChart').getContext('2d');
    deviceChart = new Chart(deviceCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($chartData['device_stats'])) !!},
            datasets: [{
                label: 'Sessions',
                data: {!! json_encode(array_values($chartData['device_stats'])) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
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

function updateChart(days) {
    // Update chart data via AJAX
    fetch(`{{ route('super-admin.sessions.dashboard') }}?days=${days}`)
        .then(response => response.json())
        .then(data => {
            dailySessionsChart.data.labels = data.chartData.daily_sessions.map(item => item.date);
            dailySessionsChart.data.datasets[0].data = data.chartData.daily_sessions.map(item => item.sessions);
            dailySessionsChart.data.datasets[1].data = data.chartData.daily_sessions.map(item => item.unique_users);
            dailySessionsChart.update();
        })
        .catch(error => console.error('Error updating chart:', error));
}

function bulkAction(action) {
    if (action === 'cleanup') {
        if (confirm('คุณต้องการล้าง sessions ที่หมดอายุทั้งหมดหรือไม่?')) {
            fetch('{{ route("super-admin.sessions.bulk-actions") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: 'cleanup'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        }
    }
}

function generateReport() {
    window.open('{{ route("super-admin.sessions.system-report") }}', '_blank');
}

function exportData() {
    window.open('{{ route("super-admin.sessions.advanced-export", ["format" => "csv", "period" => "month"]) }}', '_blank');
}

function loadUsersForBulkActions() {
    // Load users via AJAX
    fetch('/api/users/list')
        .then(response => response.json())
        .then(users => {
            const select = document.getElementById('user_ids');
            select.innerHTML = '';
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.username} (${user.email})`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading users:', error));
}

function submitBulkActions() {
    const form = document.getElementById('bulkActionsForm');
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const data = {};
    formData.forEach((value, key) => {
        if (key === 'user_ids[]') {
            if (!data.user_ids) data.user_ids = [];
            data.user_ids.push(value);
        } else {
            data[key] = value;
        }
    });

    if (!data.action || !data.user_ids || data.user_ids.length === 0) {
        alert('กรุณาเลือกการดำเนินการและผู้ใช้');
        return;
    }

    if (confirm(`คุณต้องการดำเนินการ ${data.action} กับผู้ใช้ ${data.user_ids.length} คนหรือไม่?`)) {
        fetch('{{ route("super-admin.sessions.bulk-actions") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('bulkActionsModal')).hide();
                alert(data.message);
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        });
    }
}

// Auto-refresh every 60 seconds
setInterval(function() {
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 60000);
</script>
@endpush

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-body {
    padding: 1.5rem;
}

.table-sm th,
.table-sm td {
    padding: 0.5rem;
    font-size: 0.875rem;
}

.progress {
    background-color: #e9ecef;
}

.btn-group-sm .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

#dailySessionsChart {
    height: 300px !important;
}

.modal-lg {
    max-width: 800px;
}

select[multiple] {
    height: auto;
}
</style>
@endpush
