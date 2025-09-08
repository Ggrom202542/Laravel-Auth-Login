@extends('layouts.admin')

@section('title', 'IP Management System')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="fas fa-globe-americas text-primary me-2"></i>
                        IP Management System
                    </h1>
                    <p class="text-muted mb-0">Manage IP restrictions, whitelist, and geographic access controls</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="refreshStats()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIpModal">
                            <i class="fas fa-plus me-1"></i> Add IP Rule
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportIpRules('csv')">
                                    <i class="fas fa-file-csv me-2"></i> Export CSV
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportIpRules('pdf')">
                                    <i class="fas fa-file-pdf me-2"></i> Export PDF
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-danger-subtle border-danger-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-ban text-danger fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['blocked_count'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Blocked IPs</p>
                            <small class="text-danger">
                                <i class="fas fa-arrow-up me-1"></i>{{ $statistics['blocked_today'] ?? 0 }} today
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-success-subtle border-success-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-check-circle text-success fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['allowed_count'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Whitelisted IPs</p>
                            <small class="text-success">
                                <i class="fas fa-shield-alt me-1"></i>Protected access
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
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['temporary_count'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Temporary Blocks</p>
                            <small class="text-warning">
                                <i class="fas fa-hourglass-half me-1"></i>Auto-expiring
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-globe text-info fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $statistics['countries_blocked'] ?? 0 }}</h3>
                            <p class="text-muted mb-0">Countries Blocked</p>
                            <small class="text-info">
                                <i class="fas fa-flag me-1"></i>Geographic controls
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
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Search IP Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="192.168.1.1 or 192.168.*"
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="blocked" {{ request('type') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                    <option value="allowed" {{ request('type') == 'allowed' ? 'selected' : '' }}>Allowed</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Country</label>
                                <select name="country" class="form-select">
                                    <option value="">All Countries</option>
                                    @foreach($countries ?? [] as $country)
                                    <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                                        {{ $country }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
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

            <!-- IP Rules Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-list text-primary me-2"></i>
                            IP Restrictions ({{ $ipRestrictions->total() ?? 0 }} total)
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-warning" onclick="cleanupExpiredIPs()">
                                <i class="fas fa-broom me-1"></i> Cleanup Expired
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-cog me-1"></i> Bulk Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="bulkDelete()">
                                        <i class="fas fa-trash me-2"></i> Delete Selected
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkBlock()">
                                        <i class="fas fa-ban me-2"></i> Block Selected
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkAllow()">
                                        <i class="fas fa-check me-2"></i> Allow Selected
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($ipRestrictions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th class="border-0">IP Address</th>
                                        <th class="border-0">Type</th>
                                        <th class="border-0">Location</th>
                                        <th class="border-0">Reason</th>
                                        <th class="border-0">Created</th>
                                        <th class="border-0">Expires</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ipRestrictions as $ip)
                                    <tr>
                                        <td class="ps-4">
                                            <input type="checkbox" class="form-check-input ip-checkbox" 
                                                   value="{{ $ip->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm {{ $ip->type === 'blocked' ? 'bg-danger-subtle' : 'bg-success-subtle' }} rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="fas {{ $ip->type === 'blocked' ? 'fa-ban text-danger' : 'fa-check text-success' }}"></i>
                                                </div>
                                                <div>
                                                    <code class="fw-bold">{{ $ip->ip_address }}</code>
                                                    @if($ip->is_range)
                                                        <span class="badge bg-info-subtle text-info ms-2">Range</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($ip->type === 'blocked')
                                                <span class="badge bg-danger">Blocked</span>
                                            @else
                                                <span class="badge bg-success">Allowed</span>
                                            @endif
                                            @if($ip->is_temporary)
                                                <span class="badge bg-warning-subtle text-warning ms-1">Temporary</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ip->country || $ip->city)
                                                <div class="d-flex align-items-center">
                                                    @if($ip->country)
                                                        <img src="https://flagcdn.com/16x12/{{ strtolower($ip->country_code ?? 'xx') }}.png" 
                                                             class="me-2" alt="{{ $ip->country }}">
                                                    @endif
                                                    <div>
                                                        <small class="fw-semibold">{{ $ip->country ?? 'Unknown' }}</small>
                                                        @if($ip->city)
                                                            <br><small class="text-muted">{{ $ip->city }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted" title="{{ $ip->reason }}">
                                                {{ Str::limit($ip->reason ?? 'No reason provided', 30) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $ip->created_at->format('M d, H:i') }}
                                                <br>{{ $ip->created_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($ip->expires_at)
                                                @if($ip->expires_at->isPast())
                                                    <span class="badge bg-secondary">Expired</span>
                                                @else
                                                    <small class="text-warning">
                                                        {{ $ip->expires_at->format('M d, H:i') }}
                                                        <br>{{ $ip->expires_at->diffForHumans() }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge bg-primary">Permanent</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($ip->type === 'blocked')
                                                    <form action="{{ route('admin.security.ip.allow', $ip) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                title="Allow IP">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.security.ip.block', $ip) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                title="Block IP">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewIpDetails('{{ $ip->id }}')"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" 
                                                        onclick="editIp('{{ $ip->id }}')"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.security.ip.destroy', $ip) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Delete this IP restriction?')"
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
                        @if($ipRestrictions->hasPages())
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            Showing {{ $ipRestrictions->firstItem() }} to {{ $ipRestrictions->lastItem() }} 
                                            of {{ $ipRestrictions->total() }} results
                                        </small>
                                    </div>
                                    <div>
                                        {{ $ipRestrictions->links() }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-globe text-primary fs-2"></i>
                            </div>
                            <h5 class="text-dark fw-bold mb-2">No IP Restrictions</h5>
                            <p class="text-muted mb-4">Start protecting your application by adding IP restrictions</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIpModal">
                                <i class="fas fa-plus me-2"></i> Add First IP Rule
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add IP Modal -->
<div class="modal fade" id="addIpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-primary me-2"></i>
                    Add IP Restriction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.security.ip.store') }}" method="POST" id="addIpForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">IP Address <span class="text-danger">*</span></label>
                            <input type="text" name="ip_address" class="form-control" 
                                   placeholder="192.168.1.1 or 192.168.1.0/24" required>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Supports single IPs, CIDR notation, or wildcards (192.168.1.*)
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="blocked">Block (Deny Access)</option>
                                <option value="allowed">Allow (Whitelist)</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Reason</label>
                            <textarea name="reason" class="form-control" rows="3" 
                                      placeholder="Reason for this IP restriction..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_temporary" class="form-check-input" id="isTemporary">
                                <label class="form-check-label" for="isTemporary">
                                    Temporary restriction
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3" id="expiresAtField" style="display: none;">
                            <label class="form-label fw-semibold">Expires At</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Add IP Restriction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye text-primary me-2"></i>
                    IP Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ipDetailsContent">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle expires field based on temporary checkbox
    const isTemporaryCheckbox = document.getElementById('isTemporary');
    const expiresAtField = document.getElementById('expiresAtField');
    
    isTemporaryCheckbox.addEventListener('change', function() {
        if (this.checked) {
            expiresAtField.style.display = 'block';
            // Set default expiry to 24 hours from now
            const now = new Date();
            now.setHours(now.getHours() + 24);
            const expires = now.toISOString().slice(0, 16);
            document.querySelector('input[name="expires_at"]').value = expires;
        } else {
            expiresAtField.style.display = 'none';
            document.querySelector('input[name="expires_at"]').value = '';
        }
    });

    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const ipCheckboxes = document.querySelectorAll('.ip-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        ipCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

function refreshStats() {
    location.reload();
}

function clearFilters() {
    const form = document.getElementById('filterForm');
    form.reset();
    form.submit();
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
                alert(`Cleanup completed: ${data.count} expired restrictions removed`);
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

function viewIpDetails(ipId) {
    const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
    const content = document.getElementById('ipDetailsContent');
    
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.show();
    
    fetch(`{{ route('admin.security.ip.show', ':id') }}`.replace(':id', ipId))
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error loading details</div>';
        });
}

function editIp(ipId) {
    // Implement edit functionality
    alert('Edit functionality will be implemented');
}

function exportIpRules(format) {
    window.open(`{{ route('admin.security.ip.export') }}?format=${format}`, '_blank');
}

function getSelectedIps() {
    const checkboxes = document.querySelectorAll('.ip-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function bulkDelete() {
    const selected = getSelectedIps();
    if (selected.length === 0) {
        alert('Please select IP restrictions to delete');
        return;
    }
    
    if (confirm(`Delete ${selected.length} selected IP restrictions?`)) {
        // Implement bulk delete
        alert('Bulk delete functionality will be implemented');
    }
}

function bulkBlock() {
    const selected = getSelectedIps();
    if (selected.length === 0) {
        alert('Please select IP restrictions to block');
        return;
    }
    
    if (confirm(`Block ${selected.length} selected IPs?`)) {
        // Implement bulk block
        alert('Bulk block functionality will be implemented');
    }
}

function bulkAllow() {
    const selected = getSelectedIps();
    if (selected.length === 0) {
        alert('Please select IP restrictions to allow');
        return;
    }
    
    if (confirm(`Allow ${selected.length} selected IPs?`)) {
        // Implement bulk allow
        alert('Bulk allow functionality will be implemented');
    }
}
</script>
@endsection
