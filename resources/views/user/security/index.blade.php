@extends('layouts.dashboard')

@section('title', 'แดชบอร์ดความปลอดภัย')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 fw-bold text-dark">
                        <i class="bi bi-shield-lock text-primary me-2"></i>
                        แดชบอร์ดความปลอดภัย
                    </h1>
                    <p class="text-muted mb-0">ตรวจสอบความปลอดภัยบัญชีและจัดการอุปกรณ์ที่เชื่อถือได้</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="refreshSecurityData()">
                        <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                    </button>
                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i> การดำเนินการ
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('user.security.devices') }}">
                                <i class="bi bi-phone me-2"></i>จัดการอุปกรณ์
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('user.security.login-history') }}">
                                <i class="bi bi-clock-history me-2"></i>ประวัติการเข้าสู่ระบบ
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('user.security.alerts') }}">
                                <i class="bi bi-exclamation-triangle me-2"></i>การแจ้งเตือนความปลอดภัย
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="exportSecurityData()">
                                <i class="bi bi-download me-2"></i>ส่งออกข้อมูลความปลอดภัย
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Live Security Alerts -->
            <div id="live-security-alerts" class="mb-4" style="display: none;">
                <div class="alert alert-info border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-info me-3" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                        <div>
                            <strong>ระบบตรวจสอบความปลอดภัยแบบเรียลไทม์เปิดใช้งาน</strong>
                            <div class="small text-muted">เหตุการณ์ความปลอดภัยแบบเรียลไทม์จะแสดงที่นี่</div>
                        </div>
                        <button type="button" class="btn-close ms-auto" onclick="hideLiveAlerts()"></button>
                    </div>
                </div>
            </div>

            <!-- Security Score Card -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="card-title mb-3">
                                        <i class="bi bi-graph-up text-success me-2"></i>
                                        คะแนนความปลอดภัย
                                    </h5>
                                    <div class="progress mb-3" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $securityStats['security_score'] >= 80 ? 'success' : ($securityStats['security_score'] >= 60 ? 'warning' : 'danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ $securityStats['security_score'] }}%">
                                            {{ $securityStats['security_score'] }}/100
                                        </div>
                                    </div>
                                    <p class="text-muted mb-0">
                                        @if($securityStats['security_score'] >= 80)
                                            <span class="text-success fw-bold">ความปลอดภัยยอดเยี่ยม!</span> บัญชีของคุณได้รับการปกป้องเป็นอย่างดี
                                        @elseif($securityStats['security_score'] >= 60)
                                            <span class="text-warning fw-bold">ความปลอดภัยดี</span> ควรพิจารณาเปิดใช้คุณสมบัติความปลอดภัยเพิ่มเติม
                                        @else
                                            <span class="text-danger fw-bold">ความปลอดภัยต้องได้รับการปรับปรุง</span> กรุณาตรวจสอบการตั้งค่าความปลอดภัยของคุณ
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="security-score-circle">
                                        <span id="security-score" class="h2 fw-bold text-{{ $securityStats['security_score'] >= 80 ? 'success' : ($securityStats['security_score'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ $securityStats['security_score'] }}
                                        </span>
                                        <div class="small text-muted">คะแนนความปลอดภัย</div>
                                        <div class="small text-success mt-1" id="score-status" style="display: none;">
                                            <i class="bi bi-arrow-clockwise"></i> กำลังอัปเดต...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-primary-subtle border-primary-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-calendar-event text-primary fs-2"></i>
                            </div>
                            <h3 class="mb-1 fw-bold text-dark">{{ $securityStats['account_age_days'] }}</h3>
                            <p class="text-muted mb-0">วันที่ใช้งาน</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-success-subtle border-success-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-box-arrow-in-right text-success fs-2"></i>
                            </div>
                            <h3 id="total-logins-count" class="mb-1 fw-bold text-dark">{{ number_format($securityStats['total_logins']) }}</h3>
                            <p class="text-muted mb-0">การเข้าสู่ระบบสำเร็จ</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-{{ $securityStats['failed_attempts'] > 5 ? 'danger' : 'warning' }}-subtle border-{{ $securityStats['failed_attempts'] > 5 ? 'danger' : 'warning' }}-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-{{ $securityStats['failed_attempts'] > 5 ? 'danger' : 'warning' }}-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-x-circle text-{{ $securityStats['failed_attempts'] > 5 ? 'danger' : 'warning' }} fs-2"></i>
                            </div>
                            <h3 id="failed-attempts-count" class="mb-1 fw-bold text-dark">{{ $securityStats['failed_attempts'] }}</h3>
                            <p class="text-muted mb-0">ความพยายามที่ล้มเหลว</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-card bg-info-subtle border-info-subtle">
                        <div class="card-body text-center">
                            <div class="avatar-lg bg-info-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="bi bi-phone text-info fs-2"></i>
                            </div>
                            <h3 id="devices-count" class="mb-1 fw-bold text-dark">{{ $securityStats['devices_count'] }}</h3>
                            <p class="text-muted mb-0">อุปกรณ์ที่ลงทะเบียน</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Recent Login Activity -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-history text-primary me-2"></i>
                                    Recent Login Activity
                                </h6>
                                <a href="{{ route('user.security.login-history') }}" class="btn btn-sm btn-outline-primary">
                                    View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($recentLogins->count() > 0)
                                @foreach($recentLogins->take(5) as $login)
                                <div class="d-flex align-items-center mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                                    <div class="avatar-sm bg-{{ $login->is_suspicious ? 'danger' : 'success' }}-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-{{ $login->is_suspicious ? 'exclamation-triangle' : 'check' }} text-{{ $login->is_suspicious ? 'danger' : 'success' }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <div class="fw-medium">{{ $login->ip_address }}</div>
                                                <div class="small text-muted">
                                                    {{ $login->city ?? 'Unknown' }}, {{ $login->country_name ?? 'Unknown Location' }}
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-muted">{{ $login->attempted_at->diffForHumans() }}</div>
                                                @if($login->is_suspicious)
                                                    <span class="badge bg-danger-subtle text-danger">Suspicious</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-info-circle text-muted fs-2 mb-2"></i>
                                    <p class="text-muted mb-0">No recent login activity</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Trusted Devices -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-shield-check text-success me-2"></i>
                                    Trusted Devices
                                </h6>
                                <a href="{{ route('user.security.devices') }}" class="btn btn-sm btn-outline-primary">
                                    Manage All
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($userDevices->count() > 0)
                                @foreach($userDevices as $device)
                                <div class="d-flex align-items-center mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                                    <div class="avatar-sm bg-{{ $device->is_trusted ? 'success' : 'secondary' }}-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-{{ $device->device_type === 'mobile' ? 'mobile-alt' : ($device->device_type === 'tablet' ? 'tablet-alt' : 'desktop') }} text-{{ $device->is_trusted ? 'success' : 'secondary' }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <div class="fw-medium">{{ $device->device_name ?? $device->browser_name }}</div>
                                                <div class="small text-muted">{{ $device->operating_system }}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-muted">{{ $device->last_seen_at?->diffForHumans() }}</div>
                                                @if($device->is_trusted)
                                                    <span class="badge bg-success-subtle text-success">Trusted</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary">Unverified</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-mobile-alt text-muted fs-2 mb-2"></i>
                                    <p class="text-muted mb-0">No devices registered</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Alerts -->
            @if($securityAlerts->count() > 0)
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    Recent Security Alerts
                                </h6>
                                <a href="{{ route('user.security.alerts') }}" class="btn btn-sm btn-outline-primary">
                                    View All Alerts
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach($securityAlerts->take(3) as $alert)
                            <div class="alert alert-{{ $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') }} border-start border-{{ $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') }} border-4 bg-{{ $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info') }}-subtle" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-{{ $alert['severity'] === 'high' ? 'exclamation-circle' : ($alert['severity'] === 'medium' ? 'exclamation-triangle' : 'info-circle') }} me-3 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium mb-1">{{ $alert['message'] }}</div>
                                        <div class="small text-muted">
                                            {{ $alert['location'] }} • {{ $alert['date']->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Real-time Activity Feed -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-activity text-info me-2"></i>
                                    กิจกรรมความปลอดภัยแบบเรียลไทม์
                                    <span class="badge bg-success ms-2" id="live-status">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>สด
                                    </span>
                                </h5>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-3" id="last-update">อัปเดตล่าสุด: เมื่อสักครู่</small>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleActivityFeed()">
                                        <i class="bi bi-pause-fill" id="activity-toggle-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="live-activity-feed" class="activity-feed" style="max-height: 300px; overflow-y: auto;">
                                <div class="text-center py-4" id="activity-placeholder">
                                    <i class="bi bi-radar text-muted fs-2 mb-2"></i>
                                    <p class="text-muted mb-0">กำลังตรวจสอบเหตุการณ์ความปลอดภัยแบบเรียลไทม์...</p>
                                    <small class="text-muted">กิจกรรมจะแสดงที่นี่เมื่อเกิดขึ้น</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-gear text-primary me-2"></i>
                                การตั้งค่าความปลอดภัย
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="securitySettingsForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="loginNotifications" 
                                                   {{ (auth()->user()->preferences['security']['login_notifications'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="loginNotifications">
                                                <strong>การแจ้งเตือนการเข้าสู่ระบบ</strong>
                                                <div class="small text-muted">รับการแจ้งเตือนเมื่อมีการเข้าสู่ระบบใหม่</div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="suspiciousActivityAlerts" 
                                                   {{ (auth()->user()->preferences['security']['suspicious_activity_alerts'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="suspiciousActivityAlerts">
                                                <strong>การแจ้งเตือนกิจกรรมน่าสงสัย</strong>
                                                <div class="small text-muted">แจ้งเตือนเมื่อมีกิจกรรมผิดปกติในบัญชี</div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="deviceManagementAlerts" 
                                                   {{ (auth()->user()->preferences['security']['device_management_alerts'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="deviceManagementAlerts">
                                                <strong>การแจ้งเตือนการจัดการอุปกรณ์</strong>
                                                <div class="small text-muted">แจ้งเตือนเมื่อตรวจพบอุปกรณ์ใหม่</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-floppy me-1"></i> บันทึกการตั้งค่า
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    .avatar-lg {
        width: 60px;
        height: 60px;
    }
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    .security-score-circle {
        padding: 20px;
        border: 3px solid #e9ecef;
        border-radius: 50%;
        display: inline-block;
    }
    
    /* Real-time Activity Feed Styles */
    .activity-feed {
        position: relative;
    }

    .activity-item {
        transition: all 0.3s ease;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .activity-item:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .activity-icon {
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Security Notification Styles */
    .toast.security-notification {
        min-width: 350px;
        margin-bottom: 0.5rem;
    }

    .toast.security-critical {
        border-left: 4px solid #dc3545;
    }

    .toast.security-high {
        border-left: 4px solid #fd7e14;
    }

    .toast.security-warning {
        border-left: 4px solid #ffc107;
    }

    .toast.security-info {
        border-left: 4px solid #0dcaf0;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    .live-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #28a745;
        animation: pulse 2s infinite;
    }
</style>
@endpush

@push('scripts')
<script>
// Initialize real-time activity feed when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Show live security alerts indicator
    document.getElementById('live-security-alerts').style.display = 'block';
    
    // Setup SecurityNotifications integration
    if (window.SecurityNotifications) {
        // Override the updateSecurityDashboard method to integrate with our dashboard
        const originalUpdateDashboard = window.SecurityNotifications.updateSecurityDashboard;
        window.SecurityNotifications.updateSecurityDashboard = function(event) {
            originalUpdateDashboard.call(this, event);
            
            // Add activity to our live feed
            if (!this.pauseActivityFeed) {
                addActivityItem({
                    type: event.type,
                    title: getEventTitle(event.type),
                    message: event.message,
                    severity: event.severity || 'info',
                    timestamp: event.timestamp
                });
            }
        };
        
        // Override handleSecurityEvent to add activities
        const originalHandleSecurityEvent = window.SecurityNotifications.handleSecurityEvent;
        window.SecurityNotifications.handleSecurityEvent = function(event) {
            originalHandleSecurityEvent.call(this, event);
            
            if (!this.pauseActivityFeed) {
                addActivityItem({
                    type: event.type,
                    title: getEventTitle(event.type),
                    message: event.message,
                    severity: event.severity || 'info',
                    timestamp: event.timestamp
                });
            }
        };
        
        // Override handleLoginAttempt to add activities
        const originalHandleLoginAttempt = window.SecurityNotifications.handleLoginAttempt;
        window.SecurityNotifications.handleLoginAttempt = function(event) {
            originalHandleLoginAttempt.call(this, event);
            
            if (!this.pauseActivityFeed) {
                const severity = event.is_suspicious ? 'warning' : (event.successful ? 'info' : 'warning');
                addActivityItem({
                    type: 'login_attempt',
                    title: event.successful ? 'Login Successful' : 'Login Attempt',
                    message: `From ${event.ip_address}${event.location ? ` (${event.location})` : ''}`,
                    severity: severity,
                    timestamp: event.timestamp
                });
            }
        };
        
        // Initialize pause state
        window.SecurityNotifications.pauseActivityFeed = false;
    }
    
    // Security Settings Form
    document.getElementById('securitySettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            login_notifications: document.getElementById('loginNotifications').checked,
            suspicious_activity_alerts: document.getElementById('suspiciousActivityAlerts').checked,
            device_management_alerts: document.getElementById('deviceManagementAlerts').checked,
            _token: '{{ csrf_token() }}'
        };

        fetch('{{ route("user.security.update-settings") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('การตั้งค่าความปลอดภัยได้รับการอัปเดตเรียบร้อยแล้ว!', 'success');
                
                // Add activity item for settings update
                addActivityItem({
                    type: 'settings_updated',
                    title: 'อัปเดตการตั้งค่า',
                    message: 'ค่ากำหนดการแจ้งเตือนความปลอดภัยได้รับการอัปเดต',
                    severity: 'info',
                    timestamp: new Date().toISOString()
                });
            } else {
                showToast('ไม่สามารถอัปเดตการตั้งค่าความปลอดภัยได้', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('เกิดข้อผิดพลาดในการอัปเดตการตั้งค่า', 'error');
        });
    });
    
    // Auto-refresh security statistics every 30 seconds
    setInterval(refreshSecurityData, 30000);
    
    // Update last update time every minute
    setInterval(function() {
        const lastUpdate = document.getElementById('last-update');
        if (lastUpdate) {
            const now = new Date();
            const minutes = Math.floor((now - window.lastSecurityUpdate || now) / 60000);
            lastUpdate.textContent = `อัปเดตล่าสุด: ${minutes > 0 ? minutes + ' นาทีที่แล้ว' : 'เมื่อสักครู่'}`;
        }
    }, 60000);
});

function refreshSecurityData() {
    // Show loading state
    document.getElementById('score-status').style.display = 'block';
    
    // Fetch updated security data
    fetch('{{ route("user.security.api.stats") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update security score
        const scoreElement = document.getElementById('security-score');
        if (scoreElement && data.security_score !== undefined) {
            scoreElement.textContent = data.security_score;
            scoreElement.className = `h2 fw-bold text-${getScoreColor(data.security_score)}`;
        }
        
        // Update statistics
        if (data.total_logins !== undefined) {
            document.getElementById('total-logins-count').textContent = new Intl.NumberFormat().format(data.total_logins);
        }
        if (data.failed_attempts !== undefined) {
            document.getElementById('failed-attempts-count').textContent = data.failed_attempts;
        }
        if (data.devices_count !== undefined) {
            document.getElementById('devices-count').textContent = data.devices_count;
        }
        
        // Hide loading state
        document.getElementById('score-status').style.display = 'none';
        
        // Update last update time
        document.getElementById('last-update').textContent = 'อัปเดตล่าสุด: เมื่อสักครู่';
    })
    .catch(error => {
        console.error('Error refreshing security data:', error);
        document.getElementById('score-status').style.display = 'none';
    });
}

function getScoreColor(score) {
    if (score >= 80) return 'success';
    if (score >= 60) return 'warning';
    return 'danger';
}

function toggleActivityFeed() {
    const icon = document.getElementById('activity-toggle-icon');
    const status = document.getElementById('live-status');
    
    if (icon.classList.contains('bi-pause-fill')) {
        icon.classList.remove('bi-pause-fill');
        icon.classList.add('bi-play-fill');
        status.innerHTML = '<i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>หยุดชั่วคราว';
        status.className = 'badge bg-warning ms-2';
        // Pause real-time updates
        if (window.SecurityNotifications) {
            window.SecurityNotifications.pauseActivityFeed = true;
        }
    } else {
        icon.classList.remove('bi-play-fill');
        icon.classList.add('bi-pause-fill');
        status.innerHTML = '<i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>สด';
        status.className = 'badge bg-success ms-2';
        // Resume real-time updates
        if (window.SecurityNotifications) {
            window.SecurityNotifications.pauseActivityFeed = false;
        }
    }
}

function addActivityItem(activity) {
    const feed = document.getElementById('live-activity-feed');
    const placeholder = document.getElementById('activity-placeholder');
    
    // Hide placeholder if this is the first item
    if (placeholder && placeholder.style.display !== 'none') {
        placeholder.style.display = 'none';
    }
    
    const item = document.createElement('div');
    item.className = 'activity-item border-bottom pb-3 mb-3';
    item.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="activity-icon bg-${activity.severity === 'critical' ? 'danger' : (activity.severity === 'warning' ? 'warning' : 'info')}-subtle rounded-circle p-2 me-3">
                <i class="bi ${getActivityIcon(activity.type)} text-${activity.severity === 'critical' ? 'danger' : (activity.severity === 'warning' ? 'warning' : 'info')}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-medium">${activity.title}</div>
                <div class="small text-muted">${activity.message}</div>
            </div>
            <div class="text-end">
                <small class="text-muted">${formatTimestamp(activity.timestamp)}</small>
            </div>
        </div>
    `;
    
    feed.insertBefore(item, feed.firstChild);
    
    // Keep only the last 20 items
    const items = feed.querySelectorAll('.activity-item');
    if (items.length > 20) {
        items[items.length - 1].remove();
    }
}

function getActivityIcon(type) {
    const icons = {
        'login_attempt': 'bi-box-arrow-in-right',
        'device_registered': 'bi-phone',
        'device_trusted': 'bi-shield-check',
        'device_untrusted': 'bi-shield-exclamation',
        'high_risk_login': 'bi-exclamation-triangle',
        'password_changed': 'bi-key',
        'settings_updated': 'bi-gear'
    };
    return icons[type] || 'bi-info-circle';
}

function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));

    if (diffInMinutes < 1) return 'เมื่อสักครู่';
    if (diffInMinutes < 60) return `${diffInMinutes} นาทีที่แล้ว`;
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)} ชั่วโมงที่แล้ว`;
    
    return date.toLocaleDateString('th-TH');
}

function getEventTitle(type) {
    const titles = {
        'high_risk_login': 'การเข้าสู่ระบบเสี่ยงสูง',
        'device_registered': 'อุปกรณ์ใหม่',
        'device_trusted': 'อุปกรณ์ที่เชื่อถือ',
        'device_untrusted': 'อุปกรณ์ไม่เชื่อถือ',
        'device_removed': 'อุปกรณ์ถูกลบ',
        'ip_blocked': 'IP Address ถูกบล็อก',
        'account_locked': 'บัญชีถูกล็อก',
        'password_changed': 'เปลี่ยนรหัสผ่าน',
        '2fa_enabled': 'เปิดใช้ 2FA',
        '2fa_disabled': 'ปิดใช้ 2FA',
        'settings_updated': 'อัปเดตการตั้งค่า',
        'login_attempt': 'กิจกรรมการเข้าสู่ระบบ'
    };

    return titles[type] || 'เหตุการณ์ความปลอดภัย';
}

function hideLiveAlerts() {
    document.getElementById('live-security-alerts').style.display = 'none';
}

function exportSecurityData() {
    window.open('{{ route("user.security.export") }}', '_blank');
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.innerHTML = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endpush
@endsection
