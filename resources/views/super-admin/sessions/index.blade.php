@extends('layouts.dashboard')

@section('title', 'All Sessions - Super Admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1">All Sessions Management</h3>
                    <p class="text-muted mb-0">จัดการและตรวจสอบ Sessions ทั้งระบบ</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('super-admin.sessions.dashboard') }}" class="btn btn-success">
                        <i class="bi bi-graph-up me-1"></i>Dashboard
                    </a>
                    <a href="{{ route('super-admin.sessions.realtime') }}" class="btn btn-info">
                        <i class="bi bi-broadcast me-1"></i>Real-time
                    </a>
                    <button type="button" class="btn btn-warning" onclick="bulkCleanup()">
                        <i class="bi bi-trash me-1"></i>Cleanup
                    </button>
                </div>
            </div>

            <!-- System Stats Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-server fs-1 mb-2"></i>
                            <h4>{{ number_format($systemStats['total_sessions']) }}</h4>
                            <small>Total Sessions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-wifi fs-1 mb-2"></i>
                            <h4>{{ number_format($systemStats['active_sessions']) }}</h4>
                            <small>Active Sessions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-people fs-1 mb-2"></i>
                            <h4>{{ number_format($systemStats['online_users']) }}</h4>
                            <small>Online Users</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                            <h4>{{ number_format($systemStats['suspicious_sessions'] ?? 0) }}</h4>
                            <small>Suspicious</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ $search }}" placeholder="Username, Email, IP...">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspicious" {{ $status === 'suspicious' ? 'selected' : '' }}>Suspicious</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="role" class="form-label">User Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="">All Roles</option>
                                <option value="user" {{ $role === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="super_admin" {{ $role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="days" class="form-label">Period</label>
                            <select class="form-select" id="days" name="days">
                                <option value="1" {{ $days == 1 ? 'selected' : '' }}>Last 24h</option>
                                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <a href="{{ route('super-admin.sessions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sessions Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Sessions List ({{ $sessions->total() }} results)</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-danger" onclick="bulkTerminate()">
                            <i class="bi bi-x-circle me-1"></i>Bulk Terminate
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="exportData()">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>User</th>
                                    <th>Device & Location</th>
                                    <th>IP Address</th>
                                    <th>Activity</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                <tr class="{{ $session->is_suspicious ? 'table-warning' : '' }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input session-checkbox" 
                                               value="{{ $session->session_id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                @if($session->user->role === 'super_admin')
                                                    <span class="badge bg-danger">SA</span>
                                                @elseif($session->user->role === 'admin')
                                                    <span class="badge bg-warning">A</span>
                                                @else
                                                    <span class="badge bg-primary">U</span>
                                                @endif
                                            </div>
                                            <div>
                                                <strong>{{ $session->user->username }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $session->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi {{ $session->getDeviceIcon() }} me-2"></i>
                                            <div>
                                                <span>{{ $session->device_name }}</span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $session->platform }} - {{ $session->browser }}
                                                    @if($session->location_country)
                                                        <br><i class="bi bi-geo-alt"></i> {{ $session->location_country }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-monospace">{{ $session->ip_address }}</span>
                                        @if($session->is_trusted)
                                            <br><span class="badge bg-success">Trusted</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Login:</strong> {{ $session->login_at->format('d/m/Y H:i') }}
                                            <br>
                                            <strong>Last:</strong> 
                                            <span title="{{ $session->last_activity->format('d/m/Y H:i:s') }}">
                                                {{ $session->last_activity->diffForHumans() }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->logout_at)
                                            {{ $session->login_at->diffInMinutes($session->logout_at) }} min
                                        @else
                                            {{ $session->login_at->diffInMinutes($session->last_activity) }} min
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($session->is_active)
                                                @if($session->isOnline())
                                                    <span class="badge bg-success">Online</span>
                                                @else
                                                    <span class="badge bg-warning">Active</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Closed</span>
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
                                            <a href="{{ route('super-admin.sessions.show', $session) }}" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($session->is_active)
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="terminateSession('{{ $session->session_id }}')" title="Terminate">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
                                            @if(!$session->is_trusted)
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="trustDevice('{{ $session->session_id }}')" title="Trust Device">
                                                    <i class="bi bi-shield-check"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                                        <p class="text-muted">No sessions found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($sessions->hasPages())
                <div class="card-footer">
                    {{ $sessions->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkModalTitle">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkActionsForm">
                    @csrf
                    <input type="hidden" id="bulk_action" name="action">
                    <input type="hidden" id="session_ids" name="session_ids">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" 
                                  placeholder="ระบุเหตุผลในการดำเนินการ..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="bulkWarningText">This action cannot be undone.</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitBulkAction()">
                    <span id="bulkSubmitText">Execute</span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let bulkModal;

document.addEventListener('DOMContentLoaded', function() {
    bulkModal = new bootstrap.Modal(document.getElementById('bulkActionsModal'));
    
    // Select All functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.session-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
});

function getSelectedSessions() {
    const checkboxes = document.querySelectorAll('.session-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function bulkTerminate() {
    const selected = getSelectedSessions();
    if (selected.length === 0) {
        alert('Please select sessions to terminate');
        return;
    }
    
    document.getElementById('bulkModalTitle').textContent = 'Terminate Sessions';
    document.getElementById('bulk_action').value = 'terminate';
    document.getElementById('session_ids').value = JSON.stringify(selected);
    document.getElementById('bulkWarningText').textContent = `You are about to terminate ${selected.length} session(s).`;
    document.getElementById('bulkSubmitText').textContent = 'Terminate Sessions';
    
    bulkModal.show();
}

function bulkCleanup() {
    if (confirm('This will cleanup all expired sessions. Continue?')) {
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
                alert(data.message || 'Cleanup completed successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred during cleanup');
        });
    }
}

function terminateSession(sessionId) {
    if (confirm('Are you sure you want to terminate this session?')) {
        fetch(`/super-admin/sessions/${sessionId}/terminate`, {
            method: 'DELETE',
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

function submitBulkAction() {
    const form = document.getElementById('bulkActionsForm');
    const formData = new FormData(form);
    
    const data = {
        action: formData.get('action'),
        session_ids: JSON.parse(formData.get('session_ids')),
        reason: formData.get('reason')
    };

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
            bulkModal.hide();
            alert(data.message || 'Action completed successfully');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error occurred');
    });
}

function exportData() {
    const params = new URLSearchParams(window.location.search);
    params.set('format', 'csv');
    window.open('{{ route("super-admin.sessions.advanced-export") }}?' + params.toString(), '_blank');
}

// Auto-refresh every 30 seconds
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
</style>
@endpush
