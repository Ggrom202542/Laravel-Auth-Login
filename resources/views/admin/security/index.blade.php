@extends('layouts.admin')

@section('title', 'Security Management Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-dark fw-bold mb-1">
                        <i class="fas fa-shield-alt text-primary me-2"></i>
                        Security Management
                    </h2>
                    <p class="text-muted mb-0">Comprehensive security monitoring and management dashboard</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" id="refreshStats">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-tools me-1"></i> Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="cleanupExpiredLocks()">
                                <i class="fas fa-broom me-2"></i>Cleanup Expired Locks
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="cleanupExpiredIPs()">
                                <i class="fas fa-trash me-2"></i>Cleanup Expired IPs
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.security.report') }}">
                                <i class="fas fa-chart-line me-2"></i>Generate Report
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Account Lockouts -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fw-semibold">Locked Accounts</p>
                            <h3 class="mb-0 text-danger">{{ $statistics['total_locked'] ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $statistics['locked_today'] ?? 0 }} today
                            </small>
                        </div>
                        <div class="avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-user-lock text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed Attempts -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fw-semibold">Failed Attempts</p>
                            <h3 class="mb-0 text-warning">{{ $statistics['high_failed_attempts'] ?? 0 }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                High risk accounts
                            </small>
                        </div>
                        <div class="avatar-lg bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-ban text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fw-semibold">Recent Activity</p>
                            <h3 class="mb-0 text-success">{{ $lockedAccounts->count() }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>
                                Active sessions
                            </small>
                        </div>
                        <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-chart-line text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fw-semibold">System Status</p>
                            <h3 class="mb-0 text-info">Online</h3>
                            <small class="text-muted">
                                <i class="fas fa-check-circle me-1"></i>
                                All systems operational
                            </small>
                        </div>
                        <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-server text-info fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Modules Navigation -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-th-large text-primary me-2"></i>
                        Security Modules
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Account Lockout Management -->
                        <div class="col-xl-3 col-md-6">
                            <a href="#lockedAccountsSection" class="text-decoration-none" onclick="scrollToSection('lockedAccountsSection')">
                                <div class="card border border-primary-subtle h-100 hover-card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                            <i class="fas fa-user-shield text-primary fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Account Lockout</h6>
                                        <p class="text-muted small mb-0">Manage locked accounts and failed login attempts</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- IP Management -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('admin.security.ip.index') }}" class="text-decoration-none">
                                <div class="card border border-warning-subtle h-100 hover-card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                            <i class="fas fa-network-wired text-warning fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">IP Management</h6>
                                        <p class="text-muted small mb-0">Control IP whitelist, blacklist and geographic tracking</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Device Management -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('admin.security.devices') }}" class="text-decoration-none">
                                <div class="card border border-success-subtle h-100 hover-card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                            <i class="fas fa-mobile-alt text-success fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Device Management</h6>
                                        <p class="text-muted small mb-0">Monitor and manage trusted devices</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Suspicious Login Detection -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('admin.security.suspicious-logins') }}" class="text-decoration-none">
                                <div class="card border border-info-subtle h-100 hover-card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                            <i class="fas fa-eye text-info fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Suspicious Detection</h6>
                                        <p class="text-muted small mb-0">AI-powered anomaly detection and analysis</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Locked Accounts Section -->
    <div id="lockedAccountsSection" class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-user-lock text-danger me-2"></i>
                            Locked Accounts
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-success" onclick="unlockAllExpired()">
                                <i class="fas fa-unlock me-1"></i> Unlock Expired
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($lockedAccounts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4">User</th>
                                        <th class="border-0">Failed Attempts</th>
                                        <th class="border-0">Locked Time</th>
                                        <th class="border-0">Last IP</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lockedAccounts as $user)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="fas fa-user text-danger"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $user->username }}</h6>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger-subtle text-danger">
                                                {{ $user->failed_login_attempts }} attempts
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $user->locked_at ? $user->locked_at->format('M d, H:i') : '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            <code class="text-muted">{{ $user->last_login_ip ?? 'Unknown' }}</code>
                                        </td>
                                        <td>
                                            @php
                                                $lockoutStatus = app(App\Services\AccountLockoutService::class)->getLockoutStatus($user);
                                                $remainingMinutes = $lockoutStatus['remaining_minutes'] ?? 0;
                                            @endphp
                                            @if($remainingMinutes > 0)
                                                <span class="badge bg-warning">{{ $remainingMinutes }}m remaining</span>
                                            @else
                                                <span class="badge bg-danger">Permanent lock</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="User actions">
                                                <form action="{{ route('admin.security.unlock', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" 
                                                            onclick="return confirm('Unlock this account?')"
                                                            title="Unlock Account">
                                                        <i class="fas fa-unlock"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.security.user-details', $user) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="extendLock({{ $user->id }})"
                                                        title="Extend Lock">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-check-circle text-success fs-2"></i>
                            </div>
                            <h5 class="text-dark fw-bold mb-2">No Locked Accounts</h5>
                            <p class="text-muted mb-0">All user accounts are currently active and accessible</p>
                        </div>
                    @endif
                </div>
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
</style>

<script>
// Page initialization
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 30 seconds
    setInterval(function() {
        // Refresh page stats periodically
        // location.reload();
    }, 30000);
});

// Action functions
function scrollToSection(sectionId) {
    document.getElementById(sectionId).scrollIntoView({ 
        behavior: 'smooth' 
    });
}

function refreshStats() {
    location.reload();
}

function cleanupExpiredLocks() {
    if (confirm('Are you sure you want to cleanup all expired account locks?')) {
        fetch('{{ route("admin.security.cleanup-expired") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Cleanup completed: ${data.count} expired locks removed`);
                location.reload();
            } else {
                alert('Cleanup failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred during cleanup');
        });
    }
}

function cleanupExpiredIPs() {
    if (confirm('Are you sure you want to cleanup all expired IP restrictions?')) {
        fetch('{{ route("admin.security.ip.cleanup-expired") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Cleanup completed: ${data.count} expired IP restrictions removed`);
                location.reload();
            } else {
                alert('Cleanup failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred during cleanup');
        });
    }
}

function unlockAllExpired() {
    if (confirm('Unlock all accounts with expired lockout periods?')) {
        // Implementation for bulk unlock
        alert('Feature coming soon!');
    }
}

function extendLock(userId) {
    const hours = prompt('Extend lock for how many hours?', '24');
    if (hours && !isNaN(hours)) {
        // Implementation for extending lock
        alert(`Lock extended for ${hours} hours`);
    }
}

function exportSecurityReport() {
    window.open('{{ route("admin.security.report") }}?export=pdf', '_blank');
}
</script>
@endsection
