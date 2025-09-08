@extends('layouts.app')

@section('title', 'Device Management')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="fas fa-mobile-alt text-primary me-2"></i>
                        My Devices
                    </h1>
                    <p class="text-muted mb-0">Manage your trusted devices and security settings</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshDevices()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Device Security Overview -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-primary-subtle border-primary-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-devices text-primary fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $userDevices->count() }}</h3>
                            <p class="text-muted mb-0">Registered Devices</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-success-subtle border-success-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-shield-check text-success fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $userDevices->where('is_trusted', true)->count() }}</h3>
                            <p class="text-muted mb-0">Trusted Devices</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-clock text-info fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">
                                {{ $userDevices->where('last_seen_at', '>', now()->subDays(30))->count() }}
                            </h3>
                            <p class="text-muted mb-0">Active Devices</p>
                            <small class="text-info">Last 30 days</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Device Alert -->
            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-info-circle me-3 fs-4"></i>
                <div>
                    <h6 class="alert-heading mb-1">Current Device Information</h6>
                    <p class="mb-0">
                        You are currently using: <strong id="currentDevice">Loading...</strong><br>
                        <small class="text-muted">
                            IP: <code id="currentIp">{{ request()->ip() }}</code> • 
                            Last Activity: <span id="currentTime">{{ now()->format('M d, Y H:i:s') }}</span>
                        </small>
                    </p>
                </div>
            </div>

            <!-- Device Management Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-cog text-primary me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-success" onclick="trustCurrentDevice()">
                                    <i class="fas fa-shield-check me-2"></i>
                                    Trust This Device
                                </button>
                                <small class="text-muted mt-1 text-center">
                                    Skip verification on this device for future logins
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-warning" onclick="logoutAllDevices()">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Logout All Devices
                                </button>
                                <small class="text-muted mt-1 text-center">
                                    Sign out from all devices except this one
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Devices List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-list text-primary me-2"></i>
                            My Devices
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showAllDevices" onchange="toggleDeviceView()">
                            <label class="form-check-label" for="showAllDevices">
                                <small>Show inactive devices</small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($userDevices->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($userDevices as $device)
                            <div class="list-group-item border-0 py-4 device-item {{ $device->last_seen_at && $device->last_seen_at->lt(now()->subDays(30)) ? 'inactive-device' : '' }}" 
                                 style="{{ $device->last_seen_at && $device->last_seen_at->lt(now()->subDays(30)) ? 'display: none;' : '' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center">
                                            <!-- Device Icon -->
                                            <div class="avatar-lg {{ $device->is_trusted ? 'bg-success-subtle' : 'bg-warning-subtle' }} rounded-circle d-flex align-items-center justify-content-center me-4">
                                                @if($device->device_type === 'mobile')
                                                    <i class="fas fa-mobile-alt {{ $device->is_trusted ? 'text-success' : 'text-warning' }} fs-3"></i>
                                                @elseif($device->device_type === 'tablet')
                                                    <i class="fas fa-tablet-alt {{ $device->is_trusted ? 'text-success' : 'text-warning' }} fs-3"></i>
                                                @else
                                                    <i class="fas fa-desktop {{ $device->is_trusted ? 'text-success' : 'text-warning' }} fs-3"></i>
                                                @endif
                                            </div>
                                            
                                            <!-- Device Details -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h5 class="mb-0 fw-bold text-dark">{{ $device->device_name }}</h5>
                                                    @if($device->is_trusted)
                                                        <span class="badge bg-success ms-2">
                                                            <i class="fas fa-shield-check me-1"></i> Trusted
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-question-circle me-1"></i> Untrusted
                                                        </span>
                                                    @endif
                                                    
                                                    @if($device->last_seen_at && $device->last_seen_at->gt(now()->subHours(1)))
                                                        <span class="badge bg-primary ms-2">
                                                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Online
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="text-muted mb-2">
                                                    <i class="fas fa-desktop me-2"></i>
                                                    {{ $device->platform }} • {{ $device->browser }}
                                                </div>
                                                
                                                @if($device->last_ip)
                                                    <div class="text-muted mb-2">
                                                        <i class="fas fa-map-marker-alt me-2"></i>
                                                        <code class="text-muted">{{ $device->last_ip }}</code>
                                                        @if($device->location)
                                                            • {{ $device->location }}
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                                <div class="text-muted">
                                                    <i class="fas fa-clock me-2"></i>
                                                    @if($device->last_seen_at)
                                                        Last active: {{ $device->last_seen_at->diffForHumans() }}
                                                        <small class="ms-2">({{ $device->last_seen_at->format('M d, Y H:i') }})</small>
                                                    @else
                                                        Never used
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 text-end">
                                        <div class="btn-group" role="group">
                                            @if($device->is_trusted)
                                                <form action="{{ route('user.devices.untrust', $device) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning" 
                                                            onclick="return confirm('Remove trust from this device? You will need to verify your identity again.')"
                                                            title="Remove Trust">
                                                        <i class="fas fa-times me-1"></i> Untrust
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('user.devices.trust', $device) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" 
                                                            title="Trust Device">
                                                        <i class="fas fa-check me-1"></i> Trust
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <button class="btn btn-outline-primary" 
                                                    onclick="viewDeviceDetails('{{ $device->id }}')"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <form action="{{ route('user.devices.destroy', $device) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" 
                                                        onclick="return confirm('Remove this device? You will need to register it again on next login.')"
                                                        title="Remove Device">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Device Security Score -->
                                @if($device->risk_score > 0)
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <small class="text-muted me-2">Security Score:</small>
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    @php
                                                        $score = 100 - $device->risk_score;
                                                        $scoreClass = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger');
                                                    @endphp
                                                    <div class="progress-bar bg-{{ $scoreClass }}" style="width: {{ $score }}%"></div>
                                                </div>
                                                <small class="text-{{ $scoreClass }} fw-semibold">{{ $score }}%</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-xl bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-mobile-alt text-primary fs-2"></i>
                            </div>
                            <h5 class="text-dark fw-bold mb-2">No Devices Registered</h5>
                            <p class="text-muted mb-4">Your devices will appear here after you log in from them</p>
                            <button class="btn btn-primary" onclick="registerCurrentDevice()">
                                <i class="fas fa-plus me-2"></i> Register This Device
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Security Tips -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-lightbulb text-primary me-2"></i>
                        Security Tips
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark">
                                <i class="fas fa-shield-check text-success me-2"></i>
                                Trusted Devices
                            </h6>
                            <ul class="list-unstyled text-muted">
                                <li>• Only trust devices you personally own and control</li>
                                <li>• Regularly review and remove old or unused devices</li>
                                <li>• Don't trust public or shared computers</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Stay Secure
                            </h6>
                            <ul class="list-unstyled text-muted">
                                <li>• Logout from all devices if you suspect unauthorized access</li>
                                <li>• Keep your devices updated with latest security patches</li>
                                <li>• Use strong, unique passwords for your account</li>
                            </ul>
                        </div>
                    </div>
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

<style>
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

.device-item {
    transition: opacity 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    detectCurrentDevice();
    updateCurrentTime();
    
    // Update time every minute
    setInterval(updateCurrentTime, 60000);
});

