@extends('layouts.app')

@section('title', 'Security Notifications')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="fas fa-bell text-primary me-2"></i>
                        Security Notifications
                    </h1>
                    <p class="text-muted mb-0">Configure how you want to be notified about security events</p>
                </div>
                <div>
                    <a href="{{ route('user.devices.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-mobile-alt me-1"></i> Manage Devices
                    </a>
                </div>
            </div>

            <!-- Current Security Status -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-success-subtle border-success-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-shield-check text-success fs-2"></i>
                            </div>
                            <h5 class="mb-1 fw-bold text-dark">Account Secure</h5>
                            <p class="text-muted mb-0">No recent security issues</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-primary-subtle border-primary-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-bell text-primary fs-2"></i>
                            </div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $notificationSettings['email_enabled'] ? 'Email On' : 'Email Off' }}</h5>
                            <p class="text-muted mb-0">Email notifications</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-mobile text-info fs-2"></i>
                            </div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $notificationSettings['sms_enabled'] ? 'SMS On' : 'SMS Off' }}</h5>
                            <p class="text-muted mb-0">SMS notifications</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings Form -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-cog text-primary me-2"></i>
                        Notification Preferences
                    </h5>
                </div>
                <form action="{{ route('user.notifications.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Email Notifications -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    Email Notifications
                                </h6>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="email_enabled" 
                                               id="emailEnabled" {{ $notificationSettings['email_enabled'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="emailEnabled">
                                            <strong>Enable email notifications</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Receive security alerts via email at {{ auth()->user()->email }}</small>
                                </div>

                                <div id="emailOptions" style="{{ $notificationSettings['email_enabled'] ? '' : 'display: none;' }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="email_login_alerts" 
                                                       id="emailLoginAlerts" {{ $notificationSettings['email_login_alerts'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="emailLoginAlerts">
                                                    New login alerts
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mb-2">Get notified when someone logs into your account</small>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="email_suspicious_activity" 
                                                       id="emailSuspiciousActivity" {{ $notificationSettings['email_suspicious_activity'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="emailSuspiciousActivity">
                                                    Suspicious activity alerts
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mb-2">Get notified about unusual login attempts</small>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="email_account_changes" 
                                                       id="emailAccountChanges" {{ $notificationSettings['email_account_changes'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="emailAccountChanges">
                                                    Account changes
                                                </label>
                                            </div>
                                            <small class="text-muted d-block">Get notified about password changes, email updates, etc.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="email_device_alerts" 
                                                       id="emailDeviceAlerts" {{ $notificationSettings['email_device_alerts'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="emailDeviceAlerts">
                                                    New device alerts
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mb-2">Get notified when a new device is used</small>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="email_security_reports" 
                                                       id="emailSecurityReports" {{ $notificationSettings['email_security_reports'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="emailSecurityReports">
                                                    Weekly security summary
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mb-2">Get a weekly summary of your account security</small>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="email_ip_blocks" 
                                                       id="emailIpBlocks" {{ $notificationSettings['email_ip_blocks'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="emailIpBlocks">
                                                    IP blocking alerts
                                                </label>
                                            </div>
                                            <small class="text-muted d-block">Get notified when an IP is blocked due to your account</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- SMS Notifications -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="fas fa-mobile text-primary me-2"></i>
                                    SMS Notifications
                                </h6>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="sms_enabled" 
                                               id="smsEnabled" {{ $notificationSettings['sms_enabled'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="smsEnabled">
                                            <strong>Enable SMS notifications</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        Receive critical security alerts via SMS
                                        @if(auth()->user()->phone)
                                            at {{ auth()->user()->phone }}
                                        @else
                                            <a href="{{ route('profile.edit') }}" class="text-primary">(Add phone number)</a>
                                        @endif
                                    </small>
                                </div>

                                <div id="smsOptions" style="{{ $notificationSettings['sms_enabled'] ? '' : 'display: none;' }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sms_critical_alerts" 
                                                       id="smsCriticalAlerts" {{ $notificationSettings['sms_critical_alerts'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="smsCriticalAlerts">
                                                    Critical security alerts
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mb-2">High-risk login attempts and account breaches</small>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sms_account_lockouts" 
                                                       id="smsAccountLockouts" {{ $notificationSettings['sms_account_lockouts'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="smsAccountLockouts">
                                                    Account lockouts
                                                </label>
                                            </div>
                                            <small class="text-muted d-block">When your account is locked due to failed login attempts</small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sms_password_changes" 
                                                       id="smsPasswordChanges" {{ $notificationSettings['sms_password_changes'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="smsPasswordChanges">
                                                    Password changes
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mb-2">When your password is changed</small>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sms_emergency_access" 
                                                       id="smsEmergencyAccess" {{ $notificationSettings['sms_emergency_access'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="smsEmergencyAccess">
                                                    Emergency access attempts
                                                </label>
                                            </div>
                                            <small class="text-muted d-block">Account recovery and emergency access requests</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Notification Timing -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    Notification Timing
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Quiet hours (no notifications)</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <select name="quiet_hours_start" class="form-select">
                                                    <option value="">No quiet hours</option>
                                                    @for($i = 0; $i < 24; $i++)
                                                        @php $hour = sprintf('%02d:00', $i); @endphp
                                                        <option value="{{ $hour }}" {{ $notificationSettings['quiet_hours_start'] == $hour ? 'selected' : '' }}>
                                                            {{ $hour }}
                                                        </option>
                                                    @endfor
                                                </select>
                                                <small class="text-muted">Start time</small>
                                            </div>
                                            <div class="col-6">
                                                <select name="quiet_hours_end" class="form-select">
                                                    <option value="">No quiet hours</option>
                                                    @for($i = 0; $i < 24; $i++)
                                                        @php $hour = sprintf('%02d:00', $i); @endphp
                                                        <option value="{{ $hour }}" {{ $notificationSettings['quiet_hours_end'] == $hour ? 'selected' : '' }}>
                                                            {{ $hour }}
                                                        </option>
                                                    @endfor
                                                </select>
                                                <small class="text-muted">End time</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Notification frequency</label>
                                        <select name="notification_frequency" class="form-select">
                                            <option value="immediate" {{ $notificationSettings['notification_frequency'] == 'immediate' ? 'selected' : '' }}>
                                                Immediate
                                            </option>
                                            <option value="hourly" {{ $notificationSettings['notification_frequency'] == 'hourly' ? 'selected' : '' }}>
                                                Hourly digest
                                            </option>
                                            <option value="daily" {{ $notificationSettings['notification_frequency'] == 'daily' ? 'selected' : '' }}>
                                                Daily digest
                                            </option>
                                        </select>
                                        <small class="text-muted">How often to receive non-critical notifications</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Critical security alerts will always be sent regardless of your settings
                            </small>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="testNotifications()">
                                    <i class="fas fa-paper-plane me-1"></i> Send Test
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Recent Notifications -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-history text-primary me-2"></i>
                            Recent Notifications
                        </h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="clearNotificationHistory()">
                            <i class="fas fa-trash me-1"></i> Clear History
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php
                        $recentNotifications = [
                            [
                                'type' => 'login',
                                'title' => 'Successful login',
                                'message' => 'You signed in from Chrome on Windows',
                                'time' => '2 hours ago',
                                'icon' => 'sign-in-alt',
                                'color' => 'success'
                            ],
                            [
                                'type' => 'device',
                                'title' => 'New device trusted',
                                'message' => 'You trusted a new device: iPhone 14',
                                'time' => '1 day ago',
                                'icon' => 'mobile-alt',
                                'color' => 'info'
                            ],
                            [
                                'type' => 'security',
                                'title' => 'Suspicious activity blocked',
                                'message' => 'We blocked a login attempt from an unknown location',
                                'time' => '3 days ago',
                                'icon' => 'shield-alt',
                                'color' => 'warning'
                            ]
                        ];
                    @endphp
                    
                    @if(count($recentNotifications) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentNotifications as $notification)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-{{ $notification['color'] }}-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-{{ $notification['icon'] }} text-{{ $notification['color'] }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">{{ $notification['title'] }}</h6>
                                        <p class="mb-1 text-muted">{{ $notification['message'] }}</p>
                                        <small class="text-muted">{{ $notification['time'] }}</small>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-external-link-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="avatar-xl bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-bell-slash text-secondary fs-2"></i>
                            </div>
                            <h6 class="text-dark fw-bold mb-2">No Recent Notifications</h6>
                            <p class="text-muted mb-0">Your notification history will appear here</p>
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

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-secondary-subtle {
    background-color: rgba(108, 117, 125, 0.1) !important;
}

.border-primary-subtle {
    border-color: rgba(13, 110, 253, 0.2) !important;
}

.border-success-subtle {
    border-color: rgba(25, 135, 84, 0.2) !important;
}

.border-info-subtle {
    border-color: rgba(13, 202, 240, 0.2) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle email options
    const emailEnabled = document.getElementById('emailEnabled');
    const emailOptions = document.getElementById('emailOptions');
    
    emailEnabled.addEventListener('change', function() {
        emailOptions.style.display = this.checked ? 'block' : 'none';
    });

    // Toggle SMS options
    const smsEnabled = document.getElementById('smsEnabled');
    const smsOptions = document.getElementById('smsOptions');
    
    smsEnabled.addEventListener('change', function() {
        smsOptions.style.display = this.checked ? 'block' : 'none';
    });
});

function testNotifications() {
    if (confirm('Send test notifications to verify your settings?')) {
        fetch('{{ route("user.notifications.test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Test notifications sent! Check your email and phone for test messages.');
            } else {
                alert('Failed to send test notifications: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred while sending test notifications');
        });
    }
}

function clearNotificationHistory() {
    if (confirm('Clear all notification history? This action cannot be undone.')) {
        fetch('{{ route("user.notifications.clear-history") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Notification history cleared successfully');
                location.reload();
            } else {
                alert('Failed to clear notification history');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred while clearing notification history');
        });
    }
}
</script>
@endsection
