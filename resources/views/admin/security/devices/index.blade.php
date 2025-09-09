@extends('layouts.dashboard')

@section('title', 'ระบบจัดการอุปกรณ์')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-phone text-primary me-2"></i>
                        ระบบจัดการอุปกรณ์
                    </h1>
                    <p class="text-muted mb-0">ติดตามและจัดการอุปกรณ์ของผู้ใช้ อุปกรณ์ที่ไว้วางใจ และความปลอดภัยของอุปกรณ์</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="refreshStats()">
                            <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-download me-1"></i> ส่งออก
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportDevices('csv')">
                                    <i class="bi bi-filetype-csv me-2"></i> ส่งออก CSV
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportDevices('pdf')">
                                    <i class="bi bi-filetype-pdf me-2"></i> ส่งออก PDF
                                </a></li>
                            </ul>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-gear me-1"></i> การจัดการแอดมิน
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="cleanupOldDevices()">
                                    <i class="bi bi-brush me-2"></i> ล้างอุปกรณ์เก่า
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="revokeAllDevices()">
                                    <i class="bi bi-ban me-2"></i> เพิกถอนอุปกรณ์ที่ไม่ไว้วางใจ
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="showDeviceSettings()">
                                    <i class="bi bi-sliders me-2"></i> การตั้งค่าอุปกรณ์
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- สถิติการใช้งาน -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-primary-subtle border-primary-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-devices text-primary fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['total_devices'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Total Devices</p>
                            <small class="text-primary">
                                <i class="fas fa-arrow-up me-1"></i>{{ $statistics['new_today'] ?? 0 }} registered today
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-success-subtle border-success-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-shield-check text-success fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['trusted_devices'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Trusted Devices</p>
                            <small class="text-success">
                                <i class="fas fa-percentage me-1"></i>{{ $statistics['trusted_percentage'] ?? 0 }}% trusted
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-warning-subtle border-warning-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-clock text-warning fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['active_devices'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Active Devices</p>
                            <small class="text-warning">
                                <i class="fas fa-signal me-1"></i>Last 30 days
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-danger-subtle border-danger-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-exclamation-triangle text-danger fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['suspicious_devices'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Suspicious Devices</p>
                            <small class="text-danger">
                                <i class="fas fa-eye me-1"></i>Needs review
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-filter text-primary me-2"></i>
                                Filter & Search
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-times me-1"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" id="filterForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Search User</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="user_search" class="form-control" 
                                           placeholder="Username or email"
                                           value="{{ request('user_search') }}">
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Device Type</label>
                                <select name="device_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>Desktop</option>
                                    <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                                    <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Trust Status</label>
                                <select name="trust_status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('trust_status') == '1' ? 'selected' : '' }}>Trusted</option>
                                    <option value="0" {{ request('trust_status') == '0' ? 'selected' : '' }}>Untrusted</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Devices Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-list text-primary me-2"></i>
                            Registered Devices ({{ $devices->total() ?? 0 }} total)
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-cog me-1"></i> Bulk Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="bulkTrust()">
                                        <i class="fas fa-check me-2"></i> Trust Selected
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkUntrust()">
                                        <i class="fas fa-times me-2"></i> Untrust Selected
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkDelete()">
                                        <i class="fas fa-trash me-2"></i> Delete Selected
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($devices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th class="border-0">Device Info</th>
                                        <th class="border-0">User</th>
                                        <th class="border-0">Trust Status</th>
                                        <th class="border-0">Location</th>
                                        <th class="border-0">Last Seen</th>
                                        <th class="border-0">Risk Score</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($devices as $device)
                                    <tr>
                                        <td class="ps-4">
                                            <input type="checkbox" class="form-check-input device-checkbox" 
                                                   value="{{ $device->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    @if($device->device_type === 'mobile')
                                                        <i class="fas fa-mobile-alt text-primary"></i>
                                                    @elseif($device->device_type === 'tablet')
                                                        <i class="fas fa-tablet-alt text-primary"></i>
                                                    @else
                                                        <i class="fas fa-desktop text-primary"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $device->device_name }}</h6>
                                                    <small class="text-muted">
                                                        {{ $device->platform }} • {{ $device->browser }}
                                                    </small>
                                                    <br>
                                                    <code class="text-muted small">{{ Str::limit($device->fingerprint, 20) }}</code>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="fas fa-user text-secondary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $device->user->username }}</h6>
                                                    <small class="text-muted">{{ $device->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($device->is_trusted)
                                                    <span class="badge bg-success me-2">
                                                        <i class="fas fa-shield-check me-1"></i> Trusted
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning me-2">
                                                        <i class="fas fa-question-circle me-1"></i> Untrusted
                                                    </span>
                                                @endif
                                                @if($device->trusted_at)
                                                    <small class="text-muted">
                                                        {{ $device->trusted_at->diffForHumans() }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($device->last_ip)
                                                <div>
                                                    <code class="small">{{ $device->last_ip }}</code>
                                                    @if($device->location)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            {{ $device->location }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <small class="fw-semibold">
                                                    {{ $device->last_seen_at ? $device->last_seen_at->format('M d, H:i') : 'Never' }}
                                                </small>
                                                @if($device->last_seen_at)
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $device->last_seen_at->diffForHumans() }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $riskScore = $device->risk_score ?? 0;
                                                $riskClass = $riskScore >= 80 ? 'danger' : ($riskScore >= 50 ? 'warning' : 'success');
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                                    <div class="progress-bar bg-{{ $riskClass }}" 
                                                         style="width: {{ $riskScore }}%"></div>
                                                </div>
                                                <span class="badge bg-{{ $riskClass }}-subtle text-{{ $riskClass }}">
                                                    {{ $riskScore }}%
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($device->is_trusted)
                                                    <form action="{{ route('admin.security.devices.untrust', $device) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                                title="Untrust Device">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.security.devices.trust', $device) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                title="Trust Device">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewDeviceDetails('{{ $device->id }}')"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="showDeviceActivity('{{ $device->id }}')"
                                                        title="View Activity">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                                <form action="{{ route('admin.security.devices.destroy', $device) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Delete this device registration?')"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($devices->hasPages())
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            Showing {{ $devices->firstItem() }} to {{ $devices->lastItem() }} 
                                            of {{ $devices->total() }} results
                                        </small>
                                    </div>
                                    <div>
                                        {{ $devices->links() }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-mobile-alt text-primary fs-2"></i>
                            </div>
                            <h5 class="text-dark fw-bold mb-2">No Devices Registered</h5>
                            <p class="text-muted mb-4">No user devices have been registered yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Device Details Modal -->
<div class="modal fade" id="deviceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-mobile-alt text-primary me-2"></i>
                    Device Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="deviceDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Device Activity Modal -->
<div class="modal fade" id="deviceActivityModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history text-primary me-2"></i>
                    Device Activity History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="deviceActivityContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Device Settings Modal -->
<div class="modal fade" id="deviceSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cog text-primary me-2"></i>
                    Device Management Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.security.devices.settings') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Auto-cleanup old devices (days)</label>
                        <input type="number" name="cleanup_days" class="form-control" 
                               value="90" min="1" max="365">
                        <div class="form-text">
                            Devices not seen for this many days will be automatically removed
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Risk score threshold</label>
                        <input type="range" name="risk_threshold" class="form-range" 
                               min="0" max="100" value="70" id="riskThreshold">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Low Risk</small>
                            <small class="text-muted" id="riskValue">70%</small>
                            <small class="text-muted">High Risk</small>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="auto_trust_known_devices" 
                               class="form-check-input" id="autoTrust" checked>
                        <label class="form-check-label" for="autoTrust">
                            Auto-trust devices from trusted users
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </div>
            </form>
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

.border-danger-subtle {
    border-color: rgba(220, 53, 69, 0.2) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const deviceCheckboxes = document.querySelectorAll('.device-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        deviceCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Risk threshold slider
    const riskThreshold = document.getElementById('riskThreshold');
    const riskValue = document.getElementById('riskValue');
    
    if (riskThreshold && riskValue) {
        riskThreshold.addEventListener('input', function() {
            riskValue.textContent = this.value + '%';
        });
    }
});

function refreshStats() {
    location.reload();
}

function clearFilters() {
    const form = document.getElementById('filterForm');
    form.reset();
    form.submit();
}

function viewDeviceDetails(deviceId) {
    const modal = new bootstrap.Modal(document.getElementById('deviceDetailsModal'));
    const content = document.getElementById('deviceDetailsContent');
    
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.show();
    
    fetch(`{{ route('admin.security.devices.show', ':id') }}`.replace(':id', deviceId))
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error loading device details</div>';
        });
}

function showDeviceActivity(deviceId) {
    const modal = new bootstrap.Modal(document.getElementById('deviceActivityModal'));
    const content = document.getElementById('deviceActivityContent');
    
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.show();
    
    fetch(`{{ route('admin.security.devices.activity', ':id') }}`.replace(':id', deviceId))
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error loading device activity</div>';
        });
}

function showDeviceSettings() {
    const modal = new bootstrap.Modal(document.getElementById('deviceSettingsModal'));
    modal.show();
}

function exportDevices(format) {
    window.open(`{{ route('admin.security.devices.export') }}?format=${format}`, '_blank');
}

function getSelectedDevices() {
    const checkboxes = document.querySelectorAll('.device-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function bulkTrust() {
    const selected = getSelectedDevices();
    if (selected.length === 0) {
        alert('Please select devices to trust');
        return;
    }
    
    if (confirm(`Trust ${selected.length} selected devices?`)) {
        fetch('{{ route("admin.security.devices.bulk-trust") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ device_ids: selected })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`${data.count} devices trusted successfully`);
                location.reload();
            } else {
                alert('Failed to trust devices');
            }
        });
    }
}

function bulkUntrust() {
    const selected = getSelectedDevices();
    if (selected.length === 0) {
        alert('Please select devices to untrust');
        return;
    }
    
    if (confirm(`Untrust ${selected.length} selected devices?`)) {
        fetch('{{ route("admin.security.devices.bulk-untrust") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ device_ids: selected })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`${data.count} devices untrusted successfully`);
                location.reload();
            } else {
                alert('Failed to untrust devices');
            }
        });
    }
}

function bulkDelete() {
    const selected = getSelectedDevices();
    if (selected.length === 0) {
        alert('Please select devices to delete');
        return;
    }
    
    if (confirm(`Delete ${selected.length} selected devices? This action cannot be undone.`)) {
        fetch('{{ route("admin.security.devices.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ device_ids: selected })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`${data.count} devices deleted successfully`);
                location.reload();
            } else {
                alert('Failed to delete devices');
            }
        });
    }
}

function cleanupOldDevices() {
    if (confirm('Are you sure you want to cleanup old devices? This will remove devices not seen for over 90 days.')) {
        fetch('{{ route("admin.security.devices.cleanup") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Cleanup completed: ${data.count} old devices removed`);
                location.reload();
            } else {
                alert('Cleanup failed');
            }
        });
    }
}

function revokeAllDevices() {
    if (confirm('Are you sure you want to revoke trust for ALL untrusted devices?')) {
        alert('Feature will be implemented');
    }
}
</script>
@endsection
