@extends('layouts.dashboard')

@section('title', 'Session Details - ' . $user->username)

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Session Details - {{ $user->username }}</h4>
                    <p class="text-muted mb-0">{{ $user->email }} - Role: {{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
                </div>
                <div>
                    <a href="{{ route('super-admin.sessions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to All Sessions
                    </a>
                    <button type="button" class="btn btn-danger" onclick="forceLogoutAll()">
                        <i class="bi bi-slash-circle me-1"></i>Force Logout All
                    </button>
                </div>
            </div>

            <!-- User Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-pc-display fs-1 mb-2"></i>
                            <h5>{{ $statistics['total_sessions'] }}</h5>
                            <small>Total Sessions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-wifi fs-1 mb-2"></i>
                            <h5>{{ $statistics['online_sessions'] }}</h5>
                            <small>Online Sessions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-shield-check fs-1 mb-2"></i>
                            <h5>{{ $statistics['trusted_devices'] }}</h5>
                            <small>Trusted Devices</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                            <h5>{{ $statistics['suspicious_sessions'] }}</h5>
                            <small>Suspicious Sessions</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="sessionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" 
                                    data-bs-target="#active" type="button" role="tab">
                                <i class="bi bi-pc-display me-1"></i>Active Sessions ({{ $activeSessions->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" 
                                    data-bs-target="#history" type="button" role="tab">
                                <i class="bi bi-clock-history me-1"></i>Session History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" 
                                    data-bs-target="#logs" type="button" role="tab">
                                <i class="bi bi-list-ul me-1"></i>Activity Logs
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" 
                                    data-bs-target="#security" type="button" role="tab">
                                <i class="bi bi-shield-exclamation me-1"></i>Security Events
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="sessionTabContent">
                        <!-- Active Sessions Tab -->
                        <div class="tab-pane fade show active" id="active" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Device & Browser</th>
                                            <th>Location</th>
                                            <th>IP Address</th>
                                            <th>Login Time</th>
                                            <th>Last Activity</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activeSessions as $session)
                                        <tr class="{{ $session->is_suspicious ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi {{ $session->getDeviceIcon() }} me-2 fs-4"></i>
                                                    <div>
                                                        <strong>{{ $session->device_name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $session->platform }} - {{ $session->browser }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($session->location_country)
                                                    <i class="bi bi-geo-alt me-1"></i>{{ $session->location_country }}
                                                    @if($session->location_city)
                                                        <br><small class="text-muted">{{ $session->location_city }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Unknown</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="font-monospace">{{ $session->ip_address }}</span>
                                            </td>
                                            <td>
                                                <span title="{{ $session->login_at->format('d/m/Y H:i:s') }}">
                                                    {{ $session->login_at->diffForHumans() }}
                                                </span>
                                            </td>
                                            <td>
                                                <span title="{{ $session->last_activity->format('d/m/Y H:i:s') }}">
                                                    {{ $session->last_activity->diffForHumans() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    @if($session->isOnline())
                                                        <span class="badge bg-success">Online</span>
                                                    @else
                                                        <span class="badge bg-warning">Idle</span>
                                                    @endif
                                                    
                                                    @if($session->is_trusted)
                                                        <span class="badge bg-success">Trusted</span>
                                                    @else
                                                        <span class="badge bg-warning">Untrusted</span>
                                                    @endif
                                                    
                                                    @if($session->is_suspicious)
                                                        <span class="badge bg-danger">Suspicious</span>
                                                    @endif
                                                    
                                                    @if($session->is_current)
                                                        <span class="badge bg-info">Current</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="terminateSession('{{ $session->session_id }}')">
                                                        <i class="bi bi-x-circle"></i> Terminate
                                                    </button>
                                                    @if(!$session->is_trusted)
                                                        <button type="button" class="btn btn-outline-success" 
                                                                onclick="trustDevice('{{ $session->session_id }}')">
                                                            <i class="bi bi-shield-check"></i> Trust
                                                        </button>
                                                    @endif
                                                    @if(!$session->is_suspicious)
                                                        <button type="button" class="btn btn-outline-warning" 
                                                                onclick="markSuspicious('{{ $session->session_id }}')">
                                                            <i class="bi bi-exclamation-triangle"></i> Flag
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-pc-display display-4 text-muted mb-3"></i>
                                                <p class="text-muted">No active sessions</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Session History Tab -->
                        <div class="tab-pane fade" id="history" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Device</th>
                                            <th>IP Address</th>
                                            <th>Login Time</th>
                                            <th>Logout Time</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sessionHistory as $session)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi {{ $session->getDeviceIcon() }} me-2"></i>
                                                    <div>
                                                        <span>{{ $session->device_name }}</span>
                                                        <br>
                                                        <small class="text-muted">{{ $session->platform }} - {{ $session->browser }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="font-monospace">{{ $session->ip_address }}</span>
                                            </td>
                                            <td>
                                                {{ $session->login_at->format('d/m/Y H:i:s') }}
                                            </td>
                                            <td>
                                                @if($session->logout_at)
                                                    {{ $session->logout_at->format('d/m/Y H:i:s') }}
                                                @else
                                                    <span class="text-muted">Still active</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($session->logout_at)
                                                    {{ $session->logout_at->diffInMinutes($session->login_at) }} min
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($session->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Closed</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="bi bi-clock-history display-4 text-muted mb-3"></i>
                                                <p class="text-muted">No session history</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Activity Logs Tab -->
                        <div class="tab-pane fade" id="logs" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Time</th>
                                            <th>Action</th>
                                            <th>IP Address</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sessionLogs as $log)
                                        <tr>
                                            <td>
                                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                                            </td>
                                            <td>
                                                <span class="badge {{ $log->getActionBadgeClass() }}">
                                                    {{ $log->getActionLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-monospace">{{ $log->ip_address }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $log->details ?? $log->reason ?? '-' }}</small>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i class="bi bi-list-ul display-4 text-muted mb-3"></i>
                                                <p class="text-muted">No activity logs</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Security Events Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Security events and suspicious activities for this user
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Time</th>
                                            <th>Event Type</th>
                                            <th>IP Address</th>
                                            <th>Description</th>
                                            <th>Risk Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $securityEvents = $sessionLogs->where('action', 'suspicious_activity')
                                                                          ->merge($activeSessions->where('is_suspicious', true))
                                        @endphp
                                        @forelse($securityEvents as $event)
                                        <tr>
                                            <td>
                                                {{ $event->created_at ? $event->created_at->format('d/m/Y H:i:s') : $event->suspicious_detected_at->format('d/m/Y H:i:s') }}
                                            </td>
                                            <td>
                                                @if(isset($event->action))
                                                    <span class="badge bg-danger">{{ $event->getActionLabel() }}</span>
                                                @else
                                                    <span class="badge bg-warning">Suspicious Session</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="font-monospace">{{ $event->ip_address }}</span>
                                            </td>
                                            <td>
                                                {{ $event->reason ?? $event->suspicious_reason ?? 'Suspicious activity detected' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">High</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="bi bi-shield-check display-4 text-success mb-3"></i>
                                                <p class="text-muted">No security events detected</p>
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
    </div>
</div>

<!-- Force Logout All Modal -->
<div class="modal fade" id="forceLogoutAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Force Logout All Sessions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This will immediately logout the user from all devices and sessions.
                </div>
                <form id="forceLogoutAllForm">
                    @csrf
                    <div class="mb-3">
                        <label for="logout_reason" class="form-label">Reason for Force Logout</label>
                        <textarea class="form-control" id="logout_reason" name="reason" rows="3" required
                                  placeholder="Specify reason for forcing logout..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitForceLogoutAll()">
                    <i class="bi bi-slash-circle me-1"></i>Force Logout All
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let forceLogoutAllModal;

document.addEventListener('DOMContentLoaded', function() {
    forceLogoutAllModal = new bootstrap.Modal(document.getElementById('forceLogoutAllModal'));
});

function forceLogoutAll() {
    forceLogoutAllModal.show();
}

function submitForceLogoutAll() {
    const form = document.getElementById('forceLogoutAllForm');
    const formData = new FormData(form);
    
    fetch('{{ route("super-admin.sessions.force-logout", $user) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            forceLogoutAllModal.hide();
            alert(data.message || 'User has been logged out from all sessions');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error occurred while forcing logout');
    });
}

function terminateSession(sessionId) {
    if (confirm('Are you sure you want to terminate this session?')) {
        fetch('{{ route("super-admin.sessions.terminate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                session_id: sessionId,
                reason: 'Terminated by Super Admin'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
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

function trustDevice(sessionId) {
    fetch('{{ route("super-admin.sessions.trust-device") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            session_id: sessionId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error trusting device');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error occurred');
    });
}

function markSuspicious(sessionId) {
    const reason = prompt('Enter reason for marking as suspicious:');
    if (reason) {
        fetch('{{ route("super-admin.sessions.mark-suspicious") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                session_id: sessionId,
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error marking session as suspicious');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred');
        });
    }
}

// Auto refresh every 30 seconds
setInterval(function() {
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
</script>
@endpush

@push('styles')
<style>
.font-monospace {
    font-family: 'Courier New', monospace;
}

.badge {
    font-size: 0.75rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn-group-sm .btn {
    font-size: 0.75rem;
}

.table td {
    vertical-align: middle;
}

.nav-tabs .nav-link {
    color: #495057;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    font-weight: 600;
}
</style>
@endpush