function detectCurrentDevice() {
    const userAgent = navigator.userAgent;
    let deviceInfo = 'Unknown Device';
    
    // Simple device detection
    if (/Mobile|Android|iPhone|iPad/.test(userAgent)) {
        if (/iPad/.test(userAgent)) {
            deviceInfo = 'iPad';
        } else if (/iPhone/.test(userAgent)) {
            deviceInfo = 'iPhone';
        } else if (/Android/.test(userAgent)) {
            deviceInfo = 'Android Device';
        } else {
            deviceInfo = 'Mobile Device';
        }
    } else {
        if (/Windows/.test(userAgent)) {
            deviceInfo = 'Windows Computer';
        } else if (/Mac/.test(userAgent)) {
            deviceInfo = 'Mac Computer';
        } else if (/Linux/.test(userAgent)) {
            deviceInfo = 'Linux Computer';
        } else {
            deviceInfo = 'Desktop Computer';
        }
    }
    
    // Add browser info
    if (/Chrome/.test(userAgent)) {
        deviceInfo += ' (Chrome)';
    } else if (/Firefox/.test(userAgent)) {
        deviceInfo += ' (Firefox)';
    } else if (/Safari/.test(userAgent)) {
        deviceInfo += ' (Safari)';
    } else if (/Edge/.test(userAgent)) {
        deviceInfo += ' (Edge)';
    }
    
    document.getElementById('currentDevice').textContent = deviceInfo;
}

function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
    document.getElementById('currentTime').textContent = timeString;
}

function refreshDevices() {
    location.reload();
}

function toggleDeviceView() {
    const showAll = document.getElementById('showAllDevices').checked;
    const inactiveDevices = document.querySelectorAll('.inactive-device');
    
    inactiveDevices.forEach(device => {
        device.style.display = showAll ? 'block' : 'none';
    });
}

function trustCurrentDevice() {
    if (confirm('Trust this device? You will not need to verify your identity again on future logins from this device.')) {
        fetch('{{ route("user.devices.trust-current") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Device trusted successfully!');
                location.reload();
            } else {
                alert('Failed to trust device: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred while trusting device');
        });
    }
}

function logoutAllDevices() {
    if (confirm('Sign out from all other devices? You will remain logged in on this device.')) {
        fetch('{{ route("user.devices.logout-all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Successfully signed out from ${data.count} devices`);
                location.reload();
            } else {
                alert('Failed to logout from other devices');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred while logging out devices');
        });
    }
}

function registerCurrentDevice() {
    if (confirm('Register this device for enhanced security tracking?')) {
        fetch('{{ route("user.devices.register-current") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Device registered successfully!');
                location.reload();
            } else {
                alert('Failed to register device: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred while registering device');
        });
    }
}

function viewDeviceDetails(deviceId) {
    const modal = new bootstrap.Modal(document.getElementById('deviceDetailsModal'));
    const content = document.getElementById('deviceDetailsContent');
    
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading device details...</div>';
    modal.show();
    
    fetch(`{{ route('user.devices.show', ':id') }}`.replace(':id', deviceId))
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">Error loading device details</div>';
        });
}
</script>
@endsection
