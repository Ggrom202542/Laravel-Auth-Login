@extends('layouts.dashboard')

@section('title', 'Real-time Sessions - Super Admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1">Real-time Session Monitor</h3>
                    <p class="text-muted mb-0">ตรวจสอบและติดตาม Sessions แบบ Real-time</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('super-admin.sessions.index') }}" class="btn btn-primary">
                        <i class="bi bi-list-ul me-1"></i>All Sessions
                    </a>
                    <a href="{{ route('super-admin.sessions.dashboard') }}" class="btn btn-success">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                    <button type="button" class="btn btn-info" id="refreshBtn">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                    <button type="button" class="btn btn-warning" id="toggleAutoRefresh">
                        <i class="bi bi-play-circle me-1"></i>Auto Refresh
                    </button>
                </div>
            </div>

            <!-- Real-time Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-wifi fs-1 mb-2"></i>
                            <h4 id="activeSessions">0</h4>
                            <small>Active Sessions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-people fs-1 mb-2"></i>
                            <h4 id="onlineUsers">0</h4>
                            <small>Online Users</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-clock fs-1 mb-2"></i>
                            <h4 id="avgDuration">0m</h4>
                            <small>Avg Duration</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                            <h4 id="suspiciousSessions">0</h4>
                            <small>Suspicious</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Control Panel -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-sliders me-2"></i>Monitor Controls
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="refreshInterval" class="form-label">Refresh Interval</label>
                            <select class="form-select" id="refreshInterval">
                                <option value="5">5 seconds</option>
                                <option value="10" selected>10 seconds</option>
                                <option value="30">30 seconds</option>
                                <option value="60">1 minute</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterRole" class="form-label">Filter by Role</label>
                            <select class="form-select" id="filterRole">
                                <option value="">All Roles</option>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label">Filter by Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="online">Online</option>
                                <option value="active">Active</option>
                                <option value="suspicious">Suspicious</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="soundAlerts">
                                <label class="form-check-label" for="soundAlerts">
                                    Sound Alerts
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="row">
                <div class="col-lg-8">
                    <!-- Live Sessions Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-broadcast me-2"></i>Live Sessions
                                <span class="badge bg-primary ms-2" id="sessionCount">0</span>
                            </h6>
                            <div class="text-muted small">
                                Last updated: <span id="lastUpdate">-</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="liveSessionsTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>User</th>
                                            <th>Device</th>
                                            <th>Location</th>
                                            <th>Activity</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th width="100">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sessionsTableBody">
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="text-muted mt-2">Loading sessions...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Recent Activities -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>Recent Activities
                            </h6>
                        </div>
                        <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                            <div id="recentActivities">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2">Loading activities...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts Panel -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bell me-2"></i>Security Alerts
                                <span class="badge bg-danger ms-2" id="alertCount">0</span>
                            </h6>
                        </div>
                        <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                            <div id="securityAlerts">
                                <div class="text-center py-3">
                                    <i class="bi bi-shield-check text-success fs-2"></i>
                                    <p class="text-muted mt-2 mb-0">No alerts at the moment</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Session Detail Modal -->
<div class="modal fade" id="sessionDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sessionDetailContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="terminateSessionBtn">
                    <i class="bi bi-x-circle me-1"></i>Terminate Session
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let autoRefreshInterval;
let isAutoRefreshActive = false;
let currentRefreshInterval = 10; // seconds
let sessionDetailModal;

document.addEventListener('DOMContentLoaded', function() {
    sessionDetailModal = new bootstrap.Modal(document.getElementById('sessionDetailModal'));
    
    // Initial load
    loadRealtimeData();
    
    // Event listeners
    document.getElementById('refreshBtn').addEventListener('click', loadRealtimeData);
    document.getElementById('toggleAutoRefresh').addEventListener('click', toggleAutoRefresh);
    document.getElementById('refreshInterval').addEventListener('change', updateRefreshInterval);
    document.getElementById('filterRole').addEventListener('change', loadRealtimeData);
    document.getElementById('filterStatus').addEventListener('change', loadRealtimeData);
});

