@extends('layouts.admin')

@section('title', 'Security Overview')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-shield-alt text-primary me-2"></i>
                    Security Overview
                </h1>
                <p class="text-muted">System security monitoring and statistics</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Locked Accounts -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Locked Accounts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['total_locked'] }}
                            </div>
                            <div class="text-xs text-muted">
                                {{ $statistics['locked_today'] }} locked today
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-lock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspicious Logins -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Suspicious Activity
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['suspicious_logins_today'] }}
                            </div>
                            <div class="text-xs text-muted">
                                Today's detections
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IP Restrictions -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                IP Restrictions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['active_ip_restrictions'] }}
                            </div>
                            <div class="text-xs text-muted">
                                {{ $statistics['blocked_ips'] }} blocked IPs
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed Attempts -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                High Risk Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $statistics['high_failed_attempts'] }}
                            </div>
                            <div class="text-xs text-muted">
                                Users with 3+ failed attempts
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Locked Accounts -->
    <div class="row">
        <!-- Recent Suspicious Activity -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Recent Suspicious Activity
                    </h6>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>IP Address</th>
                                        <th>Risk Score</th>
                                        <th>Reason</th>
                                        <th>Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentActivity as $activity)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <span class="badge bg-secondary">
                                                            {{ strtoupper(substr($activity->user->username ?? 'Unknown', 0, 2)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $activity->user->username ?? 'Unknown' }}</div>
                                                        <small class="text-muted">{{ $activity->user->email ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $activity->ip_address }}</code>
                                            </td>
                                            <td>
                                                @php
                                                    $riskColor = $activity->risk_score >= 80 ? 'danger' : 
                                                                ($activity->risk_score >= 60 ? 'warning' : 'info');
                                                @endphp
                                                <span class="badge bg-{{ $riskColor }}">
                                                    {{ $activity->risk_score }}%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $activity->reason }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.security.suspicious.show', $activity) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($activity->status === 'pending')
                                                        <button type="button" class="btn btn-outline-success btn-sm"
                                                                onclick="markAsResolved({{ $activity->id }})">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">No suspicious activity detected</h5>
                            <p class="text-muted">All login attempts appear to be legitimate.</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.security.suspicious.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-list me-1"></i>
                        View All Suspicious Activity
                    </a>
                </div>
            </div>
        </div>

        <!-- Locked Accounts -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-lock me-2"></i>
                        Locked Accounts
                    </h6>
                </div>
                <div class="card-body">
                    @if($lockedAccounts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($lockedAccounts->take(5) as $account)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <div class="fw-bold">{{ $account->username }}</div>
                                        <small class="text-muted">
                                            Locked {{ $account->locked_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" 
                                                   href="{{ route('admin.security.user-details', $account) }}">
                                                    <i class="fas fa-eye me-2"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.security.unlock', $account) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="fas fa-unlock me-2"></i>Unlock Account
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($lockedAccounts->count() > 5)
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    ... and {{ $lockedAccounts->count() - 5 }} more
                                </small>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-unlock fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">No locked accounts</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-lock me-1"></i>
                        Manage All Locks
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.security.ip.index') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-globe me-2"></i>
                                Manage IP Restrictions
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.security.devices.index') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-mobile-alt me-2"></i>
                                Device Management
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.security.suspicious.index') }}" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Suspicious Activity
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.security.index') }}" class="btn btn-outline-danger btn-block">
                                <i class="fas fa-user-lock me-2"></i>
                                Account Lockouts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAsResolved(activityId) {
    if (confirm('Mark this suspicious activity as resolved?')) {
        fetch(`/admin/security/suspicious/${activityId}/resolve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the request.');
        });
    }
}

// Auto refresh every 30 seconds
setInterval(function() {
    window.location.reload();
}, 30000);
</script>
@endpush
@endsection
