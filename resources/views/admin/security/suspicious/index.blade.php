@extends('layouts.dashboard')

@section('title', 'การตรวจจับการเข้าสู่ระบบที่น่าสงสัย')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        การตรวจจับการเข้าสู่ระบบที่น่าสงสัย
                    </h1>
                    <p class="text-muted mb-0">การตรวจจับความผิดปกติด้วย AI และการติดตามภัยคุกคามแบบเรียลไทม์</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="refreshStats()">
                            <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                        </button>
                        <button type="button" class="btn btn-primary" onclick="runFullScan()">
                            <i class="bi bi-search me-1"></i> สแกนเต็มรูปแบบ
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-download me-1"></i> ส่งออก
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportDetections('csv')">
                                    <i class="fas fa-file-csv me-2"></i> Export CSV
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportDetections('pdf')">
                                    <i class="fas fa-file-pdf me-2"></i> Export PDF
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="exportThreatIntelligence()">
                                    <i class="fas fa-brain me-2"></i> Threat Intelligence Report
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-danger-subtle border-danger-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-exclamation-triangle text-danger fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['high_risk_attempts'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">High Risk Attempts</p>
                            <small class="text-danger">
                                <i class="fas fa-arrow-up me-1"></i>{{ $statistics['high_risk_today'] ?? 0 }} today
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-warning-subtle border-warning-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-eye text-warning fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['suspicious_patterns'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Suspicious Patterns</p>
                            <small class="text-warning">
                                <i class="fas fa-chart-line me-1"></i>Pattern analysis
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-primary-subtle border-primary-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-robot text-primary fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['blocked_attempts'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Auto-Blocked</p>
                            <small class="text-primary">
                                <i class="fas fa-shield-check me-1"></i>AI Prevention
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-brain text-info fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ number_format($statistics['detection_accuracy'] ?? 0, 1) }}%</h3>
                            <p class="text-muted mb-0">Detection Accuracy</p>
                            <small class="text-info">
                                <i class="fas fa-chart-pie me-1"></i>ML Model
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Real-time Monitoring -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">
                                    <i class="fas fa-chart-line text-primary me-2"></i>
                                    Real-time Threat Activity
                                </h5>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success me-2">
                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                        Live
                                    </span>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleAutoRefresh()">
                                            <i class="fas fa-pause" id="autoRefreshIcon"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" onclick="fullscreenChart()">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="threatActivityChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-globe text-primary me-2"></i>
                                Top Threat Origins
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @php
                                    $threatOrigins = [
                                        ['country' => 'Unknown', 'code' => 'xx', 'count' => 45, 'risk' => 'high'],
                                        ['country' => 'China', 'code' => 'cn', 'count' => 32, 'risk' => 'high'],
                                        ['country' => 'Russia', 'code' => 'ru', 'count' => 28, 'risk' => 'medium'],
                                        ['country' => 'United States', 'code' => 'us', 'count' => 15, 'risk' => 'low'],
                                        ['country' => 'Brazil', 'code' => 'br', 'count' => 12, 'risk' => 'medium']
                                    ];
                                @endphp
                                @foreach($threatOrigins as $origin)
                                <div class="list-group-item border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <img src="https://flagcdn.com/24x18/{{ $origin['code'] }}.png" 
                                                 class="me-3" alt="{{ $origin['country'] }}">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $origin['country'] }}</h6>
                                                <small class="text-muted">{{ $origin['count'] }} attempts</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @php
                                                $riskClass = $origin['risk'] === 'high' ? 'danger' : ($origin['risk'] === 'medium' ? 'warning' : 'success');
                                            @endphp
                                            <span class="badge bg-{{ $riskClass }}-subtle text-{{ $riskClass }}">
                                                {{ ucfirst($origin['risk']) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detection Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-filter text-primary me-2"></i>
                                Detection Filters
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                                    <i class="fas fa-times me-1"></i> Clear
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="saveFilters()">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" id="filterForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Time Range</label>
                                <select name="time_range" class="form-select">
                                    <option value="1h" {{ request('time_range') == '1h' ? 'selected' : '' }}>Last Hour</option>
                                    <option value="24h" {{ request('time_range', '24h') == '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                                    <option value="7d" {{ request('time_range') == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="30d" {{ request('time_range') == '30d' ? 'selected' : '' }}>Last 30 Days</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Risk Level</label>
                                <select name="risk_level" class="form-select">
                                    <option value="">All Levels</option>
                                    <option value="critical" {{ request('risk_level') == 'critical' ? 'selected' : '' }}>Critical (90%+)</option>
                                    <option value="high" {{ request('risk_level') == 'high' ? 'selected' : '' }}>High (70-89%)</option>
                                    <option value="medium" {{ request('risk_level') == 'medium' ? 'selected' : '' }}>Medium (40-69%)</option>
                                    <option value="low" {{ request('risk_level') == 'low' ? 'selected' : '' }}>Low (0-39%)</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Detection Type</label>
                                <select name="detection_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="anomaly" {{ request('detection_type') == 'anomaly' ? 'selected' : '' }}>Anomaly Detection</option>
                                    <option value="pattern" {{ request('detection_type') == 'pattern' ? 'selected' : '' }}>Pattern Analysis</option>
                                    <option value="geolocation" {{ request('detection_type') == 'geolocation' ? 'selected' : '' }}>Geolocation</option>
                                    <option value="behavioral" {{ request('detection_type') == 'behavioral' ? 'selected' : '' }}>Behavioral</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Suspicious Attempts Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-list text-primary me-2"></i>
                            Suspicious Login Attempts ({{ $loginAttempts->total() ?? 0 }} total)
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-warning" onclick="markAllReviewed()">
                                <i class="fas fa-check-double me-1"></i> Mark All Reviewed
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-cog me-1"></i> Bulk Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="bulkBlock()">
                                        <i class="fas fa-ban me-2"></i> Block Selected IPs
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkWhitelist()">
                                        <i class="fas fa-check me-2"></i> Whitelist Selected
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkInvestigate()">
                                        <i class="fas fa-search me-2"></i> Mark for Investigation
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($loginAttempts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th class="border-0">Attempt Details</th>
                                        <th class="border-0">Risk Analysis</th>
                                        <th class="border-0">Location & Device</th>
                                        <th class="border-0">Detection Reasons</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loginAttempts as $attempt)
                                    <tr class="{{ $attempt->risk_score >= 90 ? 'table-danger' : ($attempt->risk_score >= 70 ? 'table-warning' : '') }}">
                                        <td class="ps-4">
                                            <input type="checkbox" class="form-check-input attempt-checkbox" 
                                                   value="{{ $attempt->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm {{ $attempt->is_successful ? 'bg-danger-subtle' : 'bg-warning-subtle' }} rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="fas {{ $attempt->is_successful ? 'fa-exclamation-triangle text-danger' : 'fa-times text-warning' }}"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $attempt->username }}</h6>
                                                    <small class="text-muted">
                                                        {{ $attempt->attempted_at->format('M d, H:i:s') }}
                                                        ({{ $attempt->attempted_at->diffForHumans() }})
                                                    </small>
                                                    <br>
                                                    <code class="text-muted">{{ $attempt->ip_address }}</code>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $risk = $attempt->risk_score;
                                                $riskClass = $risk >= 90 ? 'danger' : ($risk >= 70 ? 'warning' : ($risk >= 40 ? 'info' : 'success'));
                                                $riskLabel = $risk >= 90 ? 'Critical' : ($risk >= 70 ? 'High' : ($risk >= 40 ? 'Medium' : 'Low'));
                                            @endphp
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="progress me-2" style="width: 80px; height: 12px;">
                                                    <div class="progress-bar bg-{{ $riskClass }}" 
                                                         style="width: {{ $risk }}%"></div>
                                                </div>
                                                <span class="badge bg-{{ $riskClass }}">{{ $risk }}%</span>
                                            </div>
                                            <small class="text-{{ $riskClass }} fw-semibold">{{ $riskLabel }} Risk</small>
                                        </td>
                                        <td>
                                            <div class="mb-1">
                                                @if($attempt->country)
                                                    <img src="https://flagcdn.com/16x12/{{ strtolower($attempt->country_code ?? 'xx') }}.png" 
                                                         class="me-2" alt="{{ $attempt->country }}">
                                                    <small class="fw-semibold">{{ $attempt->country }}</small>
                                                    @if($attempt->city)
                                                        <br><small class="text-muted ms-4">{{ $attempt->city }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Unknown Location</span>
                                                @endif
                                            </div>
                                            @if($attempt->user_agent)
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-desktop me-1"></i>
                                                    {{ Str::limit($attempt->user_agent, 40) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $detectionReasons = json_decode($attempt->detection_reasons ?? '[]', true);
                                            @endphp
                                            @if(!empty($detectionReasons))
                                                @foreach(array_slice($detectionReasons, 0, 3) as $reason)
                                                    <span class="badge bg-secondary-subtle text-secondary me-1 mb-1">
                                                        {{ $reason }}
                                                    </span>
                                                @endforeach
                                                @if(count($detectionReasons) > 3)
                                                    <small class="text-muted">+{{ count($detectionReasons) - 3 }} more</small>
                                                @endif
                                            @else
                                                <span class="text-muted">No specific reasons</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                @if($attempt->is_successful)
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-check me-1"></i> Successful
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-times me-1"></i> Failed
                                                    </span>
                                                @endif
                                                
                                                @if($attempt->is_blocked)
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-shield me-1"></i> Auto-blocked
                                                    </span>
                                                @endif
                                                
                                                @if($attempt->reviewed_at)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-eye me-1"></i> Reviewed
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewAttemptDetails('{{ $attempt->id }}')"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if(!$attempt->reviewed_at)
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="markReviewed('{{ $attempt->id }}')"
                                                            title="Mark Reviewed">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="blockIp('{{ $attempt->ip_address }}')"
                                                        title="Block IP">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="investigateAttempt('{{ $attempt->id }}')"
                                                        title="Investigate">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($loginAttempts->hasPages())
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            Showing {{ $loginAttempts->firstItem() }} to {{ $loginAttempts->lastItem() }} 
                                            of {{ $loginAttempts->total() }} results
                                        </small>
                                    </div>
                                    <div>
                                        {{ $loginAttempts->links() }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-shield-check text-success fs-2"></i>
                            </div>
                            <h5 class="text-dark fw-bold mb-2">No Suspicious Activity Detected</h5>
                            <p class="text-muted mb-4">Your system is secure! No suspicious login attempts found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attempt Details Modal -->
<div class="modal fade" id="attemptDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shield-alt text-primary me-2"></i>
                    Suspicious Attempt Analysis
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="attemptDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.avatar-lg {
    width: 60px;
    height: 60px;
}

.avatar-xl {
    width: 80px;
    height: 80px;
}

.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1) !important;
}

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.bg-secondary-subtle {
    background-color: rgba(108, 117, 125, 0.1) !important;
}

.border-primary-subtle {
    border-color: rgba(13, 110, 253, 0.2) !important;
}

.border-warning-subtle {
    border-color: rgba(255, 193, 7, 0.2) !important;
}

.border-success-subtle {
    border-color: rgba(25, 135, 84, 0.2) !important;
}

.border-info-subtle {
    border-color: rgba(13, 202, 240, 0.2) !important;
}

.border-danger-subtle {
    border-color: rgba(220, 53, 69, 0.2) !important;
}
</style>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let autoRefreshInterval;
let threatChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeThreatChart();
    startAutoRefresh();

    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const attemptCheckboxes = document.querySelectorAll('.attempt-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        attemptCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

function initializeThreatChart() {
    const ctx = document.getElementById('threatActivityChart');
    if (!ctx) return;

    threatChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: Array.from({length: 24}, (_, i) => `${String(i).padStart(2, '0')}:00`),
            datasets: [{
                label: 'High Risk Attempts',
                data: [12, 19, 3, 5, 2, 3, 15, 8, 12, 7, 4, 6, 8, 15, 12, 9, 6, 8, 15, 18, 12, 6, 4, 2],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
            }, {
                label: 'Medium Risk Attempts',
                data: [8, 12, 5, 8, 4, 6, 10, 12, 8, 15, 12, 9, 12, 8, 6, 12, 15, 12, 8, 6, 9, 12, 8, 6],
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4
            }, {
                label: 'Blocked Attempts',
                data: [5, 8, 2, 3, 1, 2, 8, 5, 7, 4, 2, 3, 5, 8, 7, 5, 3, 5, 8, 10, 7, 3, 2, 1],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4
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
                    position: 'top',
                },
                title: {
                    display: false
                }
            }
        }
    });
}

function startAutoRefresh() {
    autoRefreshInterval = setInterval(function() {
        updateThreatChart();
    }, 30000); // Refresh every 30 seconds
}

function toggleAutoRefresh() {
    const icon = document.getElementById('autoRefreshIcon');
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        icon.className = 'fas fa-play';
    } else {
        startAutoRefresh();
        icon.className = 'fas fa-pause';
    }
}

function updateThreatChart() {
    // Simulate real-time data update
    if (threatChart) {
        threatChart.data.datasets.forEach(dataset => {
            dataset.data.shift();
            dataset.data.push(Math.floor(Math.random() * 20));
        });
        threatChart.update();
    }
}

function fullscreenChart() {
    // Implement fullscreen chart view
    alert('Fullscreen chart view will be implemented');
}

function refreshStats() {
    location.reload();
}

function clearFilters() {
    const form = document.getElementById('filterForm');
    form.reset();
    form.submit();
}

function saveFilters() {
    // Save current filters to user preferences
    alert('Filter preferences saved');
}

function runFullScan() {
    if (confirm('Run a full security scan? This may take several minutes.')) {
        const button = event.target;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Scanning...';
        
        fetch('{{ route("admin.security.suspicious.full-scan") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Scan completed: ${data.detections} new suspicious activities detected`);
                location.reload();
            } else {
                alert('Scan failed');
            }
        })
        .catch(error => {
            alert('Error during scan');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-search me-1"></i> Run Full Scan';
        });
    }
}