function loadRealtimeData() {
    const role = document.getElementById('filterRole').value;
    const status = document.getElementById('filterStatus').value;
    
    const params = new URLSearchParams();
    if (role) params.append('role', role);
    if (status) params.append('status', status);
    
    fetch(`/super-admin/sessions/realtime-data?${params.toString()}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        updateStats(data.stats);
        updateSessionsTable(data.sessions);
        updateRecentActivities(data.activities);
        updateSecurityAlerts(data.alerts);
        updateLastUpdate();
    })
    .catch(error => {
        console.error('Error loading realtime data:', error);
        showError('Failed to load realtime data');
    });
}

function updateStats(stats) {
    document.getElementById('activeSessions').textContent = stats.active_sessions || 0;
    document.getElementById('onlineUsers').textContent = stats.online_users || 0;
    document.getElementById('avgDuration').textContent = (stats.avg_duration || 0) + 'm';
    document.getElementById('suspiciousSessions').textContent = stats.suspicious_sessions || 0;
}

function updateSessionsTable(sessions) {
    const tbody = document.getElementById('sessionsTableBody');
    const sessionCount = document.getElementById('sessionCount');
    
    sessionCount.textContent = sessions.length;
    
    if (sessions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                    <p class="text-muted">No active sessions found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = sessions.map(session => `
        <tr class="${session.is_suspicious ? 'table-warning' : ''}">
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        ${getRoleBadge(session.user.role)}
                    </div>
                    <div>
                        <strong>${session.user.username}</strong>
                        <br>
                        <small class="text-muted">${session.user.email}</small>
                    </div>
                </div>
            </td>
            <td>
                <i class="bi ${getDeviceIcon(session.device_info)} me-1"></i>
                ${session.device_name || 'Unknown'}
                <br>
                <small class="text-muted">${session.platform || ''} ${session.browser || ''}</small>
            </td>
            <td>
                <span class="font-monospace">${session.ip_address}</span>
                <br>
                <small class="text-muted">
                    <i class="bi bi-geo-alt"></i> ${session.location_country || 'Unknown'}
                </small>
            </td>
            <td>
                <span title="${session.last_activity}">
                    ${formatTimeAgo(session.last_activity)}
                </span>
            </td>
            <td>
                ${formatDuration(session.duration_minutes)}
            </td>
            <td>
                <div class="d-flex flex-column gap-1">
                    ${getStatusBadge(session)}
                </div>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" 
                            onclick="viewSessionDetails('${session.session_id}')" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    ${session.is_active ? `
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="terminateSession('${session.session_id}')" title="Terminate">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function updateRecentActivities(activities) {
    const container = document.getElementById('recentActivities');
    
    if (activities.length === 0) {
        container.innerHTML = `
            <div class="text-center py-3">
                <i class="bi bi-clock-history text-muted fs-2"></i>
                <p class="text-muted mt-2 mb-0">No recent activities</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = activities.map(activity => `
        <div class="border-bottom px-3 py-2">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <i class="bi ${getActivityIcon(activity.action)} text-${getActivityColor(activity.action)}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="small">
                        <strong>${activity.user_name}</strong> ${activity.description}
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-clock me-1"></i>${formatTimeAgo(activity.created_at)}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function updateSecurityAlerts(alerts) {
    const container = document.getElementById('securityAlerts');
    const alertCount = document.getElementById('alertCount');
    
    alertCount.textContent = alerts.length;
    
    if (alerts.length === 0) {
        container.innerHTML = `
            <div class="text-center py-3">
                <i class="bi bi-shield-check text-success fs-2"></i>
                <p class="text-muted mt-2 mb-0">No alerts at the moment</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = alerts.map(alert => `
        <div class="border-bottom px-3 py-2">
            <div class="d-flex align-items-start">
                <div class="me-2">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="small">
                        <strong>${alert.title}</strong>
                    </div>
                    <div class="text-muted small">${alert.description}</div>
                    <div class="text-muted small">
                        <i class="bi bi-clock me-1"></i>${formatTimeAgo(alert.created_at)}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    // Play sound alert if enabled
    if (alerts.length > 0 && document.getElementById('soundAlerts').checked) {
        playAlertSound();
    }
}

function toggleAutoRefresh() {
    const btn = document.getElementById('toggleAutoRefresh');
    const icon = btn.querySelector('i');
    
    if (isAutoRefreshActive) {
        clearInterval(autoRefreshInterval);
        isAutoRefreshActive = false;
        btn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Auto Refresh';
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-warning');
    } else {
        startAutoRefresh();
        isAutoRefreshActive = true;
        btn.innerHTML = '<i class="bi bi-stop-circle me-1"></i>Stop Auto';
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-danger');
    }
}

function startAutoRefresh() {
    autoRefreshInterval = setInterval(loadRealtimeData, currentRefreshInterval * 1000);
}

function updateRefreshInterval() {
    currentRefreshInterval = parseInt(document.getElementById('refreshInterval').value);
    
    if (isAutoRefreshActive) {
        clearInterval(autoRefreshInterval);
        startAutoRefresh();
    }
}

function updateLastUpdate() {
    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
}

// Helper functions
function getRoleBadge(role) {
    const badges = {
        'super_admin': '<span class="badge bg-danger">SA</span>',
        'admin': '<span class="badge bg-warning">A</span>',
        'user': '<span class="badge bg-primary">U</span>'
    };
    return badges[role] || '<span class="badge bg-secondary">?</span>';
}

function getDeviceIcon(deviceInfo) {
    if (deviceInfo.includes('mobile')) return 'bi-phone';
    if (deviceInfo.includes('tablet')) return 'bi-tablet';
    return 'bi-pc-display';
}

function getStatusBadge(session) {
    let badges = [];
    
    if (session.is_active) {
        if (session.is_online) {
            badges.push('<span class="badge bg-success">Online</span>');
        } else {
            badges.push('<span class="badge bg-warning">Active</span>');
        }
    } else {
        badges.push('<span class="badge bg-secondary">Closed</span>');
    }
    
    if (session.is_suspicious) {
        badges.push('<span class="badge bg-danger">Suspicious</span>');
    }
    
    if (session.is_current) {
        badges.push('<span class="badge bg-info">Current</span>');
    }
    
    return badges.join(' ');
}

function getActivityIcon(action) {
    const icons = {
        'login': 'bi-box-arrow-in-right',
        'logout': 'bi-box-arrow-left',
        'session_created': 'bi-plus-circle',
        'session_terminated': 'bi-x-circle',
        'suspicious_activity': 'bi-exclamation-triangle'
    };
    return icons[action] || 'bi-circle';
}

function getActivityColor(action) {
    const colors = {
        'login': 'success',
        'logout': 'secondary',
        'session_created': 'primary',
        'session_terminated': 'danger',
        'suspicious_activity': 'warning'
    };
    return colors[action] || 'muted';
}

function formatTimeAgo(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diffMs = now - time;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h ago`;
    
    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays}d ago`;
}

function formatDuration(minutes) {
    if (minutes < 60) return `${minutes}m`;
    const hours = Math.floor(minutes / 60);
    const remainingMins = minutes % 60;
    return `${hours}h ${remainingMins}m`;
}

function viewSessionDetails(sessionId) {
    // Implementation for viewing session details
    console.log('View session details:', sessionId);
}

function terminateSession(sessionId) {
    if (confirm('Are you sure you want to terminate this session?')) {
        fetch(`/super-admin/sessions/${sessionId}/terminate`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadRealtimeData(); // Refresh data
            } else {
                alert('Error terminating session');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred');
        });
    }
}

function playAlertSound() {
    // Simple beep sound using Web Audio API
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.3, audioContext.currentTime + 0.1);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.5);
}

function showError(message) {
    // Simple error notification
    const toast = document.createElement('div');
    toast.className = 'toast show position-fixed top-0 end-0 m-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="toast-header bg-danger text-white">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong class="me-auto">Error</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">${message}</div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
@endpush

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.font-monospace {
    font-family: 'Courier New', monospace;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.btn-group-sm .btn {
    font-size: 0.75rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

#recentActivities::-webkit-scrollbar,
#securityAlerts::-webkit-scrollbar {
    width: 6px;
}

#recentActivities::-webkit-scrollbar-track,
#securityAlerts::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#recentActivities::-webkit-scrollbar-thumb,
#securityAlerts::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

#recentActivities::-webkit-scrollbar-thumb:hover,
#securityAlerts::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endpush