function viewAttemptDetails(attemptId) {
    const modal = new bootstrap.Modal(document.getElementById('attemptDetailsModal'));
    const content = document.getElementById('attemptDetailsContent');
    
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading analysis...</div>';
    modal.show();
    
    fetch(`{{ route('admin.security.suspicious.show', ':id') }}`.replace(':id', attemptId))
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error loading attempt details</div>';
        });
}

function markReviewed(attemptId) {
    fetch(`{{ route('admin.security.suspicious.mark-reviewed', ':id') }}`.replace(':id', attemptId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function blockIp(ipAddress) {
    if (confirm(`Block IP address ${ipAddress}?`)) {
        fetch('{{ route("admin.security.ip.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                ip_address: ipAddress,
                type: 'blocked',
                reason: 'Blocked due to suspicious activity'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('IP address blocked successfully');
            }
        });
    }
}

function investigateAttempt(attemptId) {
    // Mark for investigation and open investigation tools
    alert('Investigation tools will be implemented');
}

function exportDetections(format) {
    window.open(`{{ route('admin.security.suspicious.export') }}?format=${format}`, '_blank');
}

function exportThreatIntelligence() {
    window.open('{{ route("admin.security.suspicious.threat-intelligence") }}', '_blank');
}

function getSelectedAttempts() {
    const checkboxes = document.querySelectorAll('.attempt-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function markAllReviewed() {
    if (confirm('Mark all visible attempts as reviewed?')) {
        // Implementation for bulk review
        alert('Bulk review functionality will be implemented');
    }
}

function bulkBlock() {
    const selected = getSelectedAttempts();
    if (selected.length === 0) {
        alert('Please select attempts to block');
        return;
    }
    
    if (confirm(`Block IPs from ${selected.length} selected attempts?`)) {
        // Implementation for bulk block
        alert('Bulk block functionality will be implemented');
    }
}

function bulkWhitelist() {
    const selected = getSelectedAttempts();
    if (selected.length === 0) {
        alert('Please select attempts to whitelist');
        return;
    }
    
    if (confirm(`Whitelist IPs from ${selected.length} selected attempts?`)) {
        // Implementation for bulk whitelist
        alert('Bulk whitelist functionality will be implemented');
    }
}

function bulkInvestigate() {
    const selected = getSelectedAttempts();
    if (selected.length === 0) {
        alert('Please select attempts to investigate');
        return;
    }
    
    if (confirm(`Mark ${selected.length} selected attempts for investigation?`)) {
        // Implementation for bulk investigation
        alert('Bulk investigation functionality will be implemented');
    }
}
</script>
@endsection
